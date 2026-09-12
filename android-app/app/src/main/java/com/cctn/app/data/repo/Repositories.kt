package com.cctn.app.data.repo

import android.content.Context
import android.net.Uri
import com.cctn.app.core.AppError
import com.cctn.app.core.AppResult
import com.cctn.app.core.map
import com.cctn.app.data.remote.CctnApi
import com.cctn.app.data.remote.apiCall
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.data.remote.dto.BillingResponse
import com.cctn.app.data.remote.dto.BookAppointmentResponse
import com.cctn.app.data.remote.dto.ChatMessageBody
import com.cctn.app.data.remote.dto.ChatResponse
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.data.remote.dto.GoogleLoginRequest
import com.cctn.app.data.remote.dto.LoginRequest
import com.cctn.app.data.remote.dto.CreatePaymentMethodRequest
import com.cctn.app.data.remote.dto.MaintenanceDto
import com.cctn.app.data.remote.dto.MaintenanceRequestBody
import com.cctn.app.data.remote.dto.NotificationsPayload
import com.cctn.app.data.remote.dto.NotificationDto
import com.cctn.app.data.remote.dto.PaymentMethodDto
import com.cctn.app.data.remote.dto.RegisterRequest
import com.cctn.app.data.remote.dto.ServiceDto
import com.cctn.app.data.remote.dto.SlotDto
import com.cctn.app.data.remote.dto.UpdatePaymentMethodDetailsRequest
import com.cctn.app.data.remote.dto.UpdatePaymentMethodRequest
import com.cctn.app.data.remote.dto.UpdateProfileRequest
import com.cctn.app.data.session.SessionManager
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AuthRepository @Inject constructor(
    private val api: CctnApi,
    private val session: SessionManager,
    private val json: Json,
) {
    suspend fun login(loginInput: String, password: String): AppResult<ClientDto> =
        apiCall(json) { api.login(LoginRequest(loginInput.trim(), password)) }
            .also { result ->
                if (result is AppResult.Success) {
                    session.signIn(result.data.token, result.data.client)
                }
            }
            .map { it.client }

    suspend fun googleLogin(idToken: String): AppResult<ClientDto> =
        apiCall(json) { api.googleLogin(GoogleLoginRequest(idToken)) }
            .also { result ->
                if (result is AppResult.Success) {
                    session.signIn(result.data.token, result.data.client)
                }
            }
            .map { it.client }
    suspend fun register(body: RegisterRequest): AppResult<ClientDto> =
        apiCall(json) { api.register(body) }
            .also { result ->
                if (result is AppResult.Success) {
                    session.signIn(result.data.token, result.data.client)
                }
            }
            .map { it.client }

    /**
     * Clears the session locally whatever the server says. A failed logout call
     * must not strand the user in an account they asked to leave; the token is
     * dropped on this device either way.
     */
    suspend fun logout() {
        apiCall(json) { api.logout() }
        session.signOut()
    }
}

@Singleton
class ProfileRepository @Inject constructor(
    private val api: CctnApi,
    private val session: SessionManager,
    private val json: Json,
) {
    suspend fun refresh(): AppResult<ClientDto> =
        apiCall(json) { api.profile() }
            .also { if (it is AppResult.Success) session.updateClient(it.data.client) }
            .map { it.client }

    suspend fun update(body: UpdateProfileRequest): AppResult<ClientDto> =
        apiCall(json) { api.updateProfile(body) }
            .also { if (it is AppResult.Success) session.updateClient(it.data.client) }
            .map { it.client }
}

