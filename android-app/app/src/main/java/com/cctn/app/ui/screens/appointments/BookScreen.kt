package com.cctn.app.ui.screens.appointments

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.ServiceDto
import com.cctn.app.data.remote.dto.SlotDto
import com.cctn.app.ui.components.CctnDatePickerDialog
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard
import java.time.LocalDate

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun BookScreen(
    onDone: () -> Unit,
    onBack: () -> Unit,
    viewModel: BookViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    var showDatePicker by remember { mutableStateOf(false) }

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        // The app-level Column already clears the status bar and the
        // bottom bar handles its own inset, so this Scaffold must not
        // add either a second time.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            TopAppBar(
                title = { Text("Book a service") },
                navigationIcon = {
                    IconButton(onClick = onBack, enabled = !state.submitting) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                ),
            )
        },
    ) { padding ->
        if (state.loadingServices) {
            LoadingState(Modifier.padding(padding))
            return@Scaffold
        }

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .verticalScroll(rememberScrollState())
                .imePadding()
                .navigationBarsPadding()
                .padding(horizontal = 16.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            SectionTitle("1. Choose a service")

            if (state.servicesError != null) {
                RetryRow(message = state.servicesError.orEmpty(), onRetry = viewModel::loadServices)
            } else if (state.services.isEmpty()) {
                Text(
                    text = "No services are available to book right now. Please try again later.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            } else {
                state.services.forEach { service ->
                    ServiceOption(
                        service = service,
                        selected = service.id == state.selectedServiceId,
                        onClick = { viewModel.selectService(service.id) },
                    )
                }
            }

            Spacer(Modifier.height(6.dp))
            SectionTitle("2. Pick a date")

            SectionCard(
                modifier = Modifier.clickable(enabled = !state.submitting) {
                    showDatePicker = true
                }
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(16.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Icon(
                        imageVector = Icons.Filled.CalendarMonth,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.primary,
                    )
                    Spacer(Modifier.size(12.dp))
                    Column {
                        Text(
                            text = Formatters.date(state.apiDate),
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.onSurface,
                        )
                        Text(
                            text = "Tap to change",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                }
            }

            Spacer(Modifier.height(6.dp))
            SectionTitle("3. Pick a time")

            when {
                state.loadingSlots -> Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(80.dp),
                    contentAlignment = Alignment.Center,
                ) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(24.dp),
                        strokeWidth = 2.dp,
                    )
                }

                state.slotsError != null -> RetryRow(
                    message = state.slotsError.orEmpty(),
                    onRetry = { viewModel.loadSlots() },
                )

                state.slots.isEmpty() -> Text(
                    text = "No time slots are set up for this date. Try another day.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )

                else -> SlotGrid(
                    slots = state.slots,
                    selected = state.selectedTime,
                    onSelect = viewModel::selectTime,
                )
            }

            Spacer(Modifier.height(6.dp))
            SectionTitle("4. Anything we should know?")

            CctnTextField(
                value = state.message,
                onValueChange = viewModel::onMessageChange,
                label = "Notes (optional)",
                placeholder = "Landmarks, preferred contact time, and so on",
                enabled = !state.submitting,
                singleLine = false,
                minLines = 3,
                imeAction = ImeAction.Done,
                supportingText = "${state.message.length}/500",
                error = if (state.message.length > 500) {
                    "Notes must be 500 characters or fewer."
                } else {
                    null
                },
            )

            if (state.submitError != null) {
                Text(
                    text = state.submitError.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error,
                )
            }

            Spacer(Modifier.height(4.dp))

            LoadingButton(
                text = "Confirm booking",
                onClick = viewModel::submit,
                modifier = Modifier.fillMaxWidth(),
                loading = state.submitting,
                enabled = state.canSubmit && state.message.length <= 500,
            )

            Spacer(Modifier.height(28.dp))
        }
    }

    if (showDatePicker) {
        CctnDatePickerDialog(
            initial = state.date,
            onDismiss = { showDatePicker = false },
            onSelected = {
                showDatePicker = false
                viewModel.selectDate(it)
            },
            // The server refuses a date before today.
            earliest = LocalDate.now(),
            latest = LocalDate.now().plusMonths(6),
        )
    }

    state.result?.let { result ->
        AlertDialog(
            onDismissRequest = {},
            title = { Text(if (result.rescheduled) "Slot taken — rescheduled" else "Booking sent") },
            text = { Text(result.message) },
            confirmButton = {
                TextButton(
                    onClick = {
                        viewModel.resultShown()
                        onDone()
                    }
                ) {
                    Text("Done")
                }
            },
        )
    }
}

