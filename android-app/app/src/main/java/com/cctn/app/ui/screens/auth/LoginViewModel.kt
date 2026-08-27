package com.cctn.app.ui.screens.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.core.Validators
import com.cctn.app.data.repo.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class LoginUiState(
    val loginInput: String = "",
    val password: String = "",
    val rememberMe: Boolean = true,
    val loginInputError: String? = null,
    val passwordError: String? = null,
    val formError: String? = null,
    val submitting: Boolean = false,
) {
    val canSubmit: Boolean
        get() = loginInput.isNotBlank() && password.isNotBlank() && !submitting
}

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val authRepository: AuthRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(LoginUiState())
    val state: StateFlow<LoginUiState> = _state.asStateFlow()

    fun onLoginInputChange(value: String) = _state.update {
        it.copy(loginInput = value, loginInputError = null, formError = null)
    }

    fun onPasswordChange(value: String) = _state.update {
        it.copy(password = value, passwordError = null, formError = null)
    }

    fun onRememberMeChange(value: Boolean) = _state.update {
        it.copy(rememberMe = value)
    }

    fun onGoogleIdToken(idToken: String) {
        val current = _state.value
        if (current.submitting) return

        _state.update { it.copy(submitting = true, formError = null) }

        viewModelScope.launch {
            when (val result = authRepository.googleLogin(idToken)) {
                is AppResult.Success -> {
                    _state.update { it.copy(submitting = false) }
                }

                is AppResult.Failure -> {
                    _state.update {
                        it.copy(
                            submitting = false,
                            formError = result.error.message ?: "Google sign-in failed. Please try again.",
                        )
                    }
                }
            }
        }
    }

    fun onGoogleSignInError(error: String) = _state.update {
        it.copy(formError = error, submitting = false)
    }

    fun submit() {
        val current = _state.value
        if (current.submitting) return

        val loginInputError = Validators.required(current.loginInput, "Username or email")
        val passwordError = if (current.password.isEmpty()) "Password is required." else null

        if (loginInputError != null || passwordError != null) {
            _state.update {
                it.copy(loginInputError = loginInputError, passwordError = passwordError)
            }
            return
        }

        _state.update { it.copy(submitting = true, formError = null) }

        viewModelScope.launch {
            when (val result = authRepository.login(current.loginInput, current.password)) {
                is AppResult.Success -> {
                    // The session flow swaps the navigation graph; this screen
                    // is about to leave, so only the spinner needs clearing.
                    _state.update { it.copy(submitting = false) }
                }

                is AppResult.Failure -> _state.update {
                    it.copy(
                        submitting = false,
                        // The server reports a wrong password against
                        // login_input, so surface it on that field too.
                        loginInputError = result.error.fieldErrors["login_input"],
                        formError = result.error.message,
                    )
                }
            }
        }
    }
}
