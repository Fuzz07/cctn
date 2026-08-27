package com.cctn.app.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * The web app's palette, transcribed.
 *
 * Every value below comes from a rule in the site's own CSS, named after where
 * it is used so the two can be diffed by eye. Change a colour on the site and
 * change it here; the app and the site are meant to read as one product.
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

/** slate-400 — the eyebrow labels in the profile card and the "or" rule. */
val LightOnSurfaceFaint = Color(0xFF94A3B8)

// ── Lines ────────────────────────────────────────────────────────────────────
/** --border, and the 1px edge every .c-card draws. */
val LightOutline = Color(0xFFE2E8F0)

/** The heavier line an input draws: `.auth-input` / `.c-input` border. */
val FieldOutline = Color(0xFFCBD5E1)

/** The ground `.c-input` sits on until it takes focus. */
val FieldFill = Color(0xFFF8FAFC)

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

// ── The auth screens ─────────────────────────────────────────────────────────
/**
 * The scrim over the office photo behind the sign-in card.
 *
 * `login.blade.php` lays `linear-gradient(rgba(15,23,42,0.55), rgba(15,23,42,0.7))`
 * over the same JPEG, so the two ends are the same slate at the same two alphas.
 */
val AuthScrimTop = Color(0xFF0F172A).copy(alpha = 0.55f)
val AuthScrimBottom = Color(0xFF0F172A).copy(alpha = 0.70f)

/** The wordmark and copyright over the photo: slate-200 on a dark ground. */
val AuthOnScrim = Color(0xFFE2E8F0)

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
 * A status pill: a tinted ground and a saturated label, no border.
 *
 * These are the `.status-pill` rules in `client/dashboard.blade.php` — the
 * treatment the dashboard actually ships — rather than the older bordered
 * `.badge-*` rules elsewhere in style.css. Each pair is one Tailwind ramp at
 * 100 / 600, so the two statuses the dashboard has no pill for follow the same
 * construction instead of being invented.
 */
data class StatusColors(
    val container: Color,
    val content: Color,
)

// .status-pill.pending — amber 100 / 600
val StatusPendingColors = StatusColors(Color(0xFFFEF3C7), Color(0xFFD97706))

// .status-pill.approved — green 100 / 600
val StatusApprovedColors = StatusColors(Color(0xFFDCFCE7), Color(0xFF16A34A))

// .status-pill.cancelled — red 100 / 600
val StatusCancelledColors = StatusColors(Color(0xFFFEE2E2), Color(0xFFDC2626))

// blue 100 / 600, matching --info
val StatusCompletedColors = StatusColors(Color(0xFFDBEAFE), Color(0xFF2563EB))

// slate 100 / 600
val StatusNeutralColors = StatusColors(Color(0xFFF1F5F9), Color(0xFF475569))

/**
 * The four tinted icon squares on the dashboard, in the order they appear.
 *
 * Taken from the inline `style` on each `.c-stat-icon`.
 */
data class TintPair(val container: Color, val content: Color)

val TintTotal = TintPair(Color(0xFFFEF2F2), Color(0xFFDC2626))
val TintPendingStat = TintPair(Color(0xFFFFF7ED), Color(0xFFEA580C))
val TintApprovedStat = TintPair(Color(0xFFF0FDF4), Color(0xFF16A34A))
val TintCancelledStat = TintPair(Color(0xFFFEF2F2), Color(0xFFEF4444))
