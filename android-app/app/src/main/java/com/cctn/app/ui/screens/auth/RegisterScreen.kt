package com.cctn.app.ui.screens.auth

import androidx.compose.foundation.background
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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.ServiceArea
import com.cctn.app.ui.components.BirthdateAndAge
import com.cctn.app.ui.components.CctnDropdownField
import com.cctn.app.ui.components.CctnPasswordField
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.LoadingButton

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RegisterScreen(
    onBack: () -> Unit,
    viewModel: RegisterViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        // The app-level Column already clears the status bar and the
        // bottom bar handles its own inset, so this Scaffold must not
        // add either a second time.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            TopAppBar(
                title = { Text("Create your account") },
                navigationIcon = {
                    IconButton(
                        onClick = { if (state.step > 1) viewModel.back() else onBack() },
                        enabled = !state.submitting,
                    ) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                ),
            )
        },
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .verticalScroll(rememberScrollState())
                .imePadding()
                .navigationBarsPadding()
                .padding(horizontal = 24.dp),
        ) {
            StepIndicator(step = state.step, total = 3)

            Spacer(Modifier.height(8.dp))

            Text(
                text = when (state.step) {
                    1 -> "Step 1 of 3 — Tell us a little about yourself."
                    2 -> "Step 2 of 3 — Where should we install your connection?"
                    else -> "Step 3 of 3 — Set up your login."
                },
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )

            Spacer(Modifier.height(20.dp))

            when (state.step) {
                1 -> AboutYouStep(state, viewModel)
                2 -> AddressStep(state, viewModel)
                else -> AccountStep(state, viewModel)
            }

            if (state.formError != null) {
                Text(
                    text = state.formError.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error,
                    modifier = Modifier.padding(vertical = 8.dp),
                )
            }

            Spacer(Modifier.height(16.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                if (state.step > 1) {
                    OutlinedButton(
                        onClick = viewModel::back,
                        enabled = !state.submitting,
                        modifier = Modifier
                            .weight(1f)
                            .height(52.dp),
                        shape = RoundedCornerShape(12.dp),
                    ) {
                        Text("Back")
                    }
                }

                LoadingButton(
                    text = if (state.isLastStep) "Create account" else "Continue",
                    onClick = { if (state.isLastStep) viewModel.submit() else viewModel.next() },
                    modifier = Modifier.weight(if (state.step > 1) 1.4f else 1f),
                    loading = state.submitting,
                )
            }

            Spacer(Modifier.height(40.dp))
        }
    }
}

@Composable
private fun StepIndicator(step: Int, total: Int) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(6.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        repeat(total) { index ->
            val done = index < step
            Box(
                modifier = Modifier
                    .weight(1f)
                    .height(5.dp)
                    .background(
                        color = if (done) {
                            MaterialTheme.colorScheme.primary
                        } else {
                            MaterialTheme.colorScheme.outline.copy(alpha = 0.4f)
                        },
                        shape = RoundedCornerShape(50),
                    ),
            )
        }
    }
}

@Composable
private fun AboutYouStep(state: RegisterUiState, viewModel: RegisterViewModel) {
    CctnTextField(
        value = state.firstname,
        onValueChange = viewModel::onFirstname,
        label = "First name",
        error = state.error("firstname"),
        enabled = !state.submitting,
    )
    CctnTextField(
        value = state.middlename,
        onValueChange = viewModel::onMiddlename,
        label = "Middle name (optional)",
        error = state.error("middlename"),
        enabled = !state.submitting,
    )
    CctnTextField(
        value = state.lastname,
        onValueChange = viewModel::onLastname,
        label = "Last name",
        error = state.error("lastname"),
        enabled = !state.submitting,
    )
    BirthdateAndAge(
        birthdate = state.birthdate,
        age = state.age,
        onBirthdate = viewModel::onBirthdate,
        error = state.error("birthdate"),
        enabled = !state.submitting,
    )

    CctnDropdownField(
        value = state.gender,
        options = ServiceArea.GENDERS,
        onOptionSelected = viewModel::onGender,
        label = "Gender",
        error = state.error("gender"),
        enabled = !state.submitting,
    )
    Spacer(Modifier.height(8.dp))
    CctnDropdownField(
        value = state.civilStatus,
        options = ServiceArea.CIVIL_STATUSES,
        onOptionSelected = viewModel::onCivilStatus,
        label = "Civil status",
        error = state.error("civil_status"),
        enabled = !state.submitting,
    )
    Spacer(Modifier.height(8.dp))
    CctnTextField(
        value = state.placeOfBirth,
        onValueChange = viewModel::onPlaceOfBirth,
        label = "Place of birth (optional)",
        error = state.error("place_of_birth"),
        enabled = !state.submitting,
        imeAction = ImeAction.Done,
    )
}

@Composable
private fun AddressStep(state: RegisterUiState, viewModel: RegisterViewModel) {
    CctnTextField(
        value = state.contactNo,
        onValueChange = viewModel::onContactNo,
        label = "Mobile number",
        placeholder = "09123456789",
        error = state.error("contact_no"),
        enabled = !state.submitting,
        keyboardType = KeyboardType.Number,
        supportingText = "11 digits starting with 09.",
    )
    CctnTextField(
        value = state.email,
        onValueChange = viewModel::onEmail,
        label = "Email address",
        error = state.error("email"),
        enabled = !state.submitting,
        keyboardType = KeyboardType.Email,
    )
    CctnTextField(
        value = ServiceArea.PROVINCE,
        onValueChange = {},
        label = "Province",
        enabled = false,
        supportingText = "We currently serve Bantayan Island only.",
    )
    CctnDropdownField(
        value = state.municipality,
        options = ServiceArea.MUNICIPALITIES,
        onOptionSelected = viewModel::onMunicipality,
        label = "Municipality",
        error = state.error("address_municipality"),
        enabled = !state.submitting,
    )
    Spacer(Modifier.height(8.dp))
    CctnDropdownField(
        value = state.barangay,
        options = state.barangayOptions,
        onOptionSelected = viewModel::onBarangay,
        label = "Barangay",
        error = state.error("address_barangay"),
        enabled = !state.submitting,
    )
}

@Composable
private fun AccountStep(state: RegisterUiState, viewModel: RegisterViewModel) {
    CctnTextField(
        value = state.username,
        onValueChange = viewModel::onUsername,
        label = "Username",
        error = state.error("username"),
        enabled = !state.submitting,
    )
    CctnPasswordField(
        value = state.password,
        onValueChange = viewModel::onPassword,
        label = "Password",
        error = state.error("password"),
        enabled = !state.submitting,
        supportingText = "At least 8 characters.",
    )
    CctnPasswordField(
        value = state.passwordConfirmation,
        onValueChange = viewModel::onPasswordConfirmation,
        label = "Confirm password",
        error = state.error("password_confirmation"),
        enabled = !state.submitting,
        imeAction = ImeAction.Done,
    )
}
