package com.cctn.app.ui.screens.appointments

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.PaymentMethodDto
import com.cctn.app.data.remote.dto.ServiceDto
import com.cctn.app.data.remote.dto.SlotDto
import com.cctn.app.data.repo.AppointmentRepository
import com.cctn.app.data.repo.PaymentMethodRepository
import com.cctn.app.data.repo.ServiceRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate
import javax.inject.Inject

data class BookUiState(
    val loadingServices: Boolean = true,
    val services: List<ServiceDto> = emptyList(),
    val servicesError: String? = null,

    val selectedServiceId: Int? = null,
    val date: LocalDate = LocalDate.now(),

    val loadingSlots: Boolean = false,
    val slots: List<SlotDto> = emptyList(),
    val slotsError: String? = null,
    val selectedTime: String? = null,

    val paymentMethods: List<PaymentMethodDto> = emptyList(),
    val selectedPaymentMethod: String = "GCash",
    val referenceNumber: String = "",

    val message: String = "",

    val submitting: Boolean = false,
    val submitError: String? = null,
    /** Set when the booking succeeded; the screen shows this and then closes. */
    val result: BookResult? = null,
) {
    val selectedService: ServiceDto?
        get() = services.firstOrNull { it.id == selectedServiceId }

    val apiDate: String get() = date.format(Formatters.API_DATE)

    val canSubmit: Boolean
        get() = selectedServiceId != null && selectedTime != null && selectedPaymentMethod.isNotBlank() && !submitting
}

data class BookResult(val message: String, val rescheduled: Boolean)

@HiltViewModel
class BookViewModel @Inject constructor(
    private val serviceRepository: ServiceRepository,
    private val appointmentRepository: AppointmentRepository,
    private val paymentMethodRepository: PaymentMethodRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(BookUiState())
    val state: StateFlow<BookUiState> = _state.asStateFlow()

    init {
        loadServices()
        loadSlots(_state.value.date)
        loadPaymentMethods()
    }

    fun loadServices() {
        _state.update { it.copy(loadingServices = true, servicesError = null) }

        viewModelScope.launch {
            when (val result = serviceRepository.list()) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        loadingServices = false,
                        services = result.data,
                        selectedServiceId = it.selectedServiceId
                            ?: result.data.singleOrNull()?.id,
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(loadingServices = false, servicesError = result.error.message)
                }
            }
        }
    }

    fun loadPaymentMethods() {
        viewModelScope.launch {
            when (val result = paymentMethodRepository.list()) {
                is AppResult.Success -> {
                    val methods = result.data
                    val defaultMethod = methods.firstOrNull { it.isDefault }?.providerName
                        ?: methods.firstOrNull()?.providerName
                        ?: "GCash"
                    _state.update {
                        it.copy(
                            paymentMethods = methods,
                            selectedPaymentMethod = defaultMethod,
                        )
                    }
                }
                is AppResult.Failure -> Unit
            }
        }
    }

    fun selectService(id: Int) = _state.update {
        it.copy(selectedServiceId = id, submitError = null)
    }

    fun selectDate(date: LocalDate) {
        _state.update { it.copy(date = date, selectedTime = null, submitError = null) }
        loadSlots(date)
    }

    fun selectTime(time: String) = _state.update {
        it.copy(selectedTime = time, submitError = null)
    }

    fun selectPaymentMethod(method: String) = _state.update {
        it.copy(selectedPaymentMethod = method, submitError = null)
    }

    fun onReferenceNumberChange(value: String) = _state.update {
        it.copy(referenceNumber = value)
    }

    fun onMessageChange(value: String) = _state.update { it.copy(message = value) }

    fun loadSlots(date: LocalDate = _state.value.date) {
        _state.update { it.copy(loadingSlots = true, slotsError = null) }

        viewModelScope.launch {
            when (val result = appointmentRepository.slots(date.format(Formatters.API_DATE))) {
                is AppResult.Success -> _state.update { current ->
                    if (current.date != date) return@update current
                    current.copy(loadingSlots = false, slots = result.data)
                }

                is AppResult.Failure -> _state.update { current ->
                    if (current.date != date) return@update current
                    current.copy(
                        loadingSlots = false,
                        slots = emptyList(),
                        slotsError = result.error.message,
                    )
                }
            }
        }
    }

    fun submit() {
        val current = _state.value
        val serviceId = current.selectedServiceId ?: return
        val time = current.selectedTime ?: return
        if (current.submitting) return

        _state.update { it.copy(submitting = true, submitError = null) }

        viewModelScope.launch {
            val result = appointmentRepository.book(
                serviceId = serviceId,
                date = current.apiDate,
                time = time,
                message = current.message,
                paymentMethod = current.selectedPaymentMethod,
                referenceNumber = current.referenceNumber.takeIf { it.isNotBlank() },
            )

            when (result) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        submitting = false,
                        result = BookResult(
                            message = result.data.message
                                ?: "Appointment booked. Awaiting confirmation.",
                            rescheduled = result.data.rescheduled,
                        ),
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(submitting = false, submitError = result.error.message)
                }
            }
        }
    }

    fun resultShown() = _state.update { it.copy(result = null) }
}
