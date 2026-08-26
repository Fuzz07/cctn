package com.cctn.app.ui.screens.billing

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.BillingStatementDto
import com.cctn.app.data.repo.BillingRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class BillingUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val balance: Double = 0.0,
    val statements: List<BillingStatementDto> = emptyList(),
    val error: String? = null,
) {
    val unpaid: List<BillingStatementDto>
        get() = statements.filterNot { it.status.equals("paid", true) }
}

@HiltViewModel
class BillingViewModel @Inject constructor(
    private val repository: BillingRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(BillingUiState())
    val state: StateFlow<BillingUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun refresh() = load(isRefresh = true)

    private fun load(isRefresh: Boolean = false) {
        _state.update {
            it.copy(
                loading = !isRefresh && it.statements.isEmpty(),
                refreshing = isRefresh,
                error = null,
            )
        }

        viewModelScope.launch {
            when (val result = repository.load()) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        balance = result.data.balance,
                        statements = result.data.statements,
                        error = null,
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(loading = false, refreshing = false, error = result.error.message)
                }
            }
        }
    }
}
