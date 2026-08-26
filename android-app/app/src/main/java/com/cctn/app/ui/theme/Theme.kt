package com.cctn.app.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Shapes
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalView
import androidx.compose.ui.unit.dp
import androidx.core.view.WindowCompat

private val LightColors = lightColorScheme(
    primary = BrandRed,
    onPrimary = Color.White,
    primaryContainer = BrandRedTint,
    onPrimaryContainer = BrandRedDark,
    secondary = LightOnSurfaceVariant,
    onSecondary = Color.White,
    background = LightBackground,
    onBackground = LightOnSurface,
    surface = LightSurface,
    onSurface = LightOnSurface,
    surfaceVariant = LightSurfaceVariant,
    onSurfaceVariant = LightOnSurfaceVariant,
    outline = LightOutline,
    outlineVariant = LightOutline,
    error = FeedbackDanger,
    onError = Color.White,
)

private val DarkColors = darkColorScheme(
    primary = Color(0xFFF87171),
    onPrimary = Color(0xFF450A0A),
    primaryContainer = Color(0xFF7F1D1D),
    onPrimaryContainer = Color(0xFFFEE2E2),
    secondary = DarkOnSurfaceVariant,
    onSecondary = Color(0xFF0F131A),
    background = DarkBackground,
    onBackground = DarkOnSurface,
    surface = DarkSurface,
    onSurface = DarkOnSurface,
    surfaceVariant = DarkSurfaceVariant,
    onSurfaceVariant = DarkOnSurfaceVariant,
    outline = DarkOutline,
    outlineVariant = DarkOutline,
    error = Color(0xFFF87171),
    onError = Color(0xFF450A0A),
)

/** The web's --radius-sm / --radius-md / --radius-lg. */
private val CctnShapes = Shapes(
    extraSmall = RoundedCornerShape(6.dp),
    small = RoundedCornerShape(8.dp),
    medium = RoundedCornerShape(12.dp),
    large = RoundedCornerShape(20.dp),
    extraLarge = RoundedCornerShape(28.dp),
)

@Composable
fun CctnTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    val colorScheme = if (darkTheme) DarkColors else LightColors
    val view = LocalView.current

    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as Activity).window
            // The bars are drawn behind by the scaffold, so only the icon
            // contrast has to follow the theme.
            WindowCompat.getInsetsController(window, view).apply {
                isAppearanceLightStatusBars = !darkTheme
                isAppearanceLightNavigationBars = !darkTheme
            }
        }
    }

    MaterialTheme(
        colorScheme = colorScheme,
        typography = CctnTypography,
        shapes = CctnShapes,
        content = content,
    )
}

/** The solid accent for a status string, for bars, dots and rules. */
fun statusAccent(status: String?): Color = when (status?.lowercase()) {
    "approved", "confirmed", "resolved", "paid" -> StatusApproved
    "completed", "in_progress", "in progress", "ongoing" -> StatusCompleted
    "pending", "unpaid", "processing" -> StatusPending
    "cancelled", "canceled", "rejected", "overdue" -> StatusCancelled
    else -> StatusNeutral
}

/**
 * The badge treatment for a status: ground, label and border.
 *
 * Light mode uses the site's .badge-* values verbatim. Dark mode cannot —
 * those grounds are near-white — so it keeps the same accent and sits it on a
 * translucent version of itself, which is the same idea rendered for a dark
 * surface.
 */
@Composable
fun statusColors(status: String?): StatusColors {
    val light = when (status?.lowercase()) {
        "approved", "confirmed", "resolved", "paid" -> StatusApprovedColors
        "completed", "in_progress", "in progress", "ongoing" -> StatusCompletedColors
        "pending", "unpaid", "processing" -> StatusPendingColors
        "cancelled", "canceled", "rejected", "overdue" -> StatusCancelledColors
        else -> StatusNeutralColors
    }

    if (!isSystemInDarkTheme()) return light

    val accent = statusAccent(status)
    return StatusColors(
        container = accent.copy(alpha = 0.16f),
        content = accent,
        border = accent.copy(alpha = 0.45f),
    )
}
