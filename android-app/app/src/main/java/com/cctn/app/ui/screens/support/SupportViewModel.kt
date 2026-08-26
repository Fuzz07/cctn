package com.cctn.app.ui.screens.support

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.core.Validators
import com.cctn.app.data.remote.dto.MaintenanceDto
import com.cctn.app.data.repo.MaintenanceRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

/** The three values the server's `in:low,medium,high` rule accepts. */
enum class Priority(val apiValue: String, val label: String) {
    Low("low", "Low"),
    Medium("medium", "Medium"),
    High("high", "High");

    companion object {
        fun fromLabel(label: String): Priority =
            entries.firstOrNull { it.label == label } ?: Medium
    }
}

data class SupportUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val requests: List<MaintenanceDto> = emptyList(),
    val error: String? = null,

    val composerOpen: Boolean = false,
    val subject: String = "",
    val description: String = "",
    val priority: Priority = Priority.Medium,
    val subjectError: String? = null,
    val descriptionError: String? = null,
    val submitError: String? = null,
    val submitting: Boolean = false,

    val message: String? = null,
)

@HiltViewModel
class SupportViewModel @Inject constructor(
    private val repository: MaintenanceRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(SupportUiState())
    val state: StateFlow<SupportUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun refresh() = load(isRefresh = true)

    private fun load(isRefresh: Boolean = false) {
        _state.update {
            it.copy(
                loading = !isRefresh && it.requests.isEmpty(),
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
                        requests = result.data,
                        error = null,
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(loading = false, refreshing = false, error = result.error.message)
                }
            }
        }
    }

    // ── Composer ─────────────────────────────────────────────────────────────

    fun openComposer() = _state.update {
        it.copy(
            composerOpen = true,
            subject = "",
            description = "",
            priority = Priority.Medium,
            subjectError = null,
            descriptionError = null,
            submitError = null,
        )
    }

    fun closeComposer() {
        // A submit in flight owns the dialog until it finishes.
        if (_state.value.submitting) return
        _state.update { it.copy(composerOpen = false) }
    }

    fun onSubject(value: String) = _state.update {
        it.copy(subject = value, subjectError = null, submitError = null)
    }

    fun onDescription(value: String) = _state.update {
        it.copy(description = value, descriptionError = null, submitError = null)
    }

    fun onPriority(value: Priority) = _state.update { it.copy(priority = value) }

    fun submit() {
        val current = _state.value
        if (current.submitting) return

        val subjectError = Validators.required(current.subject, "Subject", max = 150)
        val descriptionError = Validators.required(current.description, "Description", max = 1000)

        if (subjectError != null || descriptionError != null) {
            _state.update {
                it.copy(subjectError = subjectError, descriptionError = descriptionError)
            }
            return
        }

        _state.update { it.copy(submitting = true, submitError = null) }

        viewModelScope.launch {
            val result = repository.submit(
                subject = current.subject,
                description = current.description,
                priority = current.priority.apiValue,
            )

            when (result) {
                is AppResult.Success -> {
                    _state.update {
                        it.copy(submitting = false, composerOpen = false, message = result.data)
                    }
                    load(isRefresh = true)
                }

                is AppResult.Failure -> _state.update {
                    it.copy(
                        submitting = false,
                        subjectError = result.error.fieldErrors["subject"],
                        descriptionError = result.error.fieldErrors["description"],
                        submitError = if (result.error.fieldErrors.isEmpty()) {
                            result.error.message
                        } else {
                            null
                        },
                    )
                }
            }
        }
    }

    fun messageShown() = _state.update { it.copy(message = null) }
}
