package com.cctn.app.ui.screens.profile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.core.Formatters
import com.cctn.app.core.ServiceArea
import com.cctn.app.core.Validators
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.data.remote.dto.UpdateProfileRequest
import com.cctn.app.data.repo.AuthRepository
import com.cctn.app.data.repo.ProfileRepository
import com.cctn.app.data.session.SessionManager
import com.cctn.app.data.session.SessionState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate
import javax.inject.Inject

data class ProfileUiState(
    val refreshing: Boolean = false,
    val error: String? = null,
    val message: String? = null,

    val editing: Boolean = false,
    val firstname: String = "",
    val middlename: String = "",
    val lastname: String = "",
    val email: String = "",
    val username: String = "",
    val contactNo: String = "",
    val birthdate: LocalDate? = null,
    val municipality: String = "",
    val barangay: String = "",
    val newPassword: String = "",
    val confirmPassword: String = "",

    val errors: Map<String, String> = emptyMap(),
    val saving: Boolean = false,

    val signOutDialogOpen: Boolean = false,
    val signingOut: Boolean = false,
) {
    val barangayOptions: List<String> get() = ServiceArea.barangays(municipality)

    /** Derived, never stored: it cannot drift from the birthdate it describes. */
    val age: Int? get() = Formatters.age(birthdate)

    fun error(field: String): String? = errors[field]
}

