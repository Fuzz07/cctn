package com.cctn.app.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * The web app's palette, transcribed.
 *
 * Every value below is one of the custom properties in
 * public/assets/css/style.css, named after the variable it comes from so the
 * two can be diffed by eye. Change a colour there and change it here; the app
 * and the site are meant to read as one product.
 */

// ── Brand: --primary, --primary-dark, --primary-light ────────────────────────
val BrandRed = Color(0xFFDC2626)
val BrandRedDark = Color(0xFF991B1B)
val BrandRedLight = Color(0xFFEF4444)

/** A soft red ground for selected states. The web uses it on badges and chips. */
val BrandRedTint = Color(0xFFFEE2E2)

// ── Surfaces: --bg-page, --bg-card ───────────────────────────────────────────
val LightBackground = Color(0xFFF8FAFC)
val LightSurface = Color(0xFFFFFFFF)
val LightSurfaceVariant = Color(0xFFF1F5F9)

// ── Text: --text-dark, --text-body, --text-muted ─────────────────────────────
val LightOnSurface = Color(0xFF0F172A)
val LightOnSurfaceBody = Color(0xFF334155)
val LightOnSurfaceVariant = Color(0xFF64748B)

// ── Lines: --border ──────────────────────────────────────────────────────────
val LightOutline = Color(0xFFCBD5E1)

// ── Feedback: --success, --warning, --danger, --info ─────────────────────────
val FeedbackSuccess = Color(0xFF10B981)
val FeedbackWarning = Color(0xFFF59E0B)
val FeedbackDanger = Color(0xFFEF4444)
val FeedbackInfo = Color(0xFF3B82F6)

// ── Dark surfaces ────────────────────────────────────────────────────────────
// The site has no dark mode to copy, so these are the same slate ramp the web
// palette is built from, read from the dark end instead of the light one.
val DarkBackground = Color(0xFF0F131A)
val DarkSurface = Color(0xFF181D26)
val DarkSurfaceVariant = Color(0xFF232936)
val DarkOnSurface = Color(0xFFE9EDF5)
val DarkOnSurfaceVariant = Color(0xFF9AA5B8)
val DarkOutline = Color(0xFF39414F)

// ── Status ───────────────────────────────────────────────────────────────────
// Solid accents, used where a single colour is needed (bars, dots, borders).
val StatusPending = FeedbackWarning
val StatusApproved = FeedbackSuccess
val StatusCompleted = FeedbackInfo
val StatusCancelled = FeedbackDanger
val StatusNeutral = Color(0xFF64748B)

val ConnectionOffline = Color(0xFFDC2626)
val ConnectionOnline = Color(0xFF10B981)

/**
 * A status badge: tinted ground, dark label, matching border.
 *
 * These are the .badge-* rules in style.css. Each triplet is one Tailwind
 * ramp at 50 / 800 / 300, which is how the web ones were picked, so the
 * "completed" and "neutral" variants the site has no badge for follow the
 * same construction rather than being invented.
 */
data class StatusColors(
    val container: Color,
    val content: Color,
    val border: Color,
)

// .badge-pending — amber 50 / 800 / 300
val StatusPendingColors = StatusColors(Color(0xFFFFFBEB), Color(0xFF92400E), Color(0xFFFCD34D))

// .badge-approved — emerald 50 / 800 / 300
val StatusApprovedColors = StatusColors(Color(0xFFECFDF5), Color(0xFF065F46), Color(0xFF6EE7B7))

// .badge-cancelled — red 50 / 800 / 300
val StatusCancelledColors = StatusColors(Color(0xFFFEF2F2), Color(0xFF991B1B), Color(0xFFFCA5A5))

// blue 50 / 800 / 300, matching --info
val StatusCompletedColors = StatusColors(Color(0xFFEFF6FF), Color(0xFF1E40AF), Color(0xFF93C5FD))

// slate 50 / 700 / 300
val StatusNeutralColors = StatusColors(Color(0xFFF8FAFC), Color(0xFF334155), Color(0xFFCBD5E1))
