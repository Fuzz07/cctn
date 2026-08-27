package com.cctn.app.ui.screens.auth

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.BiasAlignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.R
import com.cctn.app.core.ServiceArea
import com.cctn.app.ui.components.BirthdateAndAge
import com.cctn.app.ui.components.BrandWordmark
import com.cctn.app.ui.components.CctnDropdownField
import com.cctn.app.ui.components.CctnPasswordField
import com.cctn.app.ui.components.CctnReadOnlyField
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.SecondaryButton
import com.cctn.app.ui.theme.AuthOnScrim
import com.cctn.app.ui.theme.AuthScrimBottom
import com.cctn.app.ui.theme.AuthScrimTop
import com.cctn.app.ui.theme.LightSystemBarIcons
import com.cctn.app.ui.theme.LightOnSurfaceFaint

/**
 * Registration, laid out as `auth/register.blade.php` lays it out: the same
 * photo and scrim as sign-in, one white card, and the three-step wizard with
 * its numbered markers across the top.
 *
 * "Sign up with Google" is absent for the same reason it is on the sign-in
 * screen — `/api/v1` has no OAuth endpoint to send anyone to.
 */
@Composable
fun RegisterScreen(
    onBack: () -> Unit,
    viewModel: RegisterViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    LightSystemBarIcons()

    Box(Modifier.fillMaxSize()) {
        Image(
            painter = painterResource(R.drawable.login_bg),
            contentDescription = null,
            contentScale = ContentScale.Crop,
            // Cover on a phone shows barely a third of a 4:3 photo. Left of
            // centre is where the BCTVI office is; dead centre is the wires.
            alignment = BiasAlignment(horizontalBias = -0.55f, verticalBias = 0f),
            modifier = Modifier.fillMaxSize(),
        )
        Box(
            Modifier
                .fillMaxSize()
                .background(Brush.verticalGradient(listOf(AuthScrimTop, AuthScrimBottom)))
        )

        Column(
            modifier = Modifier
                .fillMaxSize()
                // Outside the scroll, so the top inset holds the content clear
                // of the status bar instead of scrolling away under it. The
                // photo behind is a sibling and stays full-bleed.
                .statusBarsPadding()
                .verticalScroll(rememberScrollState())
                .imePadding()
                .navigationBarsPadding()
                .padding(horizontal = 20.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Spacer(Modifier.height(40.dp))

            BrandWordmark(subtitle = "BANTAYAN", onDark = true, markSize = 44.dp)

            Spacer(Modifier.height(20.dp))

            RegisterCard {
                Text(
                    text = "Client Registration",
                    style = MaterialTheme.typography.headlineSmall,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Spacer(Modifier.height(6.dp))
                Text(
                    text = when (state.step) {
                        1 -> "Step 1 of 3 — Tell us a little about yourself."
                        2 -> "Step 2 of 3 — Where should we install your connection?"
                        else -> "Step 3 of 3 — Set up your login."
                    },
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center,
                )

                Spacer(Modifier.height(24.dp))

                WizardSteps(current = state.step)

                Spacer(Modifier.height(24.dp))

                SectionTitle(
                    text = when (state.step) {
                        1 -> "Personal Information"
                        2 -> "Contact & Address"
                        else -> "Account & Verification"
                    },
                    icon = when (state.step) {
                        1 -> Icons.Filled.Person
                        2 -> Icons.Filled.LocationOn
                        else -> Icons.Filled.Lock
                    },
                )

                Spacer(Modifier.height(16.dp))

                Column(Modifier.fillMaxWidth()) {
                    when (state.step) {
                        1 -> AboutYouStep(state, viewModel)
                        2 -> AddressStep(state, viewModel)
                        else -> AccountStep(state, viewModel)
                    }
                }

                if (state.formError != null) {
                    Text(
                        text = state.formError.orEmpty(),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error,
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(bottom = 8.dp),
                    )
                }

                Spacer(Modifier.height(8.dp))

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    SecondaryButton(
                        text = "Back",
                        onClick = { if (state.step > 1) viewModel.back() else onBack() },
                        enabled = !state.submitting,
                        icon = Icons.AutoMirrored.Filled.ArrowBack,
                        modifier = Modifier.weight(1f),
                    )
                    LoadingButton(
                        text = if (state.isLastStep) "Create Account" else "Next",
                        onClick = { if (state.isLastStep) viewModel.submit() else viewModel.next() },
                        modifier = Modifier.weight(1.3f),
                        loading = state.submitting,
                        icon = if (state.isLastStep) null else Icons.AutoMirrored.Filled.ArrowForward,
                    )
                }

                Spacer(Modifier.height(22.dp))

                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(
                        text = "Already have an account? ",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                    Text(
                        text = "Sign In Here",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.primary,
                        modifier = Modifier.clickable(enabled = !state.submitting, onClick = onBack),
                    )
                }
            }

            Spacer(Modifier.height(36.dp))
        }
    }
}

/** `.auth-form-card` on the registration page — the same card, wider. */
@Composable
private fun RegisterCard(content: @Composable ColumnScope.() -> Unit) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .widthIn(max = 560.dp)
            .background(MaterialTheme.colorScheme.surface, RoundedCornerShape(20.dp))
            .padding(horizontal = 22.dp, vertical = 28.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        content = content,
    )
}

/**
 * The wizard's markers (`.wizard-step`): a numbered disc per step joined by a
 * rule, with the label under it. A step that is done or current is red — the
 * connector into it too — and the current one carries the 4dp halo the page
 * gives it.
 */
