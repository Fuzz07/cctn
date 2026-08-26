package com.cctn.app.core

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test
import java.time.LocalDate

class FormattersTest {

    @Test
    fun `date renders the API format`() {
        assertEquals("Mar 14, 2026", Formatters.date("2026-03-14"))
    }

    @Test
    fun `time renders 24 hour input as 12 hour`() {
        assertEquals("2:30 PM", Formatters.time("14:30"))
        assertEquals("9:00 AM", Formatters.time("09:00"))
        // The API sometimes includes seconds; only HH:mm is significant.
        assertEquals("9:00 AM", Formatters.time("09:00:00"))
    }

    @Test
    fun `timestamp reduces an ISO-8601 value to its date`() {
        assertEquals("Mar 14, 2026", Formatters.timestamp("2026-03-14T08:30:00+08:00"))
    }

    @Test
    fun `unparseable values fall back to the raw input rather than blanking`() {
        assertEquals("not a date", Formatters.date("not a date"))
        assertEquals("25:99", Formatters.time("25:99"))
    }

    @Test
    fun `null and blank values render as a dash`() {
        assertEquals("—", Formatters.date(null))
        assertEquals("—", Formatters.time(""))
        assertEquals("—", Formatters.timestamp(null))
    }

    @Test
    fun `age counts completed years, the way Carbon does server-side`() {
        val today = LocalDate.of(2026, 8, 26)

        assertEquals(31, Formatters.age(LocalDate.of(1995, 6, 12), today))
        // Birthday today: the year has just completed.
        assertEquals(31, Formatters.age(LocalDate.of(1995, 8, 26), today))
        // Birthday tomorrow: still a year short.
        assertEquals(30, Formatters.age(LocalDate.of(1995, 8, 27), today))
        assertEquals(0, Formatters.age(LocalDate.of(2026, 1, 1), today))
    }

    @Test
    fun `age handles a leap day birthdate in a non-leap year`() {
        assertEquals(4, Formatters.age(LocalDate.of(2020, 2, 29), LocalDate.of(2025, 2, 28)))
        assertEquals(5, Formatters.age(LocalDate.of(2020, 2, 29), LocalDate.of(2025, 3, 1)))
    }

    @Test
    fun `age is absent rather than negative for a future or missing birthdate`() {
        val today = LocalDate.of(2026, 8, 26)

        assertNull(Formatters.age(LocalDate.of(2026, 8, 27), today))
        assertNull(Formatters.age(null, today))
    }

    @Test
    fun `peso always shows two decimals and thousands separators`() {
        assertEquals("₱1,250.00", Formatters.peso(1250.0))
        assertEquals("₱0.00", Formatters.peso(0.0))
        assertEquals("₱12,345.68", Formatters.peso(12345.678))
    }

    @Test
    fun `titleCase normalises the statuses the API sends`() {
        assertEquals("In Progress", Formatters.titleCase("in_progress"))
        assertEquals("Pending", Formatters.titleCase("PENDING"))
        assertEquals("—", Formatters.titleCase(null))
    }

    @Test
    fun `initials use the first letter of each name`() {
        assertEquals("JD", Formatters.initials("Juan", "Dela Cruz"))
        assertEquals("J", Formatters.initials("Juan", null))
        assertEquals("?", Formatters.initials(null, null))
        assertEquals("?", Formatters.initials("  ", ""))
    }
}
