package com.cctn.app.ui.screens.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.AppointmentDto
import com.cctn.app.data.remote.dto.ClientDto
import com.cctn.app.data.repo.AppointmentRepository
import com.cctn.app.data.repo.BillingRepository
import com.cctn.app.data.session.SessionManager
import com.cctn.app.data.session.SessionState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.async
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

data class HomeUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val error: String? = null,
    val balance: Double = 0.0,
    val unpaidCount: Int = 0,
    val upcoming: AppointmentDto? = null,
    val recent: List<AppointmentDto> = emptyList(),
    val pendingCount: Int = 0,
    val approvedCount: Int = 0,
    val cancelledCount: Int = 0,
    val totalAppointments: Int = 0,
)

/** How many rows the dashboard's "Recent Appointments" card shows. */
private const val RECENT_LIMIT = 5

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val appointmentRepository: AppointmentRepository,
    private val billingRepository: BillingRepository,
    session: SessionManager,
) : ViewModel() {

    val client: StateFlow<ClientDto?> = session.state
        .map { (it as? SessionState.SignedIn)?.client }
        .stateIn(
            scope = viewModelScope,
            started = SharingStarted.WhileSubscribed(5_000),
            initialValue = session.currentClient,
        )

    private val _state = MutableStateFlow(HomeUiState())
    val state: StateFlow<HomeUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun refresh() = load(isRefresh = true)

    private fun load(isRefresh: Boolean = false) {
        _state.update {
            it.copy(loading = !isRefresh && it.upcoming == null, refreshing = isRefresh, error = null)
        }

        viewModelScope.launch {
            // Both panels are on the same screen, so fetch them together
            // rather than making the user wait for one and then the other.
            val appointmentsDeferred = async { appointmentRepository.list() }
            val billingDeferred = async { billingRepository.load() }

            val appointments = appointmentsDeferred.await()
            val billing = billingDeferred.await()

            // A failure in one half must not blank the other, but it does have
            // to be reported: silently leaving a section at zero would present
            // "no bookings" as fact when the request simply did not arrive.
            val errorMessage = listOfNotNull(
                (appointments as? AppResult.Failure)?.error?.message,
                (billing as? AppResult.Failure)?.error?.message,
            ).firstOrNull()

            _state.update { current ->
                var next = current.copy(loading = false, refreshing = false, error = errorMessage)

                if (appointments is AppResult.Success) {
                    val list = appointments.data
                    next = next.copy(
                        upcoming = list.nextUpcoming(),
                        recent = list.take(RECENT_LIMIT),
                        pendingCount = list.count { it.status.equals("pending", true) },
                        approvedCount = list.count { it.status.equals("approved", true) },
                        cancelledCount = list.count { it.status.equals("cancelled", true) },
                        totalAppointments = list.size,
                    )
                }

                if (billing is AppResult.Success) {
                    next = next.copy(
                        balance = billing.data.balance,
                        unpaidCount = billing.data.statements.count {
                            !it.status.equals("paid", true)
                        },
                    )
                }

                next
            }
        }
    }
}

/**
 * The next appointment worth showing: today or later, not cancelled, soonest
 * first. The list arrives newest-first, so it has to be searched rather than
 * simply taking the first entry.
 */
internal fun List<AppointmentDto>.nextUpcoming(today: LocalDate = LocalDate.now()): AppointmentDto? =
    filter { appointment ->
        val notCancelled = !appointment.status.equals("cancelled", true)
        val date = appointment.preferredDate
            ?.let { runCatching { LocalDate.parse(it) }.getOrNull() }
        notCancelled && date != null && !date.isBefore(today)
    }.minByOrNull { "${it.preferredDate} ${it.preferredTime.orEmpty()}" }
