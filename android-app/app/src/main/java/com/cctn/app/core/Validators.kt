package com.cctn.app.core

/**
 * Client-side mirror of app/Support/InputRules.php.
 *
 * These exist so the user sees a mistake as they type rather than after a
 * round trip. The server rules remain the ones that actually protect the
 * database, so any change there has to be repeated here.
 *
 * Values are trimmed before they are checked, matching Laravel's TrimStrings
 * middleware, so the two sides agree on what the value even is.
 *
 * Every pattern is then anchored with \A...\z rather than ^...$: in Java a bare
 * '$' also matches just before a final line terminator, so /^[a-z]+$/ would
 * accept a value with a newline embedded in it.
 */
object Validators {

    /** Letters, spaces, hyphens, apostrophes. */
    private val NAME = Regex("""\A[\p{L} '\-]+\z""")

    /** Letters, digits, spaces and address punctuation. */
    private val ADDRESS = Regex("""\A[\p{L}\d .,'\-/]+\z""")

    /** Philippine mobile: 11 digits starting 09. */
    private val MOBILE = Regex("""\A09[0-9]{9}\z""")

    private val EMAIL = Regex("""\A[^@\s]+@[^@\s]+\.[^@\s]{2,}\z""")

    const val NAME_ERROR = "Please enter letters only."
    const val ADDRESS_ERROR = "Please enter a valid address."
    const val MOBILE_ERROR =
        "Mobile number must be exactly 11 digits in the Philippine format (e.g. 09171234567)."

    fun name(value: String, label: String, required: Boolean = true, max: Int = 50): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return if (required) "$label is required." else null
        if (trimmed.length > max) return "$label must be $max characters or fewer."
        return if (NAME.matches(trimmed)) null else NAME_ERROR
    }

    fun address(value: String, label: String, required: Boolean = true, max: Int = 100): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return if (required) "$label is required." else null
        if (trimmed.length > max) return "$label must be $max characters or fewer."
        return if (ADDRESS.matches(trimmed)) null else ADDRESS_ERROR
    }

    fun mobile(value: String): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return "Mobile number is required."
        return if (MOBILE.matches(trimmed)) null else MOBILE_ERROR
    }

    fun email(value: String): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return "Email is required."
        if (trimmed.length > 100) return "Email must be 100 characters or fewer."
        return if (EMAIL.matches(trimmed)) null else "Please enter a valid email address."
    }

    fun username(value: String): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return "Username is required."
        if (trimmed.length > 50) return "Username must be 50 characters or fewer."
        return null
    }

    fun password(value: String): String? = when {
        value.isEmpty() -> "Password is required."
        value.length < 8 -> "Password must be at least 8 characters."
        else -> null
    }

    fun passwordConfirmation(password: String, confirmation: String): String? = when {
        confirmation.isEmpty() -> "Please confirm your password."
        confirmation != password -> "Passwords do not match."
        else -> null
    }

    fun required(value: String, label: String, max: Int? = null): String? {
        val trimmed = value.trim()
        if (trimmed.isEmpty()) return "$label is required."
        if (max != null && trimmed.length > max) return "$label must be $max characters or fewer."
        return null
    }

    /** Keeps a mobile field to the 11 digits the server accepts as the user types. */
    fun sanitizeMobile(input: String): String = input.filter { it.isDigit() }.take(11)
}
