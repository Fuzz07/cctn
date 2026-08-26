package com.cctn.app.ui.screens.profile

import androidx.compose.foundation.background
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
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
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
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import coil.compose.AsyncImage
import com.cctn.app.core.Formatters
import com.cctn.app.core.ServiceArea
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.ui.components.BirthdateAndAge
import com.cctn.app.ui.components.CctnDropdownField
import com.cctn.app.ui.components.CctnPasswordField
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.DetailRow
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard

@Composable
fun ProfileScreen(viewModel: ProfileViewModel = hiltViewModel()) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val client by viewModel.client.collectAsStateWithLifecycle()
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
                title = "My account",
                refreshing = state.refreshing,
                onRefresh = viewModel::refresh,
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
    ) { padding ->
        val current = client
        if (current == null) {
            LoadingState(Modifier.padding(padding))
            return@Scaffold
        }

        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .imePadding(),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            item { ProfileHeader(current) }

            if (state.editing) {
                item { EditForm(state, viewModel) }
            } else {
                item { DetailsCard(current, onEdit = viewModel::startEditing) }
            }

            item {
                OutlinedButton(
                    onClick = viewModel::askToSignOut,
                    enabled = !state.signingOut && !state.saving,
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(52.dp),
                    shape = RoundedCornerShape(12.dp),
                ) {
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.Logout,
                        contentDescription = null,
                        modifier = Modifier.size(18.dp),
                    )
                    Spacer(Modifier.size(8.dp))
                    Text("Sign out")
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

    if (state.signOutDialogOpen) {
        AlertDialog(
            onDismissRequest = viewModel::dismissSignOut,
            title = { Text("Sign out?") },
            text = { Text("You will need your username and password to sign back in.") },
            confirmButton = {
                TextButton(onClick = viewModel::confirmSignOut) {
                    Text("Sign out", color = MaterialTheme.colorScheme.error)
                }
            },
            dismissButton = {
                TextButton(onClick = viewModel::dismissSignOut) { Text("Stay signed in") }
            },
        )
    }
}

@Composable
private fun ProfileHeader(client: ClientDto) {
    Column(
        modifier = Modifier.fillMaxWidth(),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Box(
            modifier = Modifier
                .size(88.dp)
                .clip(CircleShape)
                .background(MaterialTheme.colorScheme.primaryContainer),
            contentAlignment = Alignment.Center,
        ) {
            if (!client.profilePhoto.isNullOrBlank()) {
                AsyncImage(
                    model = client.profilePhoto,
                    contentDescription = null,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier.fillMaxSize(),
                )
            } else {
                Text(
                    text = Formatters.initials(client.firstname, client.lastname),
                    style = MaterialTheme.typography.headlineMedium,
                    color = MaterialTheme.colorScheme.onPrimaryContainer,
                )
            }
        }

        Spacer(Modifier.height(12.dp))

        Text(
            text = client.fullName.ifBlank { client.username },
            style = MaterialTheme.typography.titleLarge,
            color = MaterialTheme.colorScheme.onBackground,
        )

        if (!client.accountNumber.isNullOrBlank()) {
            Spacer(Modifier.height(2.dp))
            Text(
                text = "Account " + client.accountNumber.orEmpty(),
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }
    }
}

@Composable
private fun DetailsCard(client: ClientDto, onEdit: () -> Unit) {
    SectionCard {
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Text(
                    text = "Your details",
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                TextButton(onClick = onEdit) {
                    Icon(
                        imageVector = Icons.Filled.Edit,
                        contentDescription = null,
                        modifier = Modifier.size(16.dp),
                    )
                    Spacer(Modifier.size(6.dp))
                    Text("Edit")
                }
            }

            Spacer(Modifier.height(4.dp))

            DetailRow("Username", client.username)
            DetailRow("Email", client.email)
            DetailRow("Mobile", client.contactNo.orEmpty().ifBlank { "—" })
            DetailRow(
                "Address",
                listOfNotNull(
                    client.addressBarangay?.takeIf { it.isNotBlank() },
                    client.addressMunicipality?.takeIf { it.isNotBlank() },
                    client.addressProvince?.takeIf { it.isNotBlank() },
                ).joinToString(", ").ifBlank { "—" },
            )
            DetailRow("Gender", Formatters.titleCase(client.gender))
            DetailRow("Civil status", Formatters.titleCase(client.civilStatus))
            DetailRow("Birthdate", Formatters.date(client.birthdate))
            DetailRow("Customer since", Formatters.timestamp(client.createdAt))
        }
    }
}

@Composable
private fun EditForm(state: ProfileUiState, viewModel: ProfileViewModel) {
    SectionCard {
        Column(Modifier.padding(16.dp)) {
            Text(
                text = "Edit your details",
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.onSurface,
            )

            Spacer(Modifier.height(10.dp))

            CctnTextField(
                value = state.firstname,
                onValueChange = viewModel::onFirstname,
                label = "First name",
                error = state.error("firstname"),
                enabled = !state.saving,
            )
            CctnTextField(
                value = state.middlename,
                onValueChange = viewModel::onMiddlename,
                label = "Middle name (optional)",
                error = state.error("middlename"),
                enabled = !state.saving,
            )
            CctnTextField(
                value = state.lastname,
                onValueChange = viewModel::onLastname,
                label = "Last name",
                error = state.error("lastname"),
                enabled = !state.saving,
            )
            CctnTextField(
                value = state.username,
                onValueChange = viewModel::onUsername,
                label = "Username",
                error = state.error("username"),
                enabled = !state.saving,
            )
            CctnTextField(
                value = state.email,
                onValueChange = viewModel::onEmail,
                label = "Email",
                error = state.error("email"),
                enabled = !state.saving,
                keyboardType = KeyboardType.Email,
            )
            CctnTextField(
                value = state.contactNo,
                onValueChange = viewModel::onContactNo,
                label = "Mobile number",
                error = state.error("contact_no"),
                enabled = !state.saving,
                keyboardType = KeyboardType.Number,
                supportingText = "11 digits starting with 09.",
            )

            BirthdateAndAge(
                birthdate = state.birthdate,
                age = state.age,
                onBirthdate = viewModel::onBirthdate,
                error = state.error("birthdate"),
                enabled = !state.saving,
            )

            Spacer(Modifier.height(8.dp))

            CctnDropdownField(
                value = state.municipality,
                options = ServiceArea.MUNICIPALITIES,
                onOptionSelected = viewModel::onMunicipality,
                label = "Municipality",
                error = state.error("address_municipality"),
                enabled = !state.saving,
            )
            Spacer(Modifier.height(8.dp))
            CctnDropdownField(
                value = state.barangay,
                options = state.barangayOptions,
                onOptionSelected = viewModel::onBarangay,
                label = "Barangay",
                error = state.error("address_barangay"),
                enabled = !state.saving,
            )

            Spacer(Modifier.height(16.dp))

            Text(
                text = "Change password",
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.onSurface,
            )
            Spacer(Modifier.height(4.dp))
            Text(
                text = "Leave both fields blank to keep your current password.",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
            Spacer(Modifier.height(10.dp))

            CctnPasswordField(
                value = state.newPassword,
                onValueChange = viewModel::onNewPassword,
                label = "New password",
                error = state.error("new_password"),
                enabled = !state.saving,
            )
            CctnPasswordField(
                value = state.confirmPassword,
                onValueChange = viewModel::onConfirmPassword,
                label = "Confirm new password",
                error = state.error("confirm_password"),
                enabled = !state.saving,
                imeAction = ImeAction.Done,
            )

            Spacer(Modifier.height(12.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                OutlinedButton(
                    onClick = viewModel::cancelEditing,
                    enabled = !state.saving,
                    modifier = Modifier
                        .weight(1f)
                        .height(52.dp),
                    shape = RoundedCornerShape(12.dp),
                ) {
                    Text("Cancel")
                }
                LoadingButton(
                    text = "Save changes",
                    onClick = viewModel::save,
                    modifier = Modifier.weight(1.4f),
                    loading = state.saving,
                )
            }
        }
    }
}
