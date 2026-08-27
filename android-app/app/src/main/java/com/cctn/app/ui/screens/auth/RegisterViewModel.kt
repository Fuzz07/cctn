package com.cctn.app.ui.screens.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.core.Formatters
import com.cctn.app.core.ServiceArea
import com.cctn.app.core.Validators
import com.cctn.app.data.remote.dto.RegisterRequest
import com.cctn.app.data.repo.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate
import javax.inject.Inject

data class RegisterUiState(
    val step: Int = 1,
    // Step 1 — about you
    val firstname: String = "",
    val middlename: String = "",
    val lastname: String = "",
    val birthdate: LocalDate? = null,
    val gender: String = "",
    val civilStatus: String = "",
    val placeOfBirth: String = "",
    // Step 2 — contact and address
    val contactNo: String = "",
    val email: String = "",
    val municipality: String = ServiceArea.MUNICIPALITIES.first(),
    val barangay: String = "",
    // Step 3 — account
    val username: String = "",
    val password: String = "",
    val passwordConfirmation: String = "",

    val errors: Map<String, String> = emptyMap(),
    val formError: String? = null,
    val submitting: Boolean = false,
) {
    val barangayOptions: List<String> get() = ServiceArea.barangays(municipality)

    /** Derived, never stored: it cannot drift from the birthdate it describes. */
    val age: Int? get() = Formatters.age(birthdate)
    val isLastStep: Boolean get() = step == TOTAL_STEPS

    fun error(field: String): String? = errors[field]

    companion object {
        const val TOTAL_STEPS = 3
    }
}

