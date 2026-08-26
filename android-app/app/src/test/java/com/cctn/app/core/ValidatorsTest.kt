package com.cctn.app.core

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Test

/**
 * These mirror app/Support/InputRules.php. If a rule changes on the server,
 * one of these should fail.
 */
class ValidatorsTest {

    // ── Names ────────────────────────────────────────────────────────────────

    @Test
    fun `name accepts letters spaces hyphens and apostrophes`() {
        assertNull(Validators.name("Maria Clara", "First name"))
        assertNull(Validators.name("O'Brien", "Last name"))
        assertNull(Validators.name("Smith-Jones", "Last name"))
        // \p{L} is unicode-aware, matching the server's /u modifier.
        assertNull(Validators.name("José", "First name"))
    }

    @Test
    fun `name rejects digits and punctuation`() {
        assertEquals(Validators.NAME_ERROR, Validators.name("Juan2", "First name"))
        assertEquals(Validators.NAME_ERROR, Validators.name("Juan.", "First name"))
        assertEquals(Validators.NAME_ERROR, Validators.name("<script>", "First name"))
    }

    @Test
    fun `name trims surrounding whitespace before checking, as Laravel does`() {
        // TrimStrings runs on every request server-side, so the value that gets
        // validated there has already lost its surrounding whitespace. Trimming
        // here first keeps the two answers the same.
        assertNull(Validators.name("  Juan  ", "First name"))
        assertNull(Validators.name("Juan\n", "First name"))
    }

    @Test
    fun `name rejects a newline inside the value`() {
        // Anchoring with \A and \z rather than ^ and $ is what makes this fail:
        // a bare '$' in Java also matches before a final line terminator.
        assertEquals(Validators.NAME_ERROR, Validators.name("Juan\nMaria", "First name"))
    }

    @Test
    fun `name required only when asked`() {
        assertNotNull(Validators.name("", "First name", required = true))
        assertNull(Validators.name("", "Middle name", required = false))
        assertNull(Validators.name("   ", "Middle name", required = false))
    }

    @Test
    fun `name enforces max length`() {
        assertNotNull(Validators.name("a".repeat(51), "First name", max = 50))
        assertNull(Validators.name("a".repeat(50), "First name", max = 50))
    }

    // ── Mobile ───────────────────────────────────────────────────────────────

    @Test
    fun `mobile accepts an 11 digit 09 number`() {
        assertNull(Validators.mobile("09171234567"))
    }

    @Test
    fun `mobile rejects anything but 11 digits starting 09`() {
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("0917123456"))
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("091712345678"))
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("+639171234567"))
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("19171234567"))
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("0917123456a"))
        assertEquals(Validators.MOBILE_ERROR, Validators.mobile("0917 123 4567"))
    }

    @Test
    fun `sanitizeMobile keeps at most 11 digits`() {
        assertEquals("09171234567", Validators.sanitizeMobile("0917-123-4567"))
        assertEquals("09171234567", Validators.sanitizeMobile("091712345678999"))
        assertEquals("0917", Validators.sanitizeMobile("(0917)"))
    }

    // ── Address ──────────────────────────────────────────────────────────────

    @Test
    fun `address allows digits and address punctuation`() {
        assertNull(Validators.address("Blk 4, Lot 12-A", "Barangay"))
        assertNull(Validators.address("Puting Bato", "Barangay"))
        assertNull(Validators.address("Purok 3 / Sitio Ubos", "Barangay"))
    }

    @Test
    fun `address rejects other symbols`() {
        assertEquals(Validators.ADDRESS_ERROR, Validators.address("Blk 4 #12", "Barangay"))
        assertEquals(Validators.ADDRESS_ERROR, Validators.address("Ticad\nSuba", "Barangay"))
    }

    // ── Email, username, password ────────────────────────────────────────────

    @Test
    fun `email requires a plausible address`() {
        assertNull(Validators.email("juan@example.com"))
        assertNotNull(Validators.email("juan@example"))
        assertNotNull(Validators.email("juan.example.com"))
        assertNotNull(Validators.email(""))
        assertNotNull(Validators.email("juan @example.com"))
    }

    @Test
    fun `password requires eight characters`() {
        assertNotNull(Validators.password(""))
        assertNotNull(Validators.password("short7c"))
        assertNull(Validators.password("longenough"))
    }

    @Test
    fun `password confirmation must match`() {
        assertNull(Validators.passwordConfirmation("password1", "password1"))
        assertNotNull(Validators.passwordConfirmation("password1", "password2"))
        assertNotNull(Validators.passwordConfirmation("password1", ""))
    }

    @Test
    fun `required trims before deciding`() {
        assertNotNull(Validators.required("   ", "Subject"))
        assertNull(Validators.required(" hello ", "Subject"))
        assertNotNull(Validators.required("a".repeat(151), "Subject", max = 150))
    }
}
