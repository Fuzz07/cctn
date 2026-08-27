package com.cctn.app.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.em
import androidx.compose.ui.unit.sp

private val Default = FontFamily.SansSerif

/**
 * The site's type scale, in sp.
 *
 * The web sets `font-family: system-ui` and leans on two weights the default
 * Compose scale does not use: 800 for headings (`.c-dash-welcome h1`,
 * `.c-stat-val`, `.c-profile-name`) and 700 for every field label and card
 * title. Both are spelled out here so a screen never has to override a weight
 * inline to match a page.
 */
val CctnTypography = Typography(
    // .c-dash-welcome h1 — 1.65rem / 800
    headlineMedium = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.ExtraBold,
        fontSize = 26.sp,
        lineHeight = 32.sp,
    ),
    // .auth-form-title — 1.6rem / 700
    headlineSmall = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Bold,
        fontSize = 22.sp,
        lineHeight = 28.sp,
    ),
    // .c-profile-name — 1.2rem / 800
    titleLarge = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.ExtraBold,
        fontSize = 19.sp,
        lineHeight = 25.sp,
    ),
    // .c-card-title — 1.1rem / 700
    titleMedium = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Bold,
        fontSize = 17.sp,
        lineHeight = 23.sp,
    ),
    bodyLarge = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Normal,
        fontSize = 16.sp,
        lineHeight = 23.sp,
    ),
    // The body copy size the site uses almost everywhere — 0.88–0.9rem.
    bodyMedium = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Normal,
        fontSize = 14.sp,
        lineHeight = 20.sp,
    ),
    bodySmall = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Normal,
        fontSize = 12.sp,
        lineHeight = 17.sp,
    ),
    // .auth-input-group label / .c-label / button text — 700
    labelLarge = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Bold,
        fontSize = 14.sp,
        lineHeight = 19.sp,
    ),
    labelMedium = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Bold,
        fontSize = 13.sp,
        lineHeight = 17.sp,
    ),
    // .c-stat-title / .c-info-item span — 0.72–0.75rem / 700, uppercase, tracked.
    // The tracking is set here so no caller has to remember it.
    labelSmall = TextStyle(
        fontFamily = Default,
        fontWeight = FontWeight.Bold,
        fontSize = 11.sp,
        lineHeight = 15.sp,
        letterSpacing = 0.05.em,
    ),
)