@HiltViewModel
class RegisterViewModel @Inject constructor(
    private val authRepository: AuthRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(RegisterUiState())
    val state: StateFlow<RegisterUiState> = _state.asStateFlow()

    // ── Field updates ────────────────────────────────────────────────────────

    private fun update(field: String, block: (RegisterUiState) -> RegisterUiState) {
        _state.update { current ->
            block(current).copy(
                errors = current.errors - field,
                formError = null,
            )
        }
    }

    fun onFirstname(v: String) = update("firstname") { it.copy(firstname = v) }
    fun onMiddlename(v: String) = update("middlename") { it.copy(middlename = v) }
    fun onLastname(v: String) = update("lastname") { it.copy(lastname = v) }
    fun onBirthdate(v: LocalDate) = update("birthdate") { it.copy(birthdate = v) }
    fun onGender(v: String) = update("gender") { it.copy(gender = v) }
    fun onCivilStatus(v: String) = update("civil_status") { it.copy(civilStatus = v) }
    fun onPlaceOfBirth(v: String) = update("place_of_birth") { it.copy(placeOfBirth = v) }
    fun onEmail(v: String) = update("email") { it.copy(email = v) }
    fun onUsername(v: String) = update("username") { it.copy(username = v) }
    fun onPassword(v: String) = update("password") { it.copy(password = v) }
    fun onPasswordConfirmation(v: String) =
        update("password_confirmation") { it.copy(passwordConfirmation = v) }

    fun onContactNo(v: String) = update("contact_no") {
        it.copy(contactNo = Validators.sanitizeMobile(v))
    }

    /** Changing municipality invalidates the barangay chosen under the old one. */
    fun onMunicipality(v: String) = update("address_municipality") {
        it.copy(
            municipality = v,
            barangay = if (ServiceArea.barangays(v).contains(it.barangay)) it.barangay else "",
        )
    }

    fun onBarangay(v: String) = update("address_barangay") { it.copy(barangay = v) }

    fun onGoogleIdToken(idToken: String) {
        val current = _state.value
        if (current.submitting) return

        _state.update { it.copy(submitting = true, formError = null) }

        viewModelScope.launch {
            when (val result = authRepository.googleLogin(idToken)) {
                is AppResult.Success -> {
                    _state.update { it.copy(submitting = false) }
                }

                is AppResult.Failure -> {
                    _state.update {
                        it.copy(
                            submitting = false,
                            formError = result.error.message,
                        )
                    }
                }
            }
        }
    }

    fun onGoogleSignInError(error: String) = _state.update {
        it.copy(formError = error, submitting = false)
    }

    // ── Steps ────────────────────────────────────────────────────────────────

    fun back() {
        _state.update { if (it.step > 1) it.copy(step = it.step - 1, formError = null) else it }
    }

    fun next() {
        val current = _state.value
        val errors = validate(current.step, current)
        if (errors.isNotEmpty()) {
            _state.update { it.copy(errors = errors) }
            return
        }
        _state.update { it.copy(step = (it.step + 1).coerceAtMost(RegisterUiState.TOTAL_STEPS)) }
    }

    private fun validate(step: Int, s: RegisterUiState): Map<String, String> = buildMap {
        when (step) {
            1 -> {
                Validators.name(s.firstname, "First name")?.let { put("firstname", it) }
                Validators.name(s.middlename, "Middle name", required = false)
                    ?.let { put("middlename", it) }
                Validators.name(s.lastname, "Last name")?.let { put("lastname", it) }
                if (s.birthdate == null) {
                    put("birthdate", "Birth date is required.")
                } else if (s.birthdate.isAfter(LocalDate.now())) {
                    put("birthdate", "Birth date cannot be in the future.")
                }
                Validators.required(s.gender, "Gender")?.let { put("gender", it) }
                Validators.required(s.civilStatus, "Civil status")?.let { put("civil_status", it) }
                Validators.name(s.placeOfBirth, "Place of birth", required = false, max = 100)
                    ?.let { put("place_of_birth", it) }
            }

            2 -> {
                Validators.mobile(s.contactNo)?.let { put("contact_no", it) }
                Validators.email(s.email)?.let { put("email", it) }
                Validators.required(s.municipality, "Municipality")
                    ?.let { put("address_municipality", it) }
                Validators.required(s.barangay, "Barangay")?.let { put("address_barangay", it) }
            }

            3 -> {
                Validators.username(s.username)?.let { put("username", it) }
                Validators.password(s.password)?.let { put("password", it) }
                Validators.passwordConfirmation(s.password, s.passwordConfirmation)
                    ?.let { put("password_confirmation", it) }
            }
        }
    }

    fun submit() {
        val current = _state.value
        if (current.submitting) return

        // Re-check every step, not just the visible one: a field can be made
        // invalid by going back and editing it.
        val errors = (1..RegisterUiState.TOTAL_STEPS)
            .flatMap { validate(it, current).entries }
            .associate { it.key to it.value }

        if (errors.isNotEmpty()) {
            val firstBadStep = (1..RegisterUiState.TOTAL_STEPS)
                .firstOrNull { validate(it, current).isNotEmpty() } ?: current.step
            _state.update { it.copy(errors = errors, step = firstBadStep) }
            return
        }

        _state.update { it.copy(submitting = true, formError = null) }

        viewModelScope.launch {
            val request = RegisterRequest(
                firstname = current.firstname.trim(),
                middlename = current.middlename.trim().takeIf { it.isNotEmpty() },
                lastname = current.lastname.trim(),
                birthdate = current.birthdate?.format(Formatters.API_DATE),
                email = current.email.trim(),
                username = current.username.trim(),
                password = current.password,
                passwordConfirmation = current.passwordConfirmation,
                contactNo = current.contactNo.trim(),
                addressBarangay = current.barangay,
                addressMunicipality = current.municipality,
                addressProvince = ServiceArea.PROVINCE,
                gender = current.gender,
                civilStatus = current.civilStatus,
                placeOfBirth = current.placeOfBirth.trim().takeIf { it.isNotEmpty() },
            )

            when (val result = authRepository.register(request)) {
                is AppResult.Success -> _state.update { it.copy(submitting = false) }

                is AppResult.Failure -> {
                    val fieldErrors = result.error.fieldErrors
                    // Send the user to the step that actually holds the field the
                    // server rejected, rather than leaving them on step 3.
                    val targetStep = fieldErrors.keys.firstNotNullOfOrNull { STEP_OF_FIELD[it] }

                    _state.update {
                        it.copy(
                            submitting = false,
                            errors = fieldErrors,
                            step = targetStep ?: it.step,
                            formError = if (fieldErrors.isEmpty()) result.error.message else null,
                        )
                    }
                }
            }
        }
    }

    private companion object {
        val STEP_OF_FIELD = mapOf(
            "firstname" to 1,
            "middlename" to 1,
            "lastname" to 1,
            "birthdate" to 1,
            "age" to 1,
            "gender" to 1,
            "civil_status" to 1,
            "place_of_birth" to 1,
            "contact_no" to 2,
            "email" to 2,
            "address_municipality" to 2,
            "address_barangay" to 2,
            "address_province" to 2,
            "username" to 3,
            "password" to 3,
            "password_confirmation" to 3,
        )
    }
}
