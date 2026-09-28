package com.cctn.app.ui.screens.auth

import org.junit.Assert.assertEquals
import org.junit.Assert.assertFalse
import org.junit.Assert.assertNotNull
import org.junit.Assert.assertNull
import org.junit.Assert.assertTrue
import org.junit.Test

class RecaptchaStatusTest {

    @Test
    fun `only a verified check carries a token`() {
        assertEquals("abc", RecaptchaStatus.Verified("abc").token)
        assertNull(RecaptchaStatus.Unverified().token)
        assertNull(RecaptchaStatus.NotRequired.token)
    }

    @Test
    fun `a form can go once the check is verified or switched off`() {
        assertFalse(RecaptchaStatus.Unverified().isDone)
        assertTrue(RecaptchaStatus.Verified("abc").isDone)
        assertTrue(RecaptchaStatus.NotRequired.isDone)
    }

    @Test
    fun `a token that has been sent is not offered again`() {
        val spent = RecaptchaStatus.Verified("abc").spent()

        assertTrue(spent is RecaptchaStatus.Unverified)
        assertNull(spent.token)
        assertNotNull((spent as RecaptchaStatus.Unverified).note)
    }

    @Test
    fun `sending leaves a switched-off check switched off`() {
        assertEquals(RecaptchaStatus.NotRequired, RecaptchaStatus.NotRequired.spent())
    }

    @Test
    fun `only a verified check can time out`() {
        assertTrue(RecaptchaStatus.Verified("abc").timedOut() is RecaptchaStatus.Unverified)
        assertEquals(RecaptchaStatus.NotRequired, RecaptchaStatus.NotRequired.timedOut())

        // Already reset, say by a failed sign-in: the reason given then stands.
        val reset = RecaptchaStatus.Unverified("Please verify again.")
        assertEquals(reset, reset.timedOut())
    }
}
