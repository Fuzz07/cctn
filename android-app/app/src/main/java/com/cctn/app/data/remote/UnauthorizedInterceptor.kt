package com.cctn.app.data.remote

import com.cctn.app.data.session.SessionManager
import okhttp3.Interceptor
import okhttp3.Response
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Ends the session as soon as the server rejects the token.
 *
 * Sanctum tokens are revoked server-side (a logout elsewhere, an admin
 * clearing them), so the app can hold a token that no longer works. Rather
 * than let every screen fail on its own, one 401 signs the user out and the
 * navigation graph swaps to the login screen.
 *
 * The login and register calls are exempt: a 401 there is a rejected password,
 * not an expired session.
 */
@Singleton
class UnauthorizedInterceptor @Inject constructor(
    private val session: SessionManager,
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        val response = chain.proceed(request)

        val isAuthEndpoint = request.url.encodedPath.contains("/auth/")
        if (response.code == 401 && !isAuthEndpoint) {
            session.signOut()
        }

        return response
    }
}