@Composable
private fun WizardSteps(current: Int) {
    val labels = listOf("Personal", "Contact & Address", "Account")

    Row(Modifier.fillMaxWidth()) {
        labels.forEachIndexed { index, label ->
            val number = index + 1
            val reached = number <= current
            val accent = MaterialTheme.colorScheme.primary
            val idle = MaterialTheme.colorScheme.outlineVariant

            Column(
                modifier = Modifier.weight(1f),
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                Box(Modifier.fillMaxWidth(), contentAlignment = Alignment.Center) {
                    // The connector runs from the previous marker into this one,
                    // so it belongs to every step except the first.
                    if (index > 0) {
                        Box(
                            Modifier
                                .fillMaxWidth(0.5f)
                                .align(Alignment.CenterStart)
                                .height(3.dp)
                                .background(if (reached) accent else idle)
                        )
                    }
                    if (index < labels.lastIndex) {
                        Box(
                            Modifier
                                .fillMaxWidth(0.5f)
                                .align(Alignment.CenterEnd)
                                .height(3.dp)
                                .background(if (number < current) accent else idle)
                        )
                    }

                    Box(
                        modifier = Modifier
                            .size(if (number == current) 44.dp else 36.dp)
                            .background(
                                color = if (number == current) {
                                    accent.copy(alpha = 0.15f)
                                } else {
                                    Color.Transparent
                                },
                                shape = CircleShape,
                            ),
                        contentAlignment = Alignment.Center,
                    ) {
                        Box(
                            modifier = Modifier
                                .size(36.dp)
                                .background(if (reached) accent else idle, CircleShape),
                            contentAlignment = Alignment.Center,
                        ) {
                            Text(
                                text = number.toString(),
                                style = MaterialTheme.typography.labelMedium,
                                color = if (reached) {
                                    Color.White
                                } else {
                                    MaterialTheme.colorScheme.onSurfaceVariant
                                },
                            )
                        }
                    }
                }

                Spacer(Modifier.height(7.dp))

                Text(
                    text = label,
                    style = MaterialTheme.typography.bodySmall,
                    color = if (reached) accent else LightOnSurfaceFaint,
                    textAlign = TextAlign.Center,
                )
            }
        }
    }
}

/** `.form-section-title` — a heading with an icon over a 2px slate rule. */
@Composable
private fun SectionTitle(text: String, icon: ImageVector) {
    Column(Modifier.fillMaxWidth()) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.onSurface,
                modifier = Modifier.size(20.dp),
            )
            Spacer(Modifier.size(8.dp))
            Text(
                text = text,
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.onSurface,
            )
        }
        Spacer(Modifier.height(8.dp))
        Box(
            Modifier
                .fillMaxWidth()
                .height(2.dp)
                .background(MaterialTheme.colorScheme.surfaceVariant)
        )
    }
}

@Composable
private fun AboutYouStep(state: RegisterUiState, viewModel: RegisterViewModel) {
    CctnTextField(
        value = state.firstname,
        onValueChange = viewModel::onFirstname,
        label = "First Name *",
        error = state.error("firstname"),
        enabled = !state.submitting,
    )
    CctnTextField(
        value = state.middlename,
        onValueChange = viewModel::onMiddlename,
        label = "Middle Name",
        error = state.error("middlename"),
        enabled = !state.submitting,
    )
    CctnTextField(
        value = state.lastname,
        onValueChange = viewModel::onLastname,
        label = "Last Name *",
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
        label = "Gender *",
        placeholder = "Select Gender",
        error = state.error("gender"),
        enabled = !state.submitting,
    )
    CctnDropdownField(
        value = state.civilStatus,
        options = ServiceArea.CIVIL_STATUSES,
        onOptionSelected = viewModel::onCivilStatus,
        label = "Civil Status *",
        placeholder = "Select Status",
        error = state.error("civil_status"),
        enabled = !state.submitting,
    )
    CctnTextField(
        value = state.placeOfBirth,
        onValueChange = viewModel::onPlaceOfBirth,
        label = "Place of Birth",
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
        label = "Contact No. *",
        placeholder = "09123456789",
        error = state.error("contact_no"),
        enabled = !state.submitting,
        keyboardType = KeyboardType.Number,
        supportingText = "11 digits starting with 09.",
    )
    CctnTextField(
        value = state.email,
        onValueChange = viewModel::onEmail,
        label = "Email Address *",
        error = state.error("email"),
        enabled = !state.submitting,
        keyboardType = KeyboardType.Email,
    )
    CctnReadOnlyField(
        value = ServiceArea.PROVINCE,
        label = "Province",
        supportingText = "We currently serve Bantayan Island only.",
    )
    CctnDropdownField(
        value = state.municipality,
        options = ServiceArea.MUNICIPALITIES,
        onOptionSelected = viewModel::onMunicipality,
        label = "Municipality *",
        error = state.error("address_municipality"),
        enabled = !state.submitting,
    )
    CctnDropdownField(
        value = state.barangay,
        options = state.barangayOptions,
        onOptionSelected = viewModel::onBarangay,
        label = "Barangay *",
        error = state.error("address_barangay"),
        enabled = !state.submitting,
    )
}

@Composable
private fun AccountStep(state: RegisterUiState, viewModel: RegisterViewModel) {
    CctnTextField(
        value = state.username,
        onValueChange = viewModel::onUsername,
        label = "Username *",
        error = state.error("username"),
        enabled = !state.submitting,
    )
    CctnPasswordField(
        value = state.password,
        onValueChange = viewModel::onPassword,
        label = "Password *",
        error = state.error("password"),
        enabled = !state.submitting,
        supportingText = "At least 8 characters.",
    )
    CctnPasswordField(
        value = state.passwordConfirmation,
        onValueChange = viewModel::onPasswordConfirmation,
        label = "Confirm Password *",
        error = state.error("password_confirmation"),
        enabled = !state.submitting,
        imeAction = ImeAction.Done,
    )
}
