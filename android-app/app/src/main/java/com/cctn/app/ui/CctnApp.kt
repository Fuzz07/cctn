package com.cctn.app.ui

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.navigation.NavDestination.Companion.hierarchy
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.cctn.app.data.session.SessionState
import com.cctn.app.ui.components.OfflineBanner
import com.cctn.app.ui.navigation.Routes
import com.cctn.app.ui.navigation.TopLevelDestination
import com.cctn.app.ui.screens.appointments.AppointmentsScreen
import com.cctn.app.ui.screens.appointments.BookScreen
import com.cctn.app.ui.screens.auth.LoginScreen
import com.cctn.app.ui.screens.auth.RegisterScreen
import com.cctn.app.ui.screens.billing.BillingScreen
import com.cctn.app.ui.screens.home.HomeScreen
import com.cctn.app.ui.screens.profile.ProfileScreen
import com.cctn.app.ui.screens.support.SupportScreen

/**
 * The whole app below the theme.
 *
 * Which graph is shown is driven purely by [SessionState], so a token that the
 * server rejects mid-session drops straight back to the login screen without
 * any screen having to handle it.
 */
@Composable
fun CctnApp(
    sessionState: SessionState,
    isOnline: Boolean,
) {
    Surface(
        modifier = Modifier.fillMaxSize(),
        color = MaterialTheme.colorScheme.background,
    ) {
        // The window is drawn edge to edge, so the status bar is cleared once
        // here for the whole app. Everything below — including each screen's
        // Scaffold — then runs with its own insets switched off, and the bottom
        // bar keeps its inset so its background still reaches the screen edge.
        Column(
            Modifier
                .fillMaxSize()
                .statusBarsPadding()
        ) {
            AnimatedVisibility(visible = !isOnline) { OfflineBanner() }

            when (sessionState) {
                // The splash screen stays up until the persisted session is read,
                // so there is nothing to draw here.
                SessionState.Loading -> Unit
                SessionState.SignedOut -> AuthNavHost()
                is SessionState.SignedIn -> MainNavHost()
            }
        }
    }
}

@Composable
private fun AuthNavHost(navController: NavHostController = rememberNavController()) {
    NavHost(navController = navController, startDestination = Routes.LOGIN) {
        composable(Routes.LOGIN) {
            LoginScreen(onRegister = { navController.navigate(Routes.REGISTER) })
        }
        composable(Routes.REGISTER) {
            RegisterScreen(onBack = { navController.popBackStack() })
        }
    }
}

@Composable
private fun MainNavHost(navController: NavHostController = rememberNavController()) {
    val backStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = backStackEntry?.destination?.route

    // Booking is a focused task: the tab bar goes away so the flow has the
    // whole screen and one obvious way back.
    val showBottomBar = currentRoute != Routes.BOOK

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        // Insets are handled once at the app level; the navigation bar
        // below applies its own.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        bottomBar = {
            if (showBottomBar) {
                NavigationBar(containerColor = MaterialTheme.colorScheme.surface) {
                    TopLevelDestination.entries.forEach { destination ->
                        val selected = backStackEntry?.destination?.hierarchy
                            ?.any { it.route == destination.route } == true

                        NavigationBarItem(
                            selected = selected,
                            onClick = { navController.navigateToTab(destination.route) },
                            icon = {
                                Icon(
                                    imageVector = if (selected) {
                                        destination.selectedIcon
                                    } else {
                                        destination.unselectedIcon
                                    },
                                    contentDescription = destination.label,
                                )
                            },
                            label = { Text(destination.label) },
                            colors = NavigationBarItemDefaults.colors(
                                selectedIconColor = MaterialTheme.colorScheme.primary,
                                selectedTextColor = MaterialTheme.colorScheme.primary,
                                indicatorColor = MaterialTheme.colorScheme.primaryContainer,
                            ),
                        )
                    }
                }
            }
        },
    ) { innerPadding ->
        NavHost(
            navController = navController,
            startDestination = Routes.HOME,
            modifier = Modifier.padding(innerPadding),
        ) {
            composable(Routes.HOME) {
                HomeScreen(
                    onBook = { navController.navigate(Routes.BOOK) },
                    onSeeAppointments = { navController.navigateToTab(Routes.APPOINTMENTS) },
                    onSeeBilling = { navController.navigateToTab(Routes.BILLING) },
                    onSupport = { navController.navigateToTab(Routes.SUPPORT) },
                )
            }
            composable(Routes.APPOINTMENTS) {
                AppointmentsScreen(onBook = { navController.navigate(Routes.BOOK) })
            }
            composable(Routes.BILLING) { BillingScreen() }
            composable(Routes.SUPPORT) { SupportScreen() }
            composable(Routes.PROFILE) { ProfileScreen() }
            composable(Routes.BOOK) {
                BookScreen(
                    onDone = {
                        navController.popBackStack()
                        navController.navigateToTab(Routes.APPOINTMENTS)
                    },
                    onBack = { navController.popBackStack() },
                )
            }
        }
    }
}

/**
 * Standard tab behaviour: one entry per tab on the back stack, and tapping the
 * tab you are already on does nothing rather than stacking a copy.
 */
private fun NavHostController.navigateToTab(route: String) {
    navigate(route) {
        popUpTo(graph.findStartDestination().id) { saveState = true }
        launchSingleTop = true
        restoreState = true
    }
}
