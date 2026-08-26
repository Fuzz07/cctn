package com.cctn.app.data.remote

import com.cctn.app.core.AppError
import com.cctn.app.core.AppResult
import kotlinx.coroutines.test.runTest
import kotlinx.serialization.json.Json
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.ResponseBody.Companion.toResponseBody
import org.junit.Assert.assertEquals
import org.junit.Assert.assertTrue
import org.junit.Test
import retrofit2.HttpException
import retrofit2.Response
import java.io.IOException

class ApiCallTest {

    private val json = Json { ignoreUnknownKeys = true }

    private fun httpError(code: Int, body: String): HttpException = HttpException(
        Response.error<Unit>(code, body.toResponseBody("application/json".toMediaType()))
    )

    private fun failureOf(result: AppResult<*>): AppError {
        assertTrue("expected a failure, got $result", result is AppResult.Failure)
        return (result as AppResult.Failure).error
    }

    @Test
    fun `success passes the value straight through`() = runTest {
        val result = apiCall(json) { "ok" }
        assertEquals(AppResult.Success("ok"), result)
    }

    @Test
    fun `422 is unpacked into per-field messages`() = runTest {
        val body = """
            {"message":"The given data was invalid.",
             "errors":{"contact_no":["Mobile number must be exactly 11 digits."],
                       "email":["The email has already been taken."]}}
        """.trimIndent()

        val error = failureOf(apiCall(json) { throw httpError(422, body) })

        assertEquals(AppError.Kind.Validation, error.kind)
        assertEquals(
            "Mobile number must be exactly 11 digits.",
            error.fieldErrors["contact_no"],
        )
        assertEquals("The email has already been taken.", error.fieldErrors["email"])
    }

    @Test
    fun `401 is reported as an expired session`() = runTest {
        val error = failureOf(apiCall(json) { throw httpError(401, """{"message":"Unauthenticated."}""") })

        assertEquals(AppError.Kind.Unauthorized, error.kind)
        assertTrue(error.isUnauthorized)
    }

    @Test
    fun `a 500 body is never shown to the user`() = runTest {
        // The backend answers with a stack trace and the failing SQL when
        // APP_DEBUG is on. None of that may reach the screen.
        val leaky = """
            {"message":"SQLSTATE[HY000] [2006] MySQL server has gone away (SQL: select * from `clients` where `username` = juan)",
             "exception":"Illuminate\\Database\\QueryException",
             "file":"/var/task/user/vendor/laravel/framework/src/Illuminate/Database/Connection.php"}
        """.trimIndent()

        val error = failureOf(apiCall(json) { throw httpError(500, leaky) })

        assertEquals(AppError.Kind.Server, error.kind)
        assertTrue(
            "leaked server internals: ${error.message}",
            listOf("SQLSTATE", "SQL:", "vendor/", "Exception", "clients")
                .none { error.message.contains(it, ignoreCase = true) },
        )
    }

    @Test
    fun `429 gets its own wording`() = runTest {
        val error = failureOf(apiCall(json) { throw httpError(429, """{"message":"Too Many Attempts."}""") })

        assertEquals(AppError.Kind.Server, error.kind)
        assertTrue(error.message.contains("Too many attempts"))
    }

    @Test
    fun `a dropped connection reads as a network problem`() = runTest {
        val error = failureOf(apiCall(json) { throw IOException("unexpected end of stream") })

        assertEquals(AppError.Kind.Network, error.kind)
        assertTrue(error.message.contains("connection", ignoreCase = true))
    }

    @Test
    fun `an unparseable error body still produces a usable message`() = runTest {
        val error = failureOf(apiCall(json) { throw httpError(400, "<html>Bad Request</html>") })

        assertEquals(AppError.Kind.Server, error.kind)
        assertTrue(error.message.isNotBlank())
    }
}
