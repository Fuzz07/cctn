package com.cctn.app.data.remote.dto

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/** Request and response envelopes. Every /api/v1 endpoint wraps its payload. */

// ─── Requests ────────────────────────────────────────────────────────────────

@Serializable
data class LoginRequest(
    @SerialName("login_input") val loginInput: String,
    val password: String,
)

@Serializable
data class RegisterRequest(
    val firstname: String,
    val middlename: String? = null,
    val lastname: String,
    val email: String,
    val username: String,
    val password: String,
    @SerialName("password_confirmation") val passwordConfirmation: String,
    @SerialName("contact_no") val contactNo: String,
    @SerialName("address_barangay") val addressBarangay: String,
    @SerialName("address_municipality") val addressMunicipality: String,
    @SerialName("address_province") val addressProvince: String,
    val birthdate: String? = null,
    val gender: String? = null,
    @SerialName("civil_status") val civilStatus: String? = null,
    @SerialName("place_of_birth") val placeOfBirth: String? = null,
)

@Serializable
data class UpdateProfileRequest(
    val firstname: String,
    val middlename: String? = null,
    val lastname: String,
    val email: String,
    val username: String,
    @SerialName("contact_no") val contactNo: String,
    val birthdate: String? = null,
    val gender: String? = null,
    @SerialName("civil_status") val civilStatus: String? = null,
    @SerialName("place_of_birth") val placeOfBirth: String? = null,
    @SerialName("address_barangay") val addressBarangay: String? = null,
    @SerialName("address_municipality") val addressMunicipality: String? = null,
    @SerialName("address_province") val addressProvince: String? = null,
    @SerialName("new_password") val newPassword: String? = null,
)

@Serializable
data class BookAppointmentRequest(
    @SerialName("service_id") val serviceId: Int,
    @SerialName("preferred_date") val preferredDate: String,
    @SerialName("preferred_time") val preferredTime: String,
    val message: String? = null,
)

@Serializable
data class MaintenanceRequestBody(
    val subject: String,
    val description: String,
    val priority: String,
)

// ─── Responses ───────────────────────────────────────────────────────────────

@Serializable
data class AuthResponse(
    val success: Boolean = false,
    val message: String? = null,
    val token: String,
    val client: ClientDto,
)

@Serializable
data class ProfileResponse(
    val success: Boolean = false,
    val message: String? = null,
    val client: ClientDto,
)

@Serializable
data class SimpleResponse(
    val success: Boolean = false,
    val message: String? = null,
)

@Serializable
data class AppointmentsResponse(
    val success: Boolean = false,
    val appointments: List<AppointmentDto> = emptyList(),
)

@Serializable
data class SlotsResponse(
    val success: Boolean = false,
    val date: String? = null,
    val slots: List<SlotDto> = emptyList(),
)

@Serializable
data class BookAppointmentResponse(
    val success: Boolean = false,
    val rescheduled: Boolean = false,
    val message: String? = null,
    val appointment: AppointmentDto? = null,
)

@Serializable
data class ServicesResponse(
    val success: Boolean = false,
    val services: List<ServiceDto> = emptyList(),
)

@Serializable
data class BillingResponse(
    val success: Boolean = false,
    val balance: Double = 0.0,
    val statements: List<BillingStatementDto> = emptyList(),
)

@Serializable
data class MaintenanceListResponse(
    val success: Boolean = false,
    val requests: List<MaintenanceDto> = emptyList(),
)

@Serializable
data class MaintenanceCreateResponse(
    val success: Boolean = false,
    val message: String? = null,
    val request: MaintenanceDto? = null,
)

/** Laravel's validation / error shape: {"message": "...", "errors": {"field": ["..."]}}. */
@Serializable
data class ApiErrorBody(
    val message: String? = null,
    val errors: Map<String, List<String>> = emptyMap(),
)
