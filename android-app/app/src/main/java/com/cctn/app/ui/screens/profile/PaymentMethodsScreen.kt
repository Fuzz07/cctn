package com.cctn.app.ui.screens.profile

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
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
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.CreditCard
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.PhoneAndroid
import androidx.compose.material.icons.filled.AccountBalance
import androidx.compose.material.icons.outlined.CreditCard
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.ExtendedFloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Switch
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.data.remote.dto.PaymentMethodDto
import com.cctn.app.ui.components.CctnDropdownField
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.EmptyState
import com.cctn.app.ui.components.ErrorState
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.PageHeading
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.theme.BrandRed

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PaymentMethodsScreen(
    onBack: () -> Unit,
    viewModel: PaymentMethodsViewModel = hiltViewModel(),
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
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            TopAppBar(
                title = { Text("Payment Methods") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                ),
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
        floatingActionButton = {
            ExtendedFloatingActionButton(
                onClick = viewModel::openAddDialog,
                containerColor = MaterialTheme.colorScheme.primary,
                contentColor = MaterialTheme.colorScheme.onPrimary,
                icon = { Icon(Icons.Filled.Add, contentDescription = null) },
                text = { Text("Add Method") },
            )
        },
    ) { padding ->
        val content = Modifier
            .fillMaxSize()
            .padding(padding)

        when {
            state.loading -> LoadingState(content)

            state.paymentMethods.isEmpty() && state.error != null ->
                ErrorState(
                    message = state.error.orEmpty(),
                    onRetry = viewModel::refresh,
                    modifier = content,
                )

            state.paymentMethods.isEmpty() -> EmptyState(
                icon = Icons.Outlined.CreditCard,
                title = "No payment methods added",
                body = "Add your GCash, Maya, or bank account for digital payments and faster checkout.",
                actionLabel = "Add payment method",
                onAction = viewModel::openAddDialog,
                modifier = content,
            )

            else -> LazyColumn(
                modifier = content,
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 92.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                item {
                    PageHeading(
                        title = "Payment Methods",
                        subtitle = "Manage your digital payment methods for broadband bookings and bill payments.",
                    )
                }

                items(state.paymentMethods, key = { it.id }) { method ->
                    PaymentMethodCard(
                        method = method,
                        deleting = state.deletingId == method.id,
                        onSetDefault = { viewModel.setDefault(method.id) },
                        onDelete = { viewModel.askToDelete(method) },
                    )
                }
            }
        }
    }

    if (state.showAddDialog) {
        AddPaymentMethodDialog(
            saving = state.saving,
            error = state.addError,
            onDismiss = viewModel::dismissAddDialog,
            onAdd = viewModel::addPaymentMethod,
        )
    }

    state.pendingDelete?.let { target ->
        AlertDialog(
            onDismissRequest = viewModel::dismissDelete,
            title = { Text("Remove Payment Method?") },
            text = {
                Text("Remove ${target.providerName} (${target.maskedAccountNumber}) from your account?")
            },
            confirmButton = {
                TextButton(onClick = viewModel::confirmDelete) {
                    Text("Remove", color = MaterialTheme.colorScheme.error)
                }
            },
            dismissButton = {
                TextButton(onClick = viewModel::dismissDelete) { Text("Keep") }
            },
        )
    }
}

