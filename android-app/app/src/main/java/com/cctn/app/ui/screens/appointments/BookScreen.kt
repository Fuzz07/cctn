package com.cctn.app.ui.screens.appointments

import android.content.Context
import android.net.Uri
import android.provider.OpenableColumns
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
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
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.CreditCard
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.UploadFile
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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
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
    onPaymentMethods: () -> Unit = {},
    viewModel: BookViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    var showDatePicker by remember { mutableStateOf(false) }
    val context = LocalContext.current
    val receiptPicker = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent(),
    ) { uri ->
        if (uri != null) {
            val metadata = paymentProofMetadata(context, uri)
            viewModel.onPaymentProofSelected(
                uri = uri.toString(),
                name = metadata.name,
                mimeType = metadata.mimeType,
                size = metadata.size,
            )
        }
    }

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
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.SpaceBetween,
            ) {
                SectionTitle("4. Select Digital Payment Method *")
                TextButton(onClick = onPaymentMethods, enabled = !state.submitting) {
                    Text("Manage saved methods")
                }
            }

            val digitalOptions = listOf("GCash", "Maya", "Bank Transfer", "Credit/Debit Card")
            val availableMethods = if (state.paymentMethods.isNotEmpty()) {
                state.paymentMethods.map { it.providerName }.distinct() + digitalOptions.filter { opt ->
                    state.paymentMethods.none { it.providerName.equals(opt, ignoreCase = true) }
                }
            } else digitalOptions

            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                availableMethods.chunked(2).forEach { row ->
                    Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        row.forEach { method ->
                            val selected = state.selectedPaymentMethod.equals(method, ignoreCase = true)
                            Box(
                                modifier = Modifier
                                    .weight(1f)
                                    .height(46.dp)
                                    .border(
                                        width = if (selected) 2.dp else 1.dp,
                                        color = if (selected) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.outline.copy(alpha = 0.5f),
                                        shape = RoundedCornerShape(10.dp),
                                    )
                                    .background(
                                        color = if (selected) MaterialTheme.colorScheme.primaryContainer.copy(alpha = 0.35f) else MaterialTheme.colorScheme.surface,
                                        shape = RoundedCornerShape(10.dp),
                                    )
                                    .clickable(enabled = !state.submitting) {
                                        viewModel.selectPaymentMethod(method)
                                    },
                                contentAlignment = Alignment.Center,
                            ) {
                                Text(
                                    text = method,
                                    style = MaterialTheme.typography.bodyMedium,
                                    color = if (selected) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface,
                                )
                            }
                        }
                        repeat(2 - row.size) { Spacer(Modifier.weight(1f)) }
                    }
                }
            }

            PaymentInstructionsCard(state.selectedPaymentMethod)

            CctnTextField(
                value = state.referenceNumber,
                onValueChange = viewModel::onReferenceNumberChange,
                label = "Reference / Transaction Number *",
                placeholder = "e.g. 10029384756",
                supportingText = "Enter the reference ID from your payment confirmation screen.",
                error = state.referenceNumberError,
                enabled = !state.submitting,
            )

            PaymentProofPicker(
                fileName = state.paymentProofName,
                error = state.paymentProofError,
                enabled = !state.submitting,
                onChoose = { receiptPicker.launch("image/*") },
                onRemove = { viewModel.onPaymentProofSelected(null, null, null, null) },
            )

            PendingVerificationNotice()

            Spacer(Modifier.height(6.dp))
            SectionTitle("5. Anything we should know?")

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

private data class PaymentAccountInstructions(
    val badge: String,
    val heading: String,
    val accountName: String,
    val accountLabel: String,
    val accountNumber: String,
    val instructions: String,
    val accent: Color,
)

private fun paymentInstructionsFor(method: String): PaymentAccountInstructions {
    val normalized = method.lowercase()
    return when {
        "maya" in normalized -> PaymentAccountInstructions(
            badge = "Maya E-Wallet",
            heading = "MAYA E-WALLET OFFICIAL ACCOUNT",
            accountName = "Bogo Cable Television Inc. (BCTVI)",
            accountLabel = "Maya Number",
            accountNumber = "0917 888 2099",
            instructions = "Transfer the exact amount to the official Maya account above.",
            accent = Color(0xFF15803D),
        )

        listOf("bank", "bdo", "bpi", "unionbank").any { it in normalized } -> PaymentAccountInstructions(
            badge = "Bank Transfer",
            heading = "BCTVI OFFICIAL BANK ACCOUNT",
            accountName = "Bogo Cable Television Inc.",
            accountLabel = "Bank Account Number",
            accountNumber = "0012-3456-7890 (BDO) / 1234-5678-90 (BPI)",
            instructions = "Transfer the exact amount through your bank and keep the confirmation receipt.",
            accent = Color(0xFF6B21A8),
        )

        "card" in normalized -> PaymentAccountInstructions(
            badge = "Credit / Debit Card",
            heading = "VERIFIED CARD PAYMENT CHANNEL",
            accountName = "Bogo Cable Television Inc. (BCTVI)",
            accountLabel = "Payment Channel",
            accountNumber = "Online Card Portal Transfer",
            instructions = "Complete the payment through the verified card channel and save the receipt.",
            accent = Color(0xFF0369A1),
        )

        else -> PaymentAccountInstructions(
            badge = "GCash E-Wallet",
            heading = "GCASH E-WALLET OFFICIAL ACCOUNT",
            accountName = "Bogo Cable Television Inc. (BCTVI)",
            accountLabel = "GCash Number",
            accountNumber = "0917 888 2099",
            instructions = "Send the exact payment to the GCash account above. Enter your name or account number in the message field.",
            accent = Color(0xFF1D4ED8),
        )
    }
}

