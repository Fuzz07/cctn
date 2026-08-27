package com.cctn.app.data.remote

import com.cctn.app.data.remote.dto.AppointmentsResponse
import com.cctn.app.data.remote.dto.AuthResponse
import com.cctn.app.data.remote.dto.BillingResponse
import com.cctn.app.data.remote.dto.BookAppointmentRequest
import com.cctn.app.data.remote.dto.BookAppointmentResponse
import com.cctn.app.data.remote.dto.ChatMessageBody
import com.cctn.app.data.remote.dto.ChatResponse
import com.cctn.app.data.remote.dto.GoogleLoginRequest
import com.cctn.app.data.remote.dto.LoginRequest
import com.cctn.app.data.remote.dto.MaintenanceCreateResponse
import com.cctn.app.data.remote.dto.MaintenanceListResponse
import com.cctn.app.data.remote.dto.MaintenanceRequestBody
import com.cctn.app.data.remote.dto.NotificationsResponse
import com.cctn.app.data.remote.dto.ProfileResponse
import com.cctn.app.data.remote.dto.RegisterRequest
import com.cctn.app.data.remote.dto.ServicesResponse
import com.cctn.app.data.remote.dto.SimpleResponse
import com.cctn.app.data.remote.dto.SlotsResponse
import com.cctn.app.data.remote.dto.UpdateProfileRequest
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query

/** The customer-facing half of the backend. No admin endpoint is declared here. */
interface CctnApi {

    // ── Auth ─────────────────────────────────────────────────────────────────
    @POST("auth/login")
    suspend fun login(@Body body: LoginRequest): AuthResponse

    @POST("auth/google")
    suspend fun googleLogin(@Body body: GoogleLoginRequest): AuthResponse

    @POST("auth/register")
    suspend fun register(@Body body: RegisterRequest): AuthResponse

    @POST("auth/logout")
    suspend fun logout(): SimpleResponse

    // ── Profile ──────────────────────────────────────────────────────────────
    @GET("profile")
    suspend fun profile(): ProfileResponse

    @PUT("profile")
    suspend fun updateProfile(@Body body: UpdateProfileRequest): ProfileResponse

    // ── Appointments ─────────────────────────────────────────────────────────
    @GET("appointments")
    suspend fun appointments(): AppointmentsResponse

    @GET("appointments/slots")
    suspend fun slots(@Query("date") date: String): SlotsResponse

    @POST("appointments")
    suspend fun book(@Body body: BookAppointmentRequest): BookAppointmentResponse

    @DELETE("appointments/{id}")
    suspend fun cancelAppointment(@Path("id") id: Int): SimpleResponse

    // ── Services ─────────────────────────────────────────────────────────────
    @GET("services")
    suspend fun services(): ServicesResponse

    // ── Billing ──────────────────────────────────────────────────────────────
    @GET("billing")
    suspend fun billing(): BillingResponse

    // ── Maintenance / support ────────────────────────────────────────────────
    @GET("maintenance")
    suspend fun maintenance(): MaintenanceListResponse

    @POST("maintenance")
    suspend fun submitMaintenance(@Body body: MaintenanceRequestBody): MaintenanceCreateResponse

    // ── Notifications ────────────────────────────────────────────────────────
    @GET("notifications")
    suspend fun notifications(): NotificationsResponse

    @POST("notifications/{id}/read")
    suspend fun markNotificationRead(@Path("id") id: Long): SimpleResponse

    @POST("notifications/read-all")
    suspend fun markAllNotificationsRead(): SimpleResponse

    // ── Assistant ────────────────────────────────────────────────────────────
    @POST("chat")
    suspend fun chat(@Body body: ChatMessageBody): ChatResponse
}
