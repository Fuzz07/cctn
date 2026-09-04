package com.cctn.app.data.remote.dto

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/**
 * Wire models for /api/v1.
 *
 * Every field name here mirrors the matching Laravel API resource
 * (app/Http/Resources) exactly. Anything the server may legitimately omit or
 * send as null is nullable with a default, so one unexpected null never fails
 * the whole response.
 */

@Serializable
data class ClientDto(
    val id: Int,
    @SerialName("account_number") val accountNumber: String? = null,
    val firstname: String = "",
    val middlename: String? = null,
    val lastname: String = "",
    @SerialName("full_name") val fullName: String = "",
    val email: String = "",
    val username: String = "",
    val birthdate: String? = null,
    val age: Int? = null,
    val gender: String? = null,
    @SerialName("civil_status") val civilStatus: String? = null,
    @SerialName("place_of_birth") val placeOfBirth: String? = null,
    @SerialName("address_barangay") val addressBarangay: String? = null,
    @SerialName("address_municipality") val addressMunicipality: String? = null,
    @SerialName("address_province") val addressProvince: String? = null,
    @SerialName("contact_no") val contactNo: String? = null,
    @SerialName("profile_photo") val profilePhoto: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
)

@Serializable
data class AppointmentServiceDto(
    val id: Int,
    val name: String = "",
    val price: Double = 0.0,
    @SerialName("duration_min") val durationMin: Int? = null,
)

@Serializable
data class AppointmentDto(
    val id: Int,
    val service: AppointmentServiceDto? = null,
    @SerialName("preferred_date") val preferredDate: String? = null,
    @SerialName("preferred_time") val preferredTime: String? = null,
    val message: String? = null,
    val status: String = "pending",
    @SerialName("payment_method") val paymentMethod: String? = null,
    @SerialName("reference_number") val referenceNumber: String? = null,
    @SerialName("admin_notes") val adminNotes: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
)

@Serializable
data class ServiceDto(
    val id: Int,
    @SerialName("service_name") val serviceName: String = "",
    val description: String? = null,
    val price: Double = 0.0,
    @SerialName("duration_minutes") val durationMinutes: Int? = null,
    val status: String? = null,
)

@Serializable
data class BillingStatementDto(
    val id: Int,
    @SerialName("account_number") val accountNumber: String? = null,
    @SerialName("statement_period") val statementPeriod: String? = null,
    @SerialName("amount_due") val amountDue: Double = 0.0,
    @SerialName("penalty_amount") val penaltyAmount: Double = 0.0,
    @SerialName("total_amount_due") val totalAmountDue: Double = 0.0,
    val status: String = "unpaid",
    @SerialName("due_date") val dueDate: String? = null,
    @SerialName("paid_at") val paidAt: String? = null,
    val notes: String? = null,
)

@Serializable
data class MaintenanceDto(
    val id: Int,
    val subject: String = "",
    val description: String = "",
    val priority: String = "medium",
    val status: String = "pending",
    @SerialName("follow_up_note") val followUpNote: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
    @SerialName("updated_at") val updatedAt: String? = null,
)

@Serializable
data class SlotDto(
    val time: String,
    val label: String,
    val available: Boolean,
)

@Serializable
data class PaymentMethodDto(
    val id: Int,
    @SerialName("payment_type") val paymentType: String = "gcash",
    @SerialName("provider_name") val providerName: String = "",
    @SerialName("formatted_type") val formattedType: String = "",
    @SerialName("account_name") val accountName: String = "",
    @SerialName("account_number") val accountNumber: String = "",
    @SerialName("masked_account_number") val maskedAccountNumber: String = "",
    @SerialName("is_default") val isDefault: Boolean = false,
    val notes: String? = null,
    @SerialName("theme_color") val themeColor: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
)