@Composable
private fun PaymentInstructionsCard(method: String) {
    val details = paymentInstructionsFor(method)

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.45f), RoundedCornerShape(14.dp))
            .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(14.dp))
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    imageVector = Icons.Filled.Info,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.size(18.dp),
                )
                Spacer(Modifier.size(7.dp))
                Text(
                    text = "PAYMENT ACCOUNT INSTRUCTIONS",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.primary,
                )
            }
            Text(
                text = details.badge,
                style = MaterialTheme.typography.labelSmall,
                color = details.accent,
                modifier = Modifier
                    .background(details.accent.copy(alpha = 0.12f), RoundedCornerShape(50))
                    .padding(horizontal = 9.dp, vertical = 5.dp),
            )
        }

        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(MaterialTheme.colorScheme.surface, RoundedCornerShape(10.dp))
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(10.dp))
                .padding(14.dp),
            verticalArrangement = Arrangement.spacedBy(5.dp),
        ) {
            Text(details.heading, style = MaterialTheme.typography.labelMedium, color = details.accent)
            Text("Account Name", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Text(details.accountName, style = MaterialTheme.typography.titleSmall, color = MaterialTheme.colorScheme.onSurface)
            Spacer(Modifier.height(2.dp))
            Text(details.accountLabel, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Text(details.accountNumber, style = MaterialTheme.typography.titleMedium, color = details.accent)
            Spacer(Modifier.height(3.dp))
            Text(
                text = "Instructions: ${details.instructions}",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }
    }
}

@Composable
private fun PaymentProofPicker(
    fileName: String?,
    error: String?,
    enabled: Boolean,
    onChoose: () -> Unit,
    onRemove: () -> Unit,
) {
    Column(Modifier.fillMaxWidth()) {
        Text(
            text = "Upload Payment Receipt or Screenshot *",
            style = MaterialTheme.typography.labelMedium,
            color = MaterialTheme.colorScheme.onSurface,
            modifier = Modifier.padding(bottom = 6.dp),
        )
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(
                    1.dp,
                    if (error != null) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.outline,
                    RoundedCornerShape(8.dp),
                )
                .background(MaterialTheme.colorScheme.background, RoundedCornerShape(8.dp))
                .clickable(enabled = enabled, onClick = onChoose)
                .padding(horizontal = 14.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Icon(
                imageVector = if (fileName == null) Icons.Filled.UploadFile else Icons.Filled.CheckCircle,
                contentDescription = null,
                tint = if (fileName == null) MaterialTheme.colorScheme.primary else Color(0xFF15803D),
            )
            Spacer(Modifier.size(10.dp))
            Text(
                text = fileName ?: "Choose receipt image",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurface,
                modifier = Modifier.weight(1f),
            )
            if (fileName != null) {
                TextButton(onClick = onRemove, enabled = enabled) { Text("Remove") }
            }
        }
        Text(
            text = error ?: "Upload a clear JPG, PNG, or WebP image up to 4 MB.",
            style = MaterialTheme.typography.bodySmall,
            color = if (error != null) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.onSurfaceVariant,
            modifier = Modifier.padding(top = 5.dp, start = 3.dp),
        )
    }
}

@Composable
private fun PendingVerificationNotice() {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(Color(0xFFFFF8E1), RoundedCornerShape(10.dp))
            .border(1.dp, Color(0xFFF6C453), RoundedCornerShape(10.dp))
            .padding(14.dp),
        verticalAlignment = Alignment.Top,
    ) {
        Icon(
            imageVector = Icons.Filled.CreditCard,
            contentDescription = null,
            tint = Color(0xFFB45309),
            modifier = Modifier.size(20.dp),
        )
        Spacer(Modifier.size(9.dp))
        Text(
            text = "After booking, your payment status will remain Pending Verification until an administrator verifies the transaction reference and receipt.",
            style = MaterialTheme.typography.bodySmall,
            color = Color(0xFF92400E),
        )
    }
}

private data class PaymentProofMetadata(
    val name: String,
    val mimeType: String?,
    val size: Long?,
)

private fun paymentProofMetadata(context: Context, uri: Uri): PaymentProofMetadata {
    var name = "payment-receipt.jpg"
    var size: Long? = null

    context.contentResolver.query(
        uri,
        arrayOf(OpenableColumns.DISPLAY_NAME, OpenableColumns.SIZE),
        null,
        null,
        null,
    )?.use { cursor ->
        if (cursor.moveToFirst()) {
            val nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME)
            val sizeIndex = cursor.getColumnIndex(OpenableColumns.SIZE)
            if (nameIndex >= 0 && !cursor.isNull(nameIndex)) name = cursor.getString(nameIndex)
            if (sizeIndex >= 0 && !cursor.isNull(sizeIndex)) size = cursor.getLong(sizeIndex)
        }
    }

    return PaymentProofMetadata(
        name = name,
        mimeType = context.contentResolver.getType(uri),
        size = size,
    )
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