@Composable
private fun SectionTitle(text: String) {
    Text(
        text = text,
        style = MaterialTheme.typography.titleMedium,
        color = MaterialTheme.colorScheme.onBackground,
        modifier = Modifier.padding(top = 8.dp),
    )
}

@Composable
private fun RetryRow(message: String, onRetry: () -> Unit) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.SpaceBetween,
    ) {
        Text(
            text = message,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.error,
            modifier = Modifier.weight(1f),
        )
        TextButton(onClick = onRetry) { Text("Retry") }
    }
}

@Composable
private fun ServiceOption(
    service: ServiceDto,
    selected: Boolean,
    onClick: () -> Unit,
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .border(
                width = if (selected) 2.dp else 1.dp,
                color = if (selected) {
                    MaterialTheme.colorScheme.primary
                } else {
                    MaterialTheme.colorScheme.outline.copy(alpha = 0.6f)
                },
                shape = RoundedCornerShape(14.dp),
            )
            .background(
                color = if (selected) {
                    MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.35f)
                } else {
                    MaterialTheme.colorScheme.surface
                },
                shape = RoundedCornerShape(14.dp),
            )
            .clickable(onClick = onClick)
            .padding(16.dp),
    ) {
        Column {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
            ) {
                Text(
                    text = service.serviceName,
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier.weight(1f),
                )
                Text(
                    text = Formatters.peso(service.price),
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.primary,
                )
            }
            if (!service.description.isNullOrBlank()) {
                Spacer(Modifier.height(4.dp))
                Text(
                    text = service.description.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }
        }
    }
}

@Composable
private fun SlotGrid(
    slots: List<SlotDto>,
    selected: String?,
    onSelect: (String) -> Unit,
) {
    // A plain wrapping layout rather than a nested lazy grid: the whole screen
    // already scrolls, and nesting scroll containers is a runtime crash.
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        slots.chunked(3).forEach { row ->
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                row.forEach { slot ->
                    SlotChip(
                        slot = slot,
                        selected = slot.time == selected,
                        onClick = { onSelect(slot.time) },
                        modifier = Modifier.weight(1f),
                    )
                }
                // Keeps the last row's chips the same width as the rows above.
                repeat(3 - row.size) { Spacer(Modifier.weight(1f)) }
            }
        }
    }
}

@Composable
private fun SlotChip(
    slot: SlotDto,
    selected: Boolean,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
) {
    val enabled = slot.available

    val background = when {
        selected -> MaterialTheme.colorScheme.primary
        !enabled -> MaterialTheme.colorScheme.surfaceVariant
        else -> MaterialTheme.colorScheme.surface
    }
    val contentColor = when {
        selected -> MaterialTheme.colorScheme.onPrimary
        !enabled -> MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.5f)
        else -> MaterialTheme.colorScheme.onSurface
    }

    Box(
        modifier = modifier
            .height(46.dp)
            .border(
                width = 1.dp,
                color = if (selected) {
                    MaterialTheme.colorScheme.primary
                } else {
                    MaterialTheme.colorScheme.outline.copy(alpha = 0.5f)
                },
                shape = RoundedCornerShape(10.dp),
            )
            .background(background, RoundedCornerShape(10.dp))
            .clickable(enabled = enabled, onClick = onClick),
        contentAlignment = Alignment.Center,
    ) {
        Text(
            text = slot.label,
            style = MaterialTheme.typography.bodyMedium,
            color = contentColor,
        )
    }
}
