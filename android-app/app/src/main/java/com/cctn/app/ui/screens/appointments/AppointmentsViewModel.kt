package com.cctn.app.ui.screens.appointments

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.data.repo.AppointmentRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class AppointmentsUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val appointments: List<AppointmentDto> = emptyList(),
    val error: String? = null,
    /** The appointment the user is being asked to confirm cancelling. */
    val pendingCancel: AppointmentDto? = null,
    val cancellingId: Int? = null,
    val message: String? = null,
)

@HiltViewModel
class AppointmentsViewModel @Inject constructor(
    private val repository: AppointmentRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(AppointmentsUiState())
    val state: StateFlow<AppointmentsUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun refresh() = load(isRefresh = true)

    private fun load(isRefresh: Boolean = false) {
        _state.update {
            it.copy(
                loading = !isRefresh && it.appointments.isEmpty(),
                refreshing = isRefresh,
                error = null,
            )
        }

        viewModelScope.launch {
            when (val result = repository.list()) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        appointments = result.data,
                        error = null,
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        // A refresh that fails keeps the list already on screen
                        // and reports the problem, rather than emptying it.
                        error = result.error.message,
                    )
                }
            }
        }
    }

    fun askToCancel(appointment: AppointmentDto) =
        _state.update { it.copy(pendingCancel = appointment) }

    fun dismissCancel() = _state.update { it.copy(pendingCancel = null) }

    fun confirmCancel() {
        val target = _state.value.pendingCancel ?: return
        _state.update { it.copy(pendingCancel = null, cancellingId = target.id) }

        viewModelScope.launch {
            when (val result = repository.cancel(target.id)) {
                is AppResult.Success -> {
                    _state.update { it.copy(cancellingId = null, message = result.data) }
                    // Re-read rather than patching locally: the server is what
                    // decides the resulting status.
                    load(isRefresh = true)
                }

                is AppResult.Failure -> _state.update {
                    it.copy(cancellingId = null, message = result.error.message)
                }
            }
        }
    }

    fun messageShown() = _state.update { it.copy(message = null) }
}
