package com.cctn.app.data.remote.dto

import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.jsonObject
import kotlinx.serialization.json.jsonPrimitive
import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Test

class AuthRequestsTest {

    // As AppModule configures it: a null is left out rather than sent.
    private val json = Json {
        ignoreUnknownKeys = true
        coerceInputValues = true
        explicitNulls = false
    }

    private fun encode(body: LoginRequest): JsonObject =
        json.parseToJsonElement(json.encodeToString(LoginRequest.serializer(), body)).jsonObject

    private fun encode(body: RegisterRequest): JsonObject =
        json.parseToJsonElement(json.encodeToString(RegisterRequest.serializer(), body)).jsonObject

    @Test
    fun `sign-in sends the token under the name the API reads`() {
        val body = encode(LoginRequest("juan", "password123", recaptchaToken = "token"))

        assertEquals("token", body["recaptcha_token"]?.jsonPrimitive?.content)
    }

    @Test
    fun `sign-in leaves the field out when there is no token`() {
        val body = encode(LoginRequest("juan", "password123"))

        assertFalse(body.containsKey("recaptcha_token"))
    }

    @Test
    fun `registration sends the token under the same name`() {
        val body = encode(
            RegisterRequest(
                firstname = "Maria",
                lastname = "Santos",
                email = "maria@example.com",
                username = "maria",
                password = "password123",
                passwordConfirmation = "password123",
                contactNo = "09123456789",
                addressBarangay = "San Vicente",
                addressMunicipality = "Bantayan",
                addressProvince = "Cebu",
                recaptchaToken = "token",
            )
        )

        assertEquals("token", body["recaptcha_token"]?.jsonPrimitive?.content)
    }
}
