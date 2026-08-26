package com.cctn.app.core

import java.time.LocalDate
import java.time.LocalTime
import java.time.OffsetDateTime
import java.time.Period
import java.time.format.DateTimeFormatter
import java.util.Locale

/**
 * Presentation helpers for the values the API sends as strings.
 *
 * Each one falls back to the raw input when parsing fails, so an unexpected
 * format shows something readable instead of blanking the field.
 */
object Formatters {

    private val DISPLAY_DATE: DateTimeFormatter =
        DateTimeFormatter.ofPattern("MMM d, yyyy", Locale.US)

    private val DISPLAY_DATE_SHORT: DateTimeFormatter =
        DateTimeFormatter.ofPattern("EEE, MMM d", Locale.US)

    private val DISPLAY_TIME: DateTimeFormatter =
        DateTimeFormatter.ofPattern("h:mm a", Locale.US)

    val API_DATE: DateTimeFormatter = DateTimeFormatter.ISO_LOCAL_DATE

    /** "2026-03-14" -> "Mar 14, 2026" */
    fun date(value: String?): String {
        if (value.isNullOrBlank()) return "—"
        return runCatching { LocalDate.parse(value).format(DISPLAY_DATE) }.getOrDefault(value)
    }

    /** "2026-03-14" -> "Sat, Mar 14" */
    fun dateShort(value: String?): String {
        if (value.isNullOrBlank()) return "—"
        return runCatching { LocalDate.parse(value).format(DISPLAY_DATE_SHORT) }.getOrDefault(value)
    }

    /** "14:30" -> "2:30 PM" */
    fun time(value: String?): String {
        if (value.isNullOrBlank()) return "—"
        return runCatching {
            LocalTime.parse(value.take(5)).format(DISPLAY_TIME)
        }.getOrDefault(value)
    }

    /** An ISO-8601 timestamp from the API, shown as a plain date. */
    fun timestamp(value: String?): String {
        if (value.isNullOrBlank()) return "—"
        return runCatching {
            OffsetDateTime.parse(value).toLocalDate().format(DISPLAY_DATE)
        }.getOrDefault(date(value.take(10)))
    }

    /**
     * Completed years between a birthdate and today.
     *
     * The server recomputes this from the birthdate it is sent
     * (Carbon's ->age, in AuthController and ProfileController), so this is
     * only what the customer sees while filling the form. Both count completed
     * years, so the two agree.
     *
     * A birthdate in the future has no sensible age and returns null rather
     * than a negative number.
     */
    fun age(birthdate: LocalDate?, today: LocalDate = LocalDate.now()): Int? {
        if (birthdate == null || birthdate.isAfter(today)) return null
        return Period.between(birthdate, today).years
    }

    /** Philippine peso, always with two decimals. */
    fun peso(amount: Double): String = "\u20B1" + String.format(Locale.US, "%,.2f", amount)

    fun titleCase(value: String?): String {
        if (value.isNullOrBlank()) return "—"
        return value.split(' ', '_', '-')
            .filter { it.isNotBlank() }
            .joinToString(" ") { word ->
                word.lowercase(Locale.US).replaceFirstChar { it.uppercase(Locale.US) }
            }
    }

    fun initials(firstname: String?, lastname: String?): String {
        val first = firstname?.trim()?.firstOrNull()?.uppercaseChar()
        val last = lastname?.trim()?.firstOrNull()?.uppercaseChar()
        return listOfNotNull(first, last).joinToString("").ifEmpty { "?" }
    }
}
