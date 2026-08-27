package com.cctn.app.ui.screens.home

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.ui.components.CardHeader
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.ClientAvatar
import com.cctn.app.ui.components.InfoItem
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.PageHeading
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.components.StatCard
import com.cctn.app.ui.components.StatusChip
import com.cctn.app.ui.theme.TintApprovedStat
import com.cctn.app.ui.theme.TintCancelledStat
import com.cctn.app.ui.theme.TintPendingStat
import com.cctn.app.ui.theme.TintTotal

/**
 * The customer dashboard, laid out as `client/dashboard.blade.php` lays it
 * out: the welcome line and its one red action, the four counters, then the
 * recent bookings and the profile summary.
 *
 * The web puts the counters in a single row of four and the two cards
 * side by side. Its own stylesheet already answers what to do when there is
 * no room for that — `repeat(2, 1fr)` under 1024px, one column under 640px —
 * so the counters are two across here and the cards are stacked.
 */
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
            CctnTopBar(refreshing = state.refreshing, onRefresh = viewModel::refresh)
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
            verticalArrangement = Arrangement.spacedBy(16.dp),
        ) {
            item {
                PageHeading(
                    title = "Welcome back, ${client?.firstname.orEmpty().ifBlank { "there" }}! 👋",
                    subtitle = "Manage your profile, monitor booking requests, and view service details.",
                )
            }

            item {
                LoadingButton(
                    text = "Book New Appointment",
                    onClick = onBook,
                    modifier = Modifier.fillMaxWidth(),
                    icon = Icons.Filled.Add,
                )
            }

            item {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    StatCard(
                        title = "Total Bookings",
                        value = state.totalAppointments.toString(),
                        icon = Icons.Filled.Description,
                        tint = TintTotal,
                        onClick = onSeeAppointments,
                        modifier = Modifier.weight(1f),
                    )
                    StatCard(
                        title = "Pending",
                        value = state.pendingCount.toString(),
                        icon = Icons.Filled.Schedule,
                        tint = TintPendingStat,
                        onClick = onSeeAppointments,
                        modifier = Modifier.weight(1f),
                    )
                }
            }

            item {
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    StatCard(
                        title = "Approved",
                        value = state.approvedCount.toString(),
                        icon = Icons.Filled.Check,
                        tint = TintApprovedStat,
                        onClick = onSeeAppointments,
                        modifier = Modifier.weight(1f),
                    )
                    StatCard(
                        title = "Cancelled",
                        value = state.cancelledCount.toString(),
                        icon = Icons.Filled.Close,
                        tint = TintCancelledStat,
                        onClick = onSeeAppointments,
                        modifier = Modifier.weight(1f),
                    )
                }
            }

            item {
                RecentAppointmentsCard(
                    recent = state.recent,
                    onSeeAll = onSeeAppointments,
                    onBook = onBook,
                )
            }

            item {
                BalanceCard(
                    balance = state.balance,
                    unpaidCount = state.unpaidCount,
                    onSeeBilling = onSeeBilling,
                )
            }

            item {
                client?.let { ProfileSummaryCard(it) }
            }

            item {
                SectionCard(onClick = onSupport) {
                    Column(Modifier.padding(18.dp)) {
                        Text(
                            text = "Need help with your connection?",
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.onSurface,
                        )
                        Spacer(Modifier.height(4.dp))
                        Text(
                            text = "Report a fault and track it from the Support tab.",
                            style = MaterialTheme.typography.bodyMedium,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
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

/**
 * The dashboard's table of recent bookings.
 *
 * The web has four columns and lets the table scroll sideways when it does not
 * fit. Sideways scrolling inside a phone-width list is the wrong trade, so each
 * booking becomes one row that still carries all four values: the service and
 * when it is, against the status and the reference.
 */
@Composable
private fun RecentAppointmentsCard(
    recent: List<AppointmentDto>,
    onSeeAll: () -> Unit,
    onBook: () -> Unit,
) {
    SectionCard {
        Column(Modifier.padding(18.dp)) {
            CardHeader(
                title = "Recent Appointments",
                icon = Icons.Filled.CalendarMonth,
                linkText = "View History →",
                onLink = onSeeAll,
            )

            Spacer(Modifier.height(6.dp))

            if (recent.isEmpty()) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 24.dp),
                    horizontalAlignment = Alignment.CenterHorizontally,
                ) {
                    Text(
                        text = "No recent appointments found.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        textAlign = TextAlign.Center,
                    )
                    Spacer(Modifier.height(12.dp))
                    Text(
                        text = "Book your first visit →",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.clickable(onClick = onBook),
                    )
                }
                return@Column
            }

            recent.forEachIndexed { index, appointment ->
                if (index > 0) {
                    Box(
                        Modifier
                            .fillMaxWidth()
                            .height(1.dp)
                            .background(MaterialTheme.colorScheme.outlineVariant)
                    )
                }
                AppointmentRow(appointment)
            }
        }
    }
}

@Composable
private fun AppointmentRow(appointment: AppointmentDto) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 14.dp),
        verticalAlignment = Alignment.Top,
    ) {
        Column(Modifier.weight(1f)) {
            Text(
                text = appointment.service?.name ?: "Service visit",
                style = MaterialTheme.typography.labelLarge,
                color = MaterialTheme.colorScheme.onSurface,
            )
            Spacer(Modifier.height(3.dp))
            Text(
                text = Formatters.date(appointment.preferredDate) +
                    "  ·  " + Formatters.time(appointment.preferredTime),
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
            appointment.service?.durationMin?.let { minutes ->
                Spacer(Modifier.height(2.dp))
                Text(
                    text = "~$minutes mins",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }
        }

        Spacer(Modifier.width(10.dp))

        Column(horizontalAlignment = Alignment.End) {
            StatusChip(appointment.status)
            Spacer(Modifier.height(6.dp))
            Text(
                // The web pads the id to five digits and sets it in a mono face.
                text = "#" + appointment.id.toString().padStart(5, '0'),
                style = MaterialTheme.typography.bodySmall.copy(fontFamily = FontFamily.Monospace),
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }
    }
}

/**
 * Outstanding balance.
 *
 * The web dashboard has no such panel — billing is its own page there — but the
 * app already loads the figure for this screen and billing is one of its five
 * tabs, so it is shown in the same card the rest of the page uses.
 */
@Composable
private fun BalanceCard(balance: Double, unpaidCount: Int, onSeeBilling: () -> Unit) {
    SectionCard(onClick = onSeeBilling) {
        Column(Modifier.padding(18.dp)) {
            CardHeader(
                title = "Outstanding Balance",
                icon = Icons.Filled.Description,
                linkText = "View Billing →",
                onLink = onSeeBilling,
            )

            Spacer(Modifier.height(14.dp))

            Text(
                text = Formatters.peso(balance),
                style = MaterialTheme.typography.headlineMedium,
                color = if (balance > 0) {
                    MaterialTheme.colorScheme.primary
                } else {
                    MaterialTheme.colorScheme.onSurface
                },
            )
            Spacer(Modifier.height(4.dp))
            Text(
                text = when (unpaidCount) {
                    0 -> "You are all paid up."
                    1 -> "1 statement awaiting payment"
                    else -> "$unpaidCount statements awaiting payment"
                },
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }
    }
}

/** The right-hand card on the web dashboard (`.c-profile-card`). */
@Composable
private fun ProfileSummaryCard(client: ClientDto) {
    SectionCard {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(18.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            ClientAvatar(
                photoUrl = client.profilePhoto,
                initials = Formatters.initials(client.firstname, client.lastname),
                size = 96.dp,
            )

            Spacer(Modifier.height(12.dp))

            Text(
                text = client.fullName.ifBlank { "${client.firstname} ${client.lastname}".trim() },
                style = MaterialTheme.typography.titleLarge,
                color = MaterialTheme.colorScheme.onSurface,
            )
            Spacer(Modifier.height(3.dp))
            Text(
                text = "@" + client.username,
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.primary,
            )

            Spacer(Modifier.height(18.dp))
            Box(
                Modifier
                    .fillMaxWidth()
                    .height(1.dp)
                    .background(MaterialTheme.colorScheme.outlineVariant)
            )
            Spacer(Modifier.height(16.dp))

            Column(
                modifier = Modifier.fillMaxWidth(),
                verticalArrangement = Arrangement.spacedBy(14.dp),
            ) {
                InfoItem("Email", client.email)
                InfoItem("Phone", client.contactNo.orEmpty())
                InfoItem(
                    label = "Address",
                    value = listOfNotNull(
                        client.addressBarangay?.takeIf { it.isNotBlank() },
                        client.addressMunicipality?.takeIf { it.isNotBlank() },
                    ).joinToString(", "),
                )
                InfoItem("Member Since", Formatters.timestamp(client.createdAt))
            }
        }
    }
}
