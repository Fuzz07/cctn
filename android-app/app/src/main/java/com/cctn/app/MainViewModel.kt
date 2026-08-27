package com.cctn.app

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.data.repo.ProfileRepository
import com.cctn.app.data.session.SessionManager
import com.cctn.app.data.session.SessionState
import com.cctn.app.network.NetworkMonitor
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class MainViewModel @Inject constructor(
    private val session: SessionManager,
    private val profileRepository: ProfileRepository,
    networkMonitor: NetworkMonitor,
) : ViewModel() {

    val sessionState: StateFlow<SessionState> = session.state

    val isOnline: StateFlow<Boolean> = networkMonitor.isOnline.stateIn(
        scope = viewModelScope,
        started = SharingStarted.WhileSubscribed(5_000),
        // Assume a connection until told otherwise: showing the offline banner
        // for a frame on every cold start would be wrong far more often than right.
        initialValue = true,
    )

    init {
        session.restore()

        // The cached profile gets the UI on screen immediately; this brings it
        // up to date, and confirms the stored token is still good. A failure is
        // ignored on purpose — a 401 already signs the user out through the
        // interceptor, and anything else can wait for the next screen refresh.
        if (session.state.value is SessionState.SignedIn) {
            viewModelScope.launch { profileRepository.refresh() }
        }
    }

    fun handleAuthToken(token: String) {
        viewModelScope.launch {
            session.setTokenOnly(token)
            profileRepository.refresh()
        }
    }
}
