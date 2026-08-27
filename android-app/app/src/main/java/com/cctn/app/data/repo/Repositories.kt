package com.cctn.app.data.repo

import com.cctn.app.core.AppResult
import com.cctn.app.core.map
import com.cctn.app.data.remote.CctnApi
import com.cctn.app.data.remote.apiCall
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.data.remote.dto.BillingResponse
import com.cctn.app.data.remote.dto.BookAppointmentRequest
import com.cctn.app.data.remote.dto.BookAppointmentResponse
import com.cctn.app.data.remote.dto.ChatMessageBody
import com.cctn.app.data.remote.dto.ChatResponse
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.data.remote.dto.GoogleLoginRequest
import com.cctn.app.data.remote.dto.LoginRequest
import com.cctn.app.data.remote.dto.MaintenanceDto
import com.cctn.app.data.remote.dto.MaintenanceRequestBody
import com.cctn.app.data.remote.dto.RegisterRequest
import com.cctn.app.data.remote.dto.ServiceDto
import com.cctn.app.data.remote.dto.SlotDto
import com.cctn.app.data.remote.dto.UpdateProfileRequest
import com.cctn.app.data.session.SessionManager
import kotlinx.serialization.json.Json
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
    ): AppResult<BookAppointmentResponse> = apiCall(json) {
        api.book(
            BookAppointmentRequest(
                serviceId = serviceId,
                preferredDate = date,
                preferredTime = time,
                message = message?.takeIf { it.isNotBlank() },
            )
        )
    }

    suspend fun cancel(id: Int): AppResult<String> =
        apiCall(json) { api.cancelAppointment(id) }
            .map { it.message ?: "Appointment cancelled." }
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
