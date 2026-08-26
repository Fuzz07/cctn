package com.cctn.app.ui.screens.home

import com.cctn.app.data.remote.dto.AppointmentDto
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test
import java.time.LocalDate

class UpcomingAppointmentTest {

    private val today = LocalDate.of(2026, 3, 14)

    private fun appointment(
        id: Int,
        date: String?,
        time: String? = "09:00",
        status: String = "pending",
    ) = AppointmentDto(id = id, preferredDate = date, preferredTime = time, status = status)

    @Test
    fun `picks the soonest appointment from today onwards`() {
        // The API returns these newest-first, so the answer is not simply first().
        val list = listOf(
            appointment(1, "2026-04-02"),
            appointment(2, "2026-03-20"),
            appointment(3, "2026-03-01"),
        )

        assertEquals(2, list.nextUpcoming(today)?.id)
    }

    @Test
    fun `an appointment later today still counts`() {
        val list = listOf(appointment(1, "2026-03-14", time = "16:00"))
        assertEquals(1, list.nextUpcoming(today)?.id)
    }

    @Test
    fun `same day appointments are ordered by time`() {
        val list = listOf(
            appointment(1, "2026-03-14", time = "16:00"),
            appointment(2, "2026-03-14", time = "08:00"),
        )

        assertEquals(2, list.nextUpcoming(today)?.id)
    }

    @Test
    fun `cancelled appointments are skipped`() {
        val list = listOf(
            appointment(1, "2026-03-16", status = "cancelled"),
            appointment(2, "2026-03-18", status = "approved"),
        )

        assertEquals(2, list.nextUpcoming(today)?.id)
    }

    @Test
    fun `past appointments are ignored`() {
        val list = listOf(appointment(1, "2026-03-13"), appointment(2, "2026-01-01"))
        assertNull(list.nextUpcoming(today))
    }

    @Test
    fun `a missing or malformed date does not crash the home screen`() {
        val list = listOf(appointment(1, null), appointment(2, "not-a-date"))
        assertNull(list.nextUpcoming(today))
    }

    @Test
    fun `an empty list has no upcoming appointment`() {
        assertNull(emptyList<AppointmentDto>().nextUpcoming(today))
    }
}
