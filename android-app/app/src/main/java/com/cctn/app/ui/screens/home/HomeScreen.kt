package com.cctn.app.ui.screens.home

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.AddCircleOutline
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Receipt
import androidx.compose.material.icons.filled.SupportAgent
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.components.StatusChip
import com.cctn.app.ui.theme.BrandRed
import com.cctn.app.ui.theme.BrandRedDark

@Composable
fun HomeScreen(
    onBook: () -> Unit,
    onSeeAppointments: () -> Unit,
    onSeeBilling: () -> Unit,
    onSupport: () -> Unit,
    viewModel: HomeViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val client by viewModel.client.collectAsStateWithLifecycle()

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        // The app-level Column already clears the status bar and the
        // bottom bar handles its own inset, so this Scaffold must not
        // add either a second time.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            CctnTopBar(
                title = "Home",
                refreshing = state.refreshing,
                onRefresh = viewModel::refresh,
            )
        },
    ) { padding ->
        if (state.loading) {
            LoadingState(Modifier.padding(padding))
            return@Scaffold
        }

        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            item {
                AccountHeader(
                    name = client?.firstname.orEmpty(),
                    accountNumber = client?.accountNumber,
                    balance = state.balance,
                    unpaidCount = state.unpaidCount,
                    onSeeBilling = onSeeBilling,
                )
            }

            item {
                Text(
                    text = "Quick actions",
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onBackground,
                )
            }

            item {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    QuickAction(
                        icon = Icons.Filled.AddCircleOutline,
                        label = "Book service",
                        onClick = onBook,
                        modifier = Modifier.weight(1f),
                    )
                    QuickAction(
                        icon = Icons.Filled.Receipt,
                        label = "My bills",
                        onClick = onSeeBilling,
                        modifier = Modifier.weight(1f),
                    )
                    QuickAction(
                        icon = Icons.Filled.SupportAgent,
                        label = "Get help",
                        onClick = onSupport,
                        modifier = Modifier.weight(1f),
                    )
                }
            }

            item {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Text(
                        text = "Next appointment",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onBackground,
                    )
                    Text(
                        text = "See all",
                        style = MaterialTheme.typography.labelLarge,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.clickable(onClick = onSeeAppointments),
                    )
                }
            }

            item {
                UpcomingCard(
                    upcoming = state.upcoming,
                    onBook = onBook,
                )
            }

            item {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    StatTile(
                        value = state.pendingCount.toString(),
                        label = "Pending",
                        modifier = Modifier.weight(1f),
                        onClick = onSeeAppointments,
                    )
                    StatTile(
                        value = state.totalAppointments.toString(),
                        label = "Bookings",
                        modifier = Modifier.weight(1f),
                        onClick = onSeeAppointments,
                    )
                    StatTile(
                        value = state.unpaidCount.toString(),
                        label = "Unpaid bills",
                        modifier = Modifier.weight(1f),
                        onClick = onSeeBilling,
                    )
                }
            }

            if (state.error != null) {
                item {
                    Text(
                        text = state.error.orEmpty(),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error,
                    )
                }
            }
        }
    }
}

@Composable
private fun UpcomingCard(
    upcoming: com.cctn.app.data.remote.dto.AppointmentDto?,
    onBook: () -> Unit,
) {
    SectionCard {
        if (upcoming == null) {
            Column(Modifier.padding(18.dp)) {
                Text(
                    text = "No upcoming appointment",
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Spacer(Modifier.height(4.dp))
                Text(
                    text = "Book an installation or a service visit and it will show up here.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                Spacer(Modifier.height(14.dp))
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    modifier = Modifier.clickable(onClick = onBook),
                ) {
                    Text(
                        text = "Book now",
                        style = MaterialTheme.typography.labelLarge,
                        color = MaterialTheme.colorScheme.primary,
                    )
                    Spacer(Modifier.size(4.dp))
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.size(16.dp),
                    )
                }
            }
        } else {
            Column(Modifier.padding(18.dp)) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.Top,
                ) {
                    Text(
                        text = upcoming.service?.name ?: "Service visit",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                        modifier = Modifier.weight(1f),
                    )
                    StatusChip(upcoming.status)
                }
                Spacer(Modifier.height(10.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(
                        imageVector = Icons.Filled.CalendarMonth,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.size(16.dp),
                    )
                    Spacer(Modifier.size(8.dp))
                    Text(
                        text = Formatters.date(upcoming.preferredDate) + "  ·  " +
                            Formatters.time(upcoming.preferredTime),
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                }
            }
        }
    }
}

@Composable
private fun AccountHeader(
    name: String,
    accountNumber: String?,
    balance: Double,
    unpaidCount: Int,
    onSeeBilling: () -> Unit,
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(18.dp))
            .background(Brush.linearGradient(listOf(BrandRed, BrandRedDark)))
            .clickable(onClick = onSeeBilling)
            .padding(20.dp),
    ) {
        Text(
            text = if (name.isBlank()) "Welcome" else "Hello, $name",
            style = MaterialTheme.typography.titleLarge,
            color = Color.White,
        )
        if (!accountNumber.isNullOrBlank()) {
            Spacer(Modifier.height(2.dp))
            Text(
                text = "Account $accountNumber",
                style = MaterialTheme.typography.bodySmall,
                color = Color.White.copy(alpha = 0.85f),
            )
        }

        Spacer(Modifier.height(18.dp))

        Text(
            text = "Outstanding balance",
            style = MaterialTheme.typography.bodySmall,
            color = Color.White.copy(alpha = 0.85f),
        )
        Spacer(Modifier.height(2.dp))
        Text(
            text = Formatters.peso(balance),
            style = MaterialTheme.typography.headlineMedium,
            color = Color.White,
        )
        Spacer(Modifier.height(4.dp))
        Text(
            text = when (unpaidCount) {
                0 -> "You are all paid up."
                1 -> "1 statement awaiting payment"
                else -> "$unpaidCount statements awaiting payment"
            },
            style = MaterialTheme.typography.bodySmall,
            color = Color.White.copy(alpha = 0.85f),
        )
    }
}

@Composable
private fun QuickAction(
    icon: ImageVector,
    label: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(16.dp))
            .background(MaterialTheme.colorScheme.surface)
            .clickable(onClick = onClick)
            .padding(vertical = 16.dp, horizontal = 8.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Icon(
            imageVector = icon,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.primary,
            modifier = Modifier.size(26.dp),
        )
        Spacer(Modifier.height(8.dp))
        Text(
            text = label,
            style = MaterialTheme.typography.labelMedium,
            color = MaterialTheme.colorScheme.onSurface,
        )
    }
}

@Composable
private fun StatTile(
    value: String,
    label: String,
    modifier: Modifier = Modifier,
    onClick: () -> Unit,
) {
    Column(
        modifier = modifier
            .clip(RoundedCornerShape(16.dp))
            .background(MaterialTheme.colorScheme.surface)
            .clickable(onClick = onClick)
            .padding(vertical = 16.dp, horizontal = 10.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(
            text = value,
            style = MaterialTheme.typography.headlineSmall,
            color = MaterialTheme.colorScheme.primary,
        )
        Spacer(Modifier.height(2.dp))
        Text(
            text = label,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )
    }
}
