package com.cctn.app.ui.screens.profile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.PaymentMethodDto
import com.cctn.app.data.repo.PaymentMethodRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class PaymentMethodsUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val paymentMethods: List<PaymentMethodDto> = emptyList(),
    val error: String? = null,
    val message: String? = null,

    val showAddDialog: Boolean = false,
    val saving: Boolean = false,
    val addError: String? = null,

    val deletingId: Int? = null,
    val pendingDelete: PaymentMethodDto? = null,
)

@HiltViewModel
class PaymentMethodsViewModel @Inject constructor(
    private val repository: PaymentMethodRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(PaymentMethodsUiState())
    val state: StateFlow<PaymentMethodsUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun refresh() {
        _state.update { it.copy(refreshing = true, error = null) }
        fetch()
    }

    fun load() {
        _state.update { it.copy(loading = true, error = null) }
        fetch()
    }

    private fun fetch() {
        viewModelScope.launch {
            when (val result = repository.list()) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        paymentMethods = result.data,
                    )
                }
                is AppResult.Failure -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        error = result.error.message,
                    )
                }
            }
        }
    }

    fun openAddDialog() {
        _state.update { it.copy(showAddDialog = true, addError = null) }
    }

    fun dismissAddDialog() {
        _state.update { it.copy(showAddDialog = false, addError = null) }
    }

    fun addPaymentMethod(
        paymentType: String,
        providerName: String,
        accountName: String,
        accountNumber: String,
        isDefault: Boolean,
        notes: String?,
    ) {
        if (_state.value.saving) return
        _state.update { it.copy(saving = true, addError = null) }

        viewModelScope.launch {
            val result = repository.create(
                paymentType = paymentType,
                providerName = providerName,
                accountName = accountName,
                accountNumber = accountNumber,
                isDefault = isDefault,
                notes = notes,
            )

            when (result) {
                is AppResult.Success -> {
                    _state.update {
                        it.copy(
                            saving = false,
                            showAddDialog = false,
                            message = "Payment method added successfully.",
                        )
                    }
                    fetch()
                }
                is AppResult.Failure -> {
                    _state.update {
                        it.copy(
                            saving = false,
                            addError = result.error.message,
                        )
                    }
                }
            }
        }
    }

    fun setDefault(id: Int) {
        viewModelScope.launch {
            when (val result = repository.setDefault(id)) {
                is AppResult.Success -> {
                    _state.update { it.copy(message = "Default payment method updated.") }
                    fetch()
                }
                is AppResult.Failure -> {
                    _state.update { it.copy(message = result.error.message) }
                }
            }
        }
    }

    fun askToDelete(method: PaymentMethodDto) {
        _state.update { it.copy(pendingDelete = method) }
    }

    fun dismissDelete() {
        _state.update { it.copy(pendingDelete = null) }
    }

    fun confirmDelete() {
        val target = _state.value.pendingDelete ?: return
        _state.update { it.copy(pendingDelete = null, deletingId = target.id) }

        viewModelScope.launch {
            when (val result = repository.delete(target.id)) {
                is AppResult.Success -> {
                    _state.update {
                        it.copy(
                            deletingId = null,
                            message = "Payment method removed.",
                        )
                    }
                    fetch()
                }
                is AppResult.Failure -> {
                    _state.update {
                        it.copy(
                            deletingId = null,
                            message = result.error.message,
                        )
                    }
                }
            }
        }
    }

    fun messageShown() {
        _state.update { it.copy(message = null) }
    }
}
