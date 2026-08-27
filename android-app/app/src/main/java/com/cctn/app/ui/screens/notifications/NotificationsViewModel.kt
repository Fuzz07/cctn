package com.cctn.app.ui.screens.notifications

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.NotificationDto
import com.cctn.app.data.repo.NotificationRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class NotificationsUiState(
    val loading: Boolean = true,
    val refreshing: Boolean = false,
    val notifications: List<NotificationDto> = emptyList(),
    val unreadCount: Int = 0,
    val error: String? = null,
)

@HiltViewModel
class NotificationsViewModel @Inject constructor(
    private val repository: NotificationRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(NotificationsUiState())
    val state: StateFlow<NotificationsUiState> = _state.asStateFlow()

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            when (val result = repository.list()) {
                is AppResult.Success -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        notifications = result.data.notifications,
                        unreadCount = result.data.unreadCount,
                        error = null,
                    )
                }

                is AppResult.Failure -> _state.update {
                    it.copy(
                        loading = false,
                        refreshing = false,
                        error = result.error.message,
                    )
                }
            }
        }
    }

    fun refresh() {
        _state.update { it.copy(refreshing = true) }
        load()
    }

    fun markAsRead(notification: NotificationDto) {
        if (notification.isRead) return

        // Optimistically mark as read in local list
        _state.update { s ->
            val updated = s.notifications.map {
                if (it.id == notification.id) it.copy(isRead = true) else it
            }
            s.copy(
                notifications = updated,
                unreadCount = (s.unreadCount - 1).coerceAtLeast(0),
            )
        }

        viewModelScope.launch {
            repository.markRead(notification.id)
        }
    }

    fun markAllAsRead() {
        // Optimistically mark all as read
        _state.update { s ->
            s.copy(
                notifications = s.notifications.map { it.copy(isRead = true) },
                unreadCount = 0,
            )
        }

        viewModelScope.launch {
            repository.markAllRead()
        }
    }
}