@Singleton
class AppointmentRepository @Inject constructor(
    @ApplicationContext private val context: Context,
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun list(): AppResult<List<AppointmentDto>> =
        apiCall(json) { api.appointments() }.map { it.appointments }

    suspend fun slots(date: String): AppResult<List<SlotDto>> =
        apiCall(json) { api.slots(date) }.map { it.slots }

    suspend fun book(
        serviceId: Int,
        date: String,
        time: String,
        message: String?,
        paymentMethod: String,
        referenceNumber: String,
        paymentProofUri: String,
        paymentProofName: String,
        paymentProofMimeType: String?,
    ): AppResult<BookAppointmentResponse> {
        val proofPart = try {
            val resolver = context.contentResolver
            val uri = Uri.parse(paymentProofUri)
            val bytes = resolver.openInputStream(uri)?.use { it.readBytes() }
                ?: throw IllegalArgumentException("The selected receipt could not be opened.")

            if (bytes.size > MAX_PAYMENT_PROOF_BYTES) {
                return AppResult.Failure(
                    AppError(
                        message = "The payment receipt must be 4 MB or smaller.",
                        fieldErrors = mapOf("payment_proof" to "The payment receipt must be 4 MB or smaller."),
                        kind = AppError.Kind.Validation,
                    )
                )
            }

            val mimeType = (paymentProofMimeType ?: resolver.getType(uri) ?: "image/jpeg")
                .toMediaTypeOrNull()
            val fileName = paymentProofName
                .replace(Regex("[^A-Za-z0-9._-]"), "_")
                .take(120)
                .ifBlank { "payment-receipt.jpg" }

            MultipartBody.Part.createFormData(
                "payment_proof",
                fileName,
                bytes.toRequestBody(mimeType),
            )
        } catch (exception: Exception) {
            return AppResult.Failure(
                AppError(
                    message = "The selected payment receipt could not be read. Please choose it again.",
                    fieldErrors = mapOf("payment_proof" to "Please choose the payment receipt again."),
                    kind = AppError.Kind.Validation,
                )
            )
        }

        return apiCall(json) {
            api.book(
                serviceId = serviceId.toString().textPart(),
                preferredDate = date.textPart(),
                preferredTime = time.textPart(),
                message = message?.trim()?.takeIf { it.isNotEmpty() }?.textPart(),
                paymentMethod = paymentMethod.trim().textPart(),
                referenceNumber = referenceNumber.trim().textPart(),
                paymentProof = proofPart,
            )
        }
    }

    private fun String.textPart() = toRequestBody("text/plain".toMediaTypeOrNull())

    private companion object {
        const val MAX_PAYMENT_PROOF_BYTES = 4 * 1024 * 1024
    }

    suspend fun updatePaymentMethod(
        id: Int,
        paymentMethod: String,
        referenceNumber: String? = null,
    ): AppResult<String> = apiCall(json) {
        api.updateAppointmentPaymentMethod(
            id = id,
            body = UpdatePaymentMethodRequest(paymentMethod, referenceNumber),
        )
    }.map { it.message ?: "Payment method updated." }

    suspend fun cancel(id: Int): AppResult<String> =
        apiCall(json) { api.cancelAppointment(id) }
            .map { it.message ?: "Appointment cancelled." }
}

@Singleton
class PaymentMethodRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun list(): AppResult<List<PaymentMethodDto>> =
        apiCall(json) { api.paymentMethods() }.map { it.paymentMethods }

    suspend fun create(
        paymentType: String,
        providerName: String,
        accountName: String,
        accountNumber: String,
        isDefault: Boolean = false,
        notes: String? = null,
    ): AppResult<PaymentMethodDto> = apiCall(json) {
        api.createPaymentMethod(
            CreatePaymentMethodRequest(
                paymentType = paymentType.trim(),
                providerName = providerName.trim(),
                accountName = accountName.trim(),
                accountNumber = accountNumber.trim(),
                isDefault = isDefault,
                notes = notes?.trim()?.takeIf { it.isNotBlank() },
            )
        )
    }.map { it.paymentMethod ?: error("Missing payment method payload") }

    suspend fun update(
        id: Int,
        providerName: String,
        accountName: String,
        accountNumber: String,
        isDefault: Boolean = false,
        notes: String? = null,
    ): AppResult<PaymentMethodDto> = apiCall(json) {
        api.updatePaymentMethod(
            id = id,
            body = UpdatePaymentMethodDetailsRequest(
                providerName = providerName.trim(),
                accountName = accountName.trim(),
                accountNumber = accountNumber.trim(),
                isDefault = isDefault,
                notes = notes?.trim()?.takeIf { it.isNotBlank() },
            )
        )
    }.map { it.paymentMethod ?: error("Missing payment method payload") }

    suspend fun delete(id: Int): AppResult<String> =
        apiCall(json) { api.deletePaymentMethod(id) }
            .map { it.message ?: "Payment method removed." }

    suspend fun setDefault(id: Int): AppResult<PaymentMethodDto> =
        apiCall(json) { api.setDefaultPaymentMethod(id) }
            .map { it.paymentMethod ?: error("Missing payment method payload") }
}

@Singleton
class ServiceRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun list(): AppResult<List<ServiceDto>> =
        apiCall(json) { api.services() }.map { it.services }
}

@Singleton
class BillingRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun load(): AppResult<BillingResponse> = apiCall(json) { api.billing() }
}

@Singleton
class MaintenanceRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun list(): AppResult<List<MaintenanceDto>> =
        apiCall(json) { api.maintenance() }.map { it.requests }

    suspend fun submit(
        subject: String,
        description: String,
        priority: String,
    ): AppResult<String> = apiCall(json) {
        api.submitMaintenance(
            MaintenanceRequestBody(subject.trim(), description.trim(), priority)
        )
    }.map { it.message ?: "Request submitted." }
}

@Singleton
class ChatRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    /** An empty message asks the assistant to introduce itself. */
    suspend fun send(message: String = ""): AppResult<ChatResponse> =
        apiCall(json) { api.chat(ChatMessageBody(message.trim())) }
}

@Singleton
class NotificationRepository @Inject constructor(
    private val api: CctnApi,
    private val json: Json,
) {
    suspend fun list(): AppResult<NotificationsPayload> =
        apiCall(json) { api.notifications() }.map { it.data }

    suspend fun markRead(id: Long): AppResult<String> =
        apiCall(json) { api.markNotificationRead(id) }.map { it.message ?: "Marked as read." }

    suspend fun markAllRead(): AppResult<String> =
        apiCall(json) { api.markAllNotificationsRead() }.map { it.message ?: "All notifications marked as read." }
}
