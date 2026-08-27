package com.cctn.app.ui.screens.chat

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cctn.app.core.AppResult
import com.cctn.app.data.remote.dto.ChatLinkDto
import com.cctn.app.data.repo.ChatRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

/**
 * One line in the transcript.
 *
 * [link] only ever sits on an assistant turn, and only points somewhere — the
 * assistant does not act on the account, so tapping it opens the screen that
 * does rather than doing anything itself.
 */
data class ChatMessage(
    val id: Long,
    val text: String,
    val fromAssistant: Boolean,
    val link: ChatLinkDto? = null,
)

data class ChatUiState(
    val messages: List<ChatMessage> = emptyList(),
    val suggestions: List<String> = emptyList(),
    val sending: Boolean = false,
    val draft: String = "",
) {
    val canSend: Boolean get() = draft.isNotBlank() && !sending
}

@HiltViewModel
class ChatViewModel @Inject constructor(
    private val repository: ChatRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(ChatUiState())
    val state: StateFlow<ChatUiState> = _state.asStateFlow()

    private var nextId = 0L

    init {
        // An empty message asks the server for its greeting, so the words the
        // assistant opens with live beside the rest of its answers rather than
        // being written twice, once per platform.
        ask(message = "", echo = false)
    }

    fun onDraftChange(value: String) = _state.update { it.copy(draft = value) }

    fun send() {
        val message = _state.value.draft.trim()
        if (message.isEmpty() || _state.value.sending) return

        _state.update { it.copy(draft = "") }
        ask(message, echo = true)
    }

    /** Tapping a suggestion sends it as though it had been typed. */
    fun sendSuggestion(text: String) {
        if (_state.value.sending) return
        ask(text, echo = true)
    }

    private fun ask(message: String, echo: Boolean) {
        _state.update { current ->
            current.copy(
                sending = true,
                // The chips belong to the answer that offered them; clearing
                // them now stops a stale set being tapped mid-request.
                suggestions = emptyList(),
                messages = if (echo) {
                    current.messages + ChatMessage(nextId++, message, fromAssistant = false)
                } else {
                    current.messages
                },
            )
        }

        viewModelScope.launch {
            when (val result = repository.send(message)) {
                is AppResult.Success -> _state.update { current ->
                    current.copy(
                        sending = false,
                        suggestions = result.data.suggestions,
                        messages = current.messages + ChatMessage(
                            id = nextId++,
                            text = result.data.reply,
                            fromAssistant = true,
                            link = result.data.link?.takeIf { !it.screen.isNullOrBlank() },
                        ),
                    )
                }

                is AppResult.Failure -> _state.update { current ->
                    current.copy(
                        sending = false,
                        messages = current.messages + ChatMessage(
                            id = nextId++,
                            text = result.error.message,
                            fromAssistant = true,
                        ),
                    )
                }
            }
        }
    }
}
