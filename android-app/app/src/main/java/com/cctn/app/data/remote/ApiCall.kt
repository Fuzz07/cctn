package com.cctn.app.data.remote

import com.cctn.app.core.AppError
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.ApiErrorBody
import kotlinx.coroutines.CancellationException
import kotlinx.serialization.json.Json
import retrofit2.HttpException
import java.io.IOException

/**
 * Runs one API call and turns anything it throws into an [AppError].
 *
 * A 5xx body is never shown to the user: the backend runs with debug output
 * enabled, so its 500 payload can contain a stack trace and the failing SQL.
 * Only 4xx messages — which are written for the client — are passed through.
 */
suspend fun <T> apiCall(json: Json, block: suspend () -> T): AppResult<T> = try {
    AppResult.Success(block())
} catch (e: CancellationException) {
    throw e
} catch (e: HttpException) {
    AppResult.Failure(e.toAppError(json))
} catch (e: IOException) {
    AppResult.Failure(
        AppError(
            message = "Can't reach the server. Check your connection and try again.",
            kind = AppError.Kind.Network,
        )
    )
} catch (e: Exception) {
    AppResult.Failure(
        AppError(
            message = "Something went wrong. Please try again.",
            kind = AppError.Kind.Unknown,
        )
    )
}

private fun HttpException.toAppError(json: Json): AppError {
    val code = code()
    val body = runCatching { response()?.errorBody()?.string() }.getOrNull()
    val parsed = body?.let {
        runCatching { json.decodeFromString(ApiErrorBody.serializer(), it) }.getOrNull()
    }

    return when {
        code == 401 -> AppError(
            message = "Your session has expired. Please sign in again.",
            kind = AppError.Kind.Unauthorized,
        )

        code == 422 -> {
            val fields = parsed?.errors.orEmpty()
                .mapNotNull { (key, messages) -> messages.firstOrNull()?.let { key to it } }
                .toMap()
            AppError(
                message = fields.values.firstOrNull()
                    ?: parsed?.message
                    ?: "Please check the details you entered.",
                fieldErrors = fields,
                kind = AppError.Kind.Validation,
            )
        }

        code == 429 -> AppError(
            message = "Too many attempts. Please wait a moment and try again.",
            kind = AppError.Kind.Server,
        )

        code in 400..499 -> AppError(
            message = parsed?.message?.takeIf { it.isNotBlank() }
                ?: "That request could not be completed.",
            kind = AppError.Kind.Server,
        )

        else -> AppError(
            message = "The server is having trouble right now. Please try again shortly.",
            kind = AppError.Kind.Server,
        )
    }
}