@Composable
private fun PaymentMethodCard(
    method: PaymentMethodDto,
    deleting: Boolean,
    onSetDefault: () -> Unit,
    onDelete: () -> Unit,
) {
    val isDefault = method.isDefault
    val borderModifier = if (isDefault) {
        Modifier.border(1.5.dp, BrandRed, RoundedCornerShape(16.dp))
    } else Modifier

    SectionCard(modifier = borderModifier) {
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        modifier = Modifier
                            .size(42.dp)
                            .clip(RoundedCornerShape(10.dp))
                            .background(
                                when {
                                    method.providerName.contains("gcash", ignoreCase = true) -> Color(0xFF007DFE)
                                    method.providerName.contains("maya", ignoreCase = true) -> Color(0xFF00D064)
                                    method.paymentType == "bank_transfer" -> Color(0xFF0A2540)
                                    else -> BrandRed
                                }
                            ),
                        contentAlignment = Alignment.Center,
                    ) {
                        Icon(
                            imageVector = when (method.paymentType.lowercase()) {
                                "gcash", "maya" -> Icons.Filled.PhoneAndroid
                                "bank_transfer" -> Icons.Filled.AccountBalance
                                else -> Icons.Filled.CreditCard
                            },
                            contentDescription = null,
                            tint = Color.White,
                            modifier = Modifier.size(22.dp),
                        )
                    }

                    Spacer(Modifier.size(12.dp))

                    Column {
                        Text(
                            text = method.providerName.ifBlank { method.formattedType },
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.onSurface,
                        )
                        Text(
                            text = method.formattedType,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                }

                if (isDefault) {
                    Box(
                        modifier = Modifier
                            .clip(RoundedCornerShape(99.dp))
                            .background(BrandRed.copy(alpha = 0.12f))
                            .padding(horizontal = 8.dp, vertical = 4.dp),
                    ) {
                        Text(
                            text = "DEFAULT",
                            style = MaterialTheme.typography.labelSmall,
                            color = BrandRed,
                        )
                    }
                }
            }

            Spacer(Modifier.height(14.dp))

            Text(
                text = method.maskedAccountNumber,
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.onSurface,
            )
            Text(
                text = method.accountName,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )

            if (!method.notes.isNullOrBlank()) {
                Spacer(Modifier.height(4.dp))
                Text(
                    text = method.notes.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.8f),
                )
            }

            Spacer(Modifier.height(10.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                if (!isDefault) {
                    TextButton(onClick = onSetDefault) {
                        Text("Set as default", color = MaterialTheme.colorScheme.primary)
                    }
                } else {
                    Row(verticalAlignment = Alignment.CenterVertically) {
                        Icon(
                            imageVector = Icons.Filled.Check,
                            contentDescription = null,
                            tint = Color(0xFF16A34A),
                            modifier = Modifier.size(16.dp),
                        )
                        Spacer(Modifier.size(4.dp))
                        Text(
                            text = "Preferred method",
                            style = MaterialTheme.typography.bodySmall,
                            color = Color(0xFF16A34A),
                        )
                    }
                }

                if (deleting) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(18.dp),
                        strokeWidth = 2.dp,
                        color = MaterialTheme.colorScheme.error,
                    )
                } else {
                    IconButton(onClick = onDelete) {
                        Icon(
                            imageVector = Icons.Filled.Delete,
                            contentDescription = "Delete",
                            tint = MaterialTheme.colorScheme.error,
                            modifier = Modifier.size(20.dp),
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun AddPaymentMethodDialog(
    saving: Boolean,
    error: String?,
    onDismiss: () -> Unit,
    onAdd: (paymentType: String, providerName: String, accountName: String, accountNumber: String, isDefault: Boolean, notes: String?) -> Unit,
) {
    val typeOptions = listOf("GCash", "Maya", "Bank Transfer", "Credit Card", "Debit Card")
    var selectedTypeOption by remember { mutableStateOf("GCash") }
    var providerName by remember { mutableStateOf("GCash") }
    var accountName by remember { mutableStateOf("") }
    var accountNumber by remember { mutableStateOf("") }
    var notes by remember { mutableStateOf("") }
    var isDefault by remember { mutableStateOf(true) }

    val rawType = when (selectedTypeOption) {
        "GCash" -> "gcash"
        "Maya" -> "maya"
        "Bank Transfer" -> "bank_transfer"
        "Credit Card" -> "credit_card"
        "Debit Card" -> "debit_card"
        else -> "gcash"
    }

    AlertDialog(
        onDismissRequest = { if (!saving) onDismiss() },
        title = { Text("Add Payment Method") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                CctnDropdownField(
                    value = selectedTypeOption,
                    options = typeOptions,
                    onOptionSelected = {
                        selectedTypeOption = it
                        if (it == "GCash") providerName = "GCash"
                        if (it == "Maya") providerName = "Maya"
                        if (it == "Bank Transfer" && (providerName == "GCash" || providerName == "Maya")) {
                            providerName = "BDO Unibank"
                        }
                    },
                    label = "Category",
                    enabled = !saving,
                )

                CctnTextField(
                    value = providerName,
                    onValueChange = { providerName = it },
                    label = "Provider / Bank",
                    placeholder = "e.g. GCash, Maya, BDO, BPI",
                    enabled = !saving,
                )

                CctnTextField(
                    value = accountName,
                    onValueChange = { accountName = it },
                    label = "Account Name",
                    placeholder = "Full Name on account",
                    enabled = !saving,
                )

                CctnTextField(
                    value = accountNumber,
                    onValueChange = { accountNumber = it },
                    label = if (rawType in listOf("gcash", "maya")) "Mobile Number" else "Account / Card Number",
                    placeholder = if (rawType in listOf("gcash", "maya")) "0917XXXXXXX" else "Account Number",
                    enabled = !saving,
                )

                CctnTextField(
                    value = notes,
                    onValueChange = { notes = it },
                    label = "Notes (optional)",
                    placeholder = "e.g. Personal e-wallet",
                    enabled = !saving,
                )

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    Text("Set as default", style = MaterialTheme.typography.bodyMedium)
                    Switch(
                        checked = isDefault,
                        onCheckedChange = { isDefault = it },
                        enabled = !saving,
                    )
                }

                if (error != null) {
                    Text(
                        text = error,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error,
                    )
                }
            }
        },
        confirmButton = {
            LoadingButton(
                text = "Save",
                onClick = {
                    if (providerName.isNotBlank() && accountName.isNotBlank() && accountNumber.isNotBlank()) {
                        onAdd(rawType, providerName, accountName, accountNumber, isDefault, notes)
                    }
                },
                loading = saving,
                enabled = providerName.isNotBlank() && accountName.isNotBlank() && accountNumber.isNotBlank(),
            )
        },
        dismissButton = {
            TextButton(onClick = onDismiss, enabled = !saving) {
                Text("Cancel")
            }
        },
    )
}
