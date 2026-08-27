package com.cctn.app.ui.screens.appointments

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
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material.icons.outlined.EventBusy
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExtendedFloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.PageHeading
import com.cctn.app.ui.components.EmptyState
import com.cctn.app.ui.components.ErrorState
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.components.StatusChip

@Composable
fun AppointmentsScreen(
    onBook: () -> Unit,
    onOpenAssistant: () -> Unit,
    viewModel: AppointmentsViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val snackbarHostState = remember { SnackbarHostState() }

    LaunchedEffect(state.message) {
        state.message?.let {
            snackbarHostState.showSnackbar(it)
            viewModel.messageShown()
        }
    }

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        // The app-level Column already clears the status bar and the
        // bottom bar handles its own inset, so this Scaffold must not
        // add either a second time.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            CctnTopBar(
                refreshing = state.refreshing,
                onRefresh = viewModel::refresh,
                onOpenAssistant = onOpenAssistant,
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
        floatingActionButton = {
            if (state.appointments.isNotEmpty()) {
                ExtendedFloatingActionButton(
                    onClick = onBook,
                    containerColor = MaterialTheme.colorScheme.primary,
                    contentColor = MaterialTheme.colorScheme.onPrimary,
                    icon = { Icon(Icons.Filled.Add, contentDescription = null) },
                    text = { Text("Book") },
                )
            }
        },
    ) { padding ->
        val content = Modifier
            .fillMaxSize()
            .padding(padding)

        when {
            state.loading -> LoadingState(content)

            state.appointments.isEmpty() && state.error != null ->
                ErrorState(
                    message = state.error.orEmpty(),
                    onRetry = viewModel::refresh,
                    modifier = content,
                )

            state.appointments.isEmpty() -> EmptyState(
                icon = Icons.Outlined.EventBusy,
                title = "No bookings yet",
                body = "Request an installation or a service visit and track its progress here.",
                actionLabel = "Book a service",
                onAction = onBook,
                modifier = content,
            )

            else -> LazyColumn(
                modifier = content,
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 92.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                item {
                    PageHeading(
                        title = "My bookings",
                        subtitle = "Track every installation and service visit you have requested.",
                    )
                }

                items(state.appointments, key = { it.id }) { appointment ->
                    AppointmentCard(
                        appointment = appointment,
                        cancelling = state.cancellingId == appointment.id,
                        onCancel = { viewModel.askToCancel(appointment) },
                    )
                }
            }
        }
    }

    state.pendingCancel?.let { target ->
        AlertDialog(
            onDismissRequest = viewModel::dismissCancel,
            title = { Text("Cancel this booking?") },
            text = {
                Text(
                    "Your " + (target.service?.name ?: "service visit") + " on " +
                        Formatters.date(target.preferredDate) + " will be cancelled. " +
                        "You can book again at any time."
                )
            },
            confirmButton = {
                TextButton(onClick = viewModel::confirmCancel) {
                    Text("Cancel booking", color = MaterialTheme.colorScheme.error)
                }
            },
            dismissButton = {
                TextButton(onClick = viewModel::dismissCancel) { Text("Keep it") }
            },
        )
    }
}

@Composable
private fun AppointmentCard(
    appointment: AppointmentDto,
    cancelling: Boolean,
    onCancel: () -> Unit,
) {
    SectionCard {
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top,
            ) {
                Column(Modifier.weight(1f)) {
                    Text(
                        text = appointment.service?.name ?: "Service visit",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                    )
                    appointment.service?.price?.takeIf { it > 0 }?.let { price ->
                        Spacer(Modifier.height(2.dp))
                        Text(
                            text = Formatters.peso(price),
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                }
                StatusChip(appointment.status)
            }

            Spacer(Modifier.height(12.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    imageVector = Icons.Filled.CalendarMonth,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(15.dp),
                )
                Spacer(Modifier.size(6.dp))
                Text(
                    text = Formatters.date(appointment.preferredDate),
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                Spacer(Modifier.size(16.dp))
                Icon(
                    imageVector = Icons.Filled.Schedule,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(15.dp),
                )
                Spacer(Modifier.size(6.dp))
                Text(
                    text = Formatters.time(appointment.preferredTime),
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }

            if (!appointment.message.isNullOrBlank()) {
                Spacer(Modifier.height(10.dp))
                Text(
                    text = appointment.message.orEmpty(),
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
            }

            if (!appointment.adminNotes.isNullOrBlank()) {
                Spacer(Modifier.height(10.dp))
                Text(
                    text = "Note from BCTVI: " + appointment.adminNotes.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }

            // Only a booking the server would actually let go can be cancelled;
            // an approved visit has to be changed by the office.
            if (appointment.isCancellable) {
                Spacer(Modifier.height(6.dp))
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.End,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    if (cancelling) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(18.dp),
                            strokeWidth = 2.dp,
                            color = MaterialTheme.colorScheme.error,
                        )
                    } else {
                        TextButton(onClick = onCancel) {
                            Text("Cancel booking", color = MaterialTheme.colorScheme.error)
                        }
                    }
                }
            }
        }
    }
}

/**
 * Mirrors the server rule: an approved appointment is refused, and a cancelled
 * or completed one has nothing left to cancel.
 */
private val AppointmentDto.isCancellable: Boolean
    get() = status.lowercase() !in setOf("approved", "cancelled", "completed", "rejected")
