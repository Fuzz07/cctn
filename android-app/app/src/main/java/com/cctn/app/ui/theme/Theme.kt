package com.cctn.app.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

private val LightColors = lightColorScheme(
    primary = BrandRed,
    onPrimary = Color.White,
    primaryContainer = BrandRedLight,
    onPrimaryContainer = BrandRedDark,
    secondary = LightOnSurfaceVariant,
    onSecondary = Color.White,
    background = LightBackground,
    onBackground = LightOnSurface,
    surface = LightSurface,
    onSurface = LightOnSurface,
    surfaceVariant = LightSurfaceVariant,
    onSurfaceVariant = LightOnSurfaceVariant,
    // Material 3 tints raised surfaces with surfaceTint, which defaults to
    // the primary colour. With a primary this saturated that turns every
    // card, dialog and the navigation bar pink. Tinting with the surface
    // colour itself keeps elevation as shadow only.
    surfaceTint = LightSurface,
    outline = LightOutline,
    outlineVariant = LightOutline,
    error = StatusCancelled,
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
    surfaceTint = DarkSurface,
    outline = DarkOutline,
    outlineVariant = DarkOutline,
    error = Color(0xFFF87171),
    onError = Color(0xFF450A0A),
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
        content = content,
    )
}

/** The colour that stands for a status string coming back from the API. */
@Composable
fun statusColor(status: String?): Color = when (status?.lowercase()) {
    "approved", "confirmed", "resolved", "paid", "completed" -> StatusApproved
    "in_progress", "in progress", "ongoing" -> StatusCompleted
    "pending", "unpaid", "processing" -> StatusPending
    "cancelled", "canceled", "rejected", "overdue" -> StatusCancelled
    else -> StatusNeutral
}
