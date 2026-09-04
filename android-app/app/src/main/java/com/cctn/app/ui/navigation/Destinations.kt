package com.cctn.app.ui.navigation

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AccountCircle
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Receipt
import androidx.compose.material.icons.filled.SupportAgent
import androidx.compose.material.icons.outlined.AccountCircle
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Receipt
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.ui.graphics.vector.ImageVector

object Routes {
    const val LOGIN = "login"
    const val REGISTER = "register"

    const val HOME = "home"
    const val APPOINTMENTS = "appointments"
    const val BILLING = "billing"
    const val SUPPORT = "support"
    const val PROFILE = "profile"

    const val BOOK = "book"
    const val NOTIFICATIONS = "notifications"
    const val PAYMENT_METHODS = "payment_methods"

    /** The assistant. Reachable from the bar on every tab, like the site's bubble. */
    const val CHAT = "chat"
}

/** The five tabs of the signed-in app. Admin areas have no route here at all. */
enum class TopLevelDestination(
    val route: String,
    val label: String,
    val selectedIcon: ImageVector,
    val unselectedIcon: ImageVector,
) {
    Home(Routes.HOME, "Home", Icons.Filled.Home, Icons.Outlined.Home),
    Appointments(
        Routes.APPOINTMENTS,
        "Bookings",
        Icons.Filled.CalendarMonth,
        Icons.Outlined.CalendarMonth,
    ),
    Billing(Routes.BILLING, "Billing", Icons.Filled.Receipt, Icons.Outlined.Receipt),
    Support(Routes.SUPPORT, "Support", Icons.Filled.SupportAgent, Icons.Outlined.SupportAgent),
    Profile(Routes.PROFILE, "Account", Icons.Filled.AccountCircle, Icons.Outlined.AccountCircle),
}
