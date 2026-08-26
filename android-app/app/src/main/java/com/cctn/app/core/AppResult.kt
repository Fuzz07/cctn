package com.cctn.app.core

/** What went wrong, in terms the UI can act on. */
data class AppError(
    val message: String,
    val fieldErrors: Map<String, String> = emptyMap(),
    val kind: Kind = Kind.Unknown,
) {
    enum class Kind { Network, Unauthorized, Validation, Server, Unknown }

    val isUnauthorized: Boolean get() = kind == Kind.Unauthorized
}

sealed interface AppResult<out T> {
    data class Success<out T>(val data: T) : AppResult<T>
    data class Failure(val error: AppError) : AppResult<Nothing>
}

inline fun <T, R> AppResult<T>.map(transform: (T) -> R): AppResult<R> = when (this) {
    is AppResult.Success -> AppResult.Success(transform(data))
    is AppResult.Failure -> this
}

fun <T> AppResult<T>.getOrNull(): T? = (this as? AppResult.Success)?.data
