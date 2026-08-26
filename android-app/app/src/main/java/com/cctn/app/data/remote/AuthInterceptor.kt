package com.cctn.app.data.remote

import com.cctn.app.data.local.SessionStore
import okhttp3.Interceptor
import okhttp3.Response
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Attaches the Sanctum bearer token and asks for JSON.
 *
 * Without `Accept: application/json` Laravel answers an unauthenticated API
 * request with a redirect to the login page instead of a 401, which the app
 * would then try to parse as JSON.
 */
@Singleton
class AuthInterceptor @Inject constructor(
    private val store: SessionStore,
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val builder = chain.request().newBuilder()
            .header("Accept", "application/json")

        store.token?.let { builder.header("Authorization", "Bearer $it") }

        return chain.proceed(builder.build())
    }
}
