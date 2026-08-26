package com.cctn.app.data.session

import com.cctn.app.data.local.SessionStore
import com.cctn.app.data.remote.dto.ClientDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject
import javax.inject.Singleton

sealed interface SessionState {
    /** Reading persisted credentials; the splash screen stays up. */
    data object Loading : SessionState
    data object SignedOut : SessionState
    data class SignedIn(val client: ClientDto) : SessionState
}

/**
 * The single source of truth for who is signed in.
 *
 * The token is read straight from [SessionStore] by the auth interceptor on
 * every request, so signing out takes effect on the next call with no extra
 * wiring.
 */
@Singleton
class SessionManager @Inject constructor(
    private val store: SessionStore,
) {
    private val _state = MutableStateFlow<SessionState>(SessionState.Loading)
    val state: StateFlow<SessionState> = _state.asStateFlow()

    /** Set once at startup from whatever was persisted. */
    fun restore() {
        val token = store.token
        val client = store.cachedClient()
        _state.value = if (token != null && client != null) {
            SessionState.SignedIn(client)
        } else {
            // A half-written session (token without profile, or the reverse) is
            // not usable; drop it rather than starting in a broken state.
            if (token != null || client != null) store.clear()
            SessionState.SignedOut
        }
    }

    fun signIn(token: String, client: ClientDto) {
        store.save(token, client)
        _state.value = SessionState.SignedIn(client)
    }

    /** Refreshes the cached profile without touching the token. */
    fun updateClient(client: ClientDto) {
        if (store.token == null) return
        store.saveClient(client)
        _state.value = SessionState.SignedIn(client)
    }

    fun signOut() {
        store.clear()
        _state.value = SessionState.SignedOut
    }

    val currentClient: ClientDto? get() = (_state.value as? SessionState.SignedIn)?.client
}
