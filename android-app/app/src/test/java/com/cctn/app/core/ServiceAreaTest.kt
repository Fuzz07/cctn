package com.cctn.app.core

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test

/**
 * The barangay list has to follow the chosen municipality, in both directions.
 * These mirror the BARANGAYS map the web forms use.
 */
class ServiceAreaTest {

    @Test
    fun `each municipality offers its own barangays`() {
        assertEquals(24, ServiceArea.barangays("Bantayan").size)
        assertEquals(10, ServiceArea.barangays("Santa Fe").size)
        assertEquals(14, ServiceArea.barangays("Madridejos").size)
    }

    @Test
    fun `selecting Madridejos offers Madridejos barangays and not Bantayan ones`() {
        val madridejos = ServiceArea.barangays("Madridejos")

        assertTrue(madridejos.contains("Kangwayan"))
        assertTrue(madridejos.contains("Tarong"))
        assertFalse(madridejos.contains("Ticad"))
        assertFalse(madridejos.contains("Atop-atop"))
    }

    @Test
    fun `and the other way round`() {
        val bantayan = ServiceArea.barangays("Bantayan")

        assertTrue(bantayan.contains("Ticad"))
        assertTrue(bantayan.contains("Puting Bato"))
        assertFalse(bantayan.contains("Kangwayan"))
        assertFalse(bantayan.contains("Hagdan"))
    }

    @Test
    fun `Santa Fe is distinct from both`() {
        val santaFe = ServiceArea.barangays("Santa Fe")

        assertTrue(santaFe.contains("Kinatarkan"))
        assertFalse(santaFe.contains("Ticad"))
        assertFalse(santaFe.contains("Kangwayan"))
    }

    @Test
    fun `a barangay is only kept across a switch when it exists in both`() {
        // This is the rule the register and profile forms apply when the
        // municipality changes. Poblacion is the one name shared by two
        // municipalities, so it is the case that proves the rule is a
        // membership check and not a blanket reset.
        assertTrue(ServiceArea.barangays("Santa Fe").contains("Poblacion"))
        assertTrue(ServiceArea.barangays("Madridejos").contains("Poblacion"))
        assertFalse(ServiceArea.barangays("Bantayan").contains("Poblacion"))

        assertTrue(keptAcrossSwitch(from = "Santa Fe", to = "Madridejos", barangay = "Poblacion"))
        assertFalse(keptAcrossSwitch(from = "Santa Fe", to = "Bantayan", barangay = "Poblacion"))
        assertFalse(keptAcrossSwitch(from = "Bantayan", to = "Madridejos", barangay = "Ticad"))
    }

    @Test
    fun `an unknown municipality offers nothing rather than throwing`() {
        assertTrue(ServiceArea.barangays("Cebu City").isEmpty())
        assertTrue(ServiceArea.barangays("").isEmpty())
    }

    @Test
    fun `every municipality on offer has barangays behind it`() {
        ServiceArea.MUNICIPALITIES.forEach { municipality ->
            assertTrue(
                "$municipality has no barangays",
                ServiceArea.barangays(municipality).isNotEmpty(),
            )
        }
    }

    /** The selection rule used by RegisterViewModel and ProfileViewModel. */
    private fun keptAcrossSwitch(from: String, to: String, barangay: String): Boolean {
        require(ServiceArea.barangays(from).contains(barangay))
        return ServiceArea.barangays(to).contains(barangay)
    }
}