@HiltViewModel
class ProfileViewModel @Inject constructor(
    private val profileRepository: ProfileRepository,
    private val authRepository: AuthRepository,
    private val session: SessionManager,
) : ViewModel() {

    val client: StateFlow<ClientDto?> = session.state
        .map { (it as? SessionState.SignedIn)?.client }
        .stateIn(
            scope = viewModelScope,
            started = SharingStarted.WhileSubscribed(5_000),
            initialValue = session.currentClient,
        )

    private val _state = MutableStateFlow(ProfileUiState())
    val state: StateFlow<ProfileUiState> = _state.asStateFlow()

    init {
        refresh()
    }

    fun refresh() {
        _state.update { it.copy(refreshing = true, error = null) }

        viewModelScope.launch {
            when (val result = profileRepository.refresh()) {
                is AppResult.Success -> _state.update { it.copy(refreshing = false) }
                is AppResult.Failure -> _state.update {
                    it.copy(refreshing = false, error = result.error.message)
                }
            }
        }
    }

    // ── Editing ──────────────────────────────────────────────────────────────

    fun startEditing() {
        val current = client.value ?: return
        val municipality = current.addressMunicipality
            ?.takeIf { it in ServiceArea.MUNICIPALITIES }
            ?: ServiceArea.MUNICIPALITIES.first()

        _state.update {
            it.copy(
                editing = true,
                firstname = current.firstname,
                middlename = current.middlename.orEmpty(),
                lastname = current.lastname,
                email = current.email,
                username = current.username,
                contactNo = current.contactNo.orEmpty(),
                birthdate = current.birthdate
                    ?.let { runCatching { LocalDate.parse(it) }.getOrNull() },
                municipality = municipality,
                // The stored barangay may predate the current list, so only
                // keep it when it is still a valid option for the municipality.
                barangay = current.addressBarangay
                    ?.takeIf { it in ServiceArea.barangays(municipality) }
                    .orEmpty(),
                newPassword = "",
                confirmPassword = "",
                errors = emptyMap(),
            )
        }
    }

    fun cancelEditing() {
        if (_state.value.saving) return
        _state.update { it.copy(editing = false, errors = emptyMap()) }
    }

    private fun update(field: String, block: (ProfileUiState) -> ProfileUiState) {
        _state.update { current -> block(current).copy(errors = current.errors - field) }
    }

    fun onFirstname(v: String) = update("firstname") { it.copy(firstname = v) }
    fun onMiddlename(v: String) = update("middlename") { it.copy(middlename = v) }
    fun onLastname(v: String) = update("lastname") { it.copy(lastname = v) }
    fun onEmail(v: String) = update("email") { it.copy(email = v) }
    fun onUsername(v: String) = update("username") { it.copy(username = v) }
    fun onBirthdate(v: LocalDate) = update("birthdate") { it.copy(birthdate = v) }

    fun onContactNo(v: String) = update("contact_no") {
        it.copy(contactNo = Validators.sanitizeMobile(v))
    }

    fun onMunicipality(v: String) = update("address_municipality") {
        it.copy(
            municipality = v,
            barangay = if (ServiceArea.barangays(v).contains(it.barangay)) it.barangay else "",
        )
    }

    fun onBarangay(v: String) = update("address_barangay") { it.copy(barangay = v) }
    fun onNewPassword(v: String) = update("new_password") { it.copy(newPassword = v) }
    fun onConfirmPassword(v: String) = update("confirm_password") { it.copy(confirmPassword = v) }

    fun save() {
        val current = _state.value
        if (current.saving) return

        val errors = buildMap {
            Validators.name(current.firstname, "First name")?.let { put("firstname", it) }
            Validators.name(current.middlename, "Middle name", required = false)
                ?.let { put("middlename", it) }
            Validators.name(current.lastname, "Last name")?.let { put("lastname", it) }
            Validators.email(current.email)?.let { put("email", it) }
            Validators.username(current.username)?.let { put("username", it) }
            Validators.mobile(current.contactNo)?.let { put("contact_no", it) }
            Validators.required(current.barangay, "Barangay")?.let { put("address_barangay", it) }
            if (current.birthdate?.isAfter(LocalDate.now()) == true) {
                put("birthdate", "Birth date cannot be in the future.")
            }

            // The password fields are optional, and only validated once the
            // user has actually started typing a new one.
            if (current.newPassword.isNotEmpty()) {
                Validators.password(current.newPassword)?.let { put("new_password", it) }
                Validators.passwordConfirmation(current.newPassword, current.confirmPassword)
                    ?.let { put("confirm_password", it) }
            }
        }

        if (errors.isNotEmpty()) {
            _state.update { it.copy(errors = errors) }
            return
        }

        _state.update { it.copy(saving = true, errors = emptyMap()) }

        viewModelScope.launch {
            val existing = client.value

            val request = UpdateProfileRequest(
                firstname = current.firstname.trim(),
                middlename = current.middlename.trim().takeIf { it.isNotEmpty() },
                lastname = current.lastname.trim(),
                email = current.email.trim(),
                username = current.username.trim(),
                contactNo = current.contactNo.trim(),
                // Untouched fields are sent back as they were, so the update
                // does not blank a column the app never edits.
                birthdate = current.birthdate?.format(Formatters.API_DATE)
                    ?: existing?.birthdate,
                gender = existing?.gender,
                civilStatus = existing?.civilStatus,
                placeOfBirth = existing?.placeOfBirth,
                addressBarangay = current.barangay,
                addressMunicipality = current.municipality,
                addressProvince = ServiceArea.PROVINCE,
                newPassword = current.newPassword.takeIf { it.isNotEmpty() },
            )

            when (val result = profileRepository.update(request)) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        saving = false,
                        editing = false,
                        newPassword = "",
                        confirmPassword = "",
                        message = "Profile updated.",
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(
                        saving = false,
                        errors = result.error.fieldErrors,
                        message = if (result.error.fieldErrors.isEmpty()) {
                            result.error.message
                        } else {
                            null
                        },
                    )
                }
            }
        }
    }

    // ── Sign out ─────────────────────────────────────────────────────────────

    fun askToSignOut() = _state.update { it.copy(signOutDialogOpen = true) }

    fun dismissSignOut() = _state.update { it.copy(signOutDialogOpen = false) }

    fun confirmSignOut() {
        if (_state.value.signingOut) return
        _state.update { it.copy(signOutDialogOpen = false, signingOut = true) }

        viewModelScope.launch {
            // Always clears the local session, so this never leaves the user
            // stuck on a screen they asked to leave.
            authRepository.logout()
        }
    }

    fun messageShown() = _state.update { it.copy(message = null) }
}
