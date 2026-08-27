package com.cctn.app.ui.screens.support

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.outlined.SupportAgent
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.ExtendedFloatingActionButton
import androidx.compose.material3.FilterChip
import androidx.compose.material3.FilterChipDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
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
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.MaintenanceDto
import com.cctn.app.ui.components.CctnTextField
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.PageHeading
import com.cctn.app.ui.components.EmptyState
import com.cctn.app.ui.components.ErrorState
import com.cctn.app.ui.components.LoadingButton
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.components.StatusChip

@Composable
fun SupportScreen(
    onOpenAssistant: () -> Unit,
    viewModel: SupportViewModel = hiltViewModel(),
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
        // The app-level Column already clears the status bar and the
        // bottom bar handles its own inset, so this Scaffold must not
        // add either a second time.
        contentWindowInsets = WindowInsets(0, 0, 0, 0),
        topBar = {
            CctnTopBar(
                refreshing = state.refreshing,
                onRefresh = viewModel::refresh,
                onOpenAssistant = onOpenAssistant,
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
        floatingActionButton = {
            if (state.requests.isNotEmpty()) {
                ExtendedFloatingActionButton(
                    onClick = viewModel::openComposer,
                    containerColor = MaterialTheme.colorScheme.primary,
                    contentColor = MaterialTheme.colorScheme.onPrimary,
                    icon = { Icon(Icons.Filled.Add, contentDescription = null) },
                    text = { Text("New request") },
                )
            }
        },
    ) { padding ->
        val content = Modifier
            .fillMaxSize()
            .padding(padding)

        when {
            state.loading -> LoadingState(content)

            state.requests.isEmpty() && state.error != null -> ErrorState(
                message = state.error.orEmpty(),
                onRetry = viewModel::refresh,
                modifier = content,
            )

            state.requests.isEmpty() -> EmptyState(
                icon = Icons.Outlined.SupportAgent,
                title = "No support requests",
                body = "Having trouble with your connection? Tell us about it and our technicians will follow up.",
                actionLabel = "Report a problem",
                onAction = viewModel::openComposer,
                modifier = content,
            )

            else -> LazyColumn(
                modifier = content,
                contentPadding = PaddingValues(start = 16.dp, end = 16.dp, top = 8.dp, bottom = 92.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                item {
                    PageHeading(
                        title = "Support",
                        subtitle = "Report a fault and follow what the team does about it.",
                    )
                }

                items(state.requests, key = { it.id }) { request -> RequestCard(request) }
            }
        }
    }

    if (state.composerOpen) {
        ComposerDialog(state = state, viewModel = viewModel)
    }
}

@Composable
private fun ComposerDialog(state: SupportUiState, viewModel: SupportViewModel) {
    AlertDialog(
        onDismissRequest = viewModel::closeComposer,
        title = { Text("Report a problem") },
        text = {
            Column(
                modifier = Modifier.verticalScroll(rememberScrollState()),
            ) {
                CctnTextField(
                    value = state.subject,
                    onValueChange = viewModel::onSubject,
                    label = "Subject",
                    placeholder = "No internet since this morning",
                    error = state.subjectError,
                    enabled = !state.submitting,
                )

                CctnTextField(
                    value = state.description,
                    onValueChange = viewModel::onDescription,
                    label = "What is happening?",
                    error = state.descriptionError,
                    enabled = !state.submitting,
                    singleLine = false,
                    minLines = 3,
                    imeAction = ImeAction.Done,
                    supportingText = "${state.description.length}/1000",
                )

                Spacer(Modifier.height(4.dp))

                Text(
                    text = "How urgent is it?",
                    style = MaterialTheme.typography.labelLarge,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
                Spacer(Modifier.height(8.dp))
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    Priority.entries.forEach { priority ->
                        FilterChip(
                            selected = state.priority == priority,
                            onClick = { viewModel.onPriority(priority) },
                            enabled = !state.submitting,
                            label = { Text(priority.label) },
                            colors = FilterChipDefaults.filterChipColors(
                                selectedContainerColor = MaterialTheme.colorScheme.primaryContainer,
                                selectedLabelColor = MaterialTheme.colorScheme.onPrimaryContainer,
                            ),
                        )
                    }
                }

                if (state.submitError != null) {
                    Spacer(Modifier.height(10.dp))
                    Text(
                        text = state.submitError.orEmpty(),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error,
                    )
                }
            }
        },
        confirmButton = {
            LoadingButton(
                text = "Send",
                onClick = viewModel::submit,
                loading = state.submitting,
            )
        },
        dismissButton = {
            TextButton(onClick = viewModel::closeComposer, enabled = !state.submitting) {
                Text("Cancel")
            }
        },
    )
}

@Composable
private fun RequestCard(request: MaintenanceDto) {
    SectionCard {
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top,
            ) {
                Text(
                    text = request.subject,
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                    modifier = Modifier.weight(1f),
                )
                StatusChip(request.status)
            }

            Spacer(Modifier.height(6.dp))

            Text(
                text = request.description,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )

            Spacer(Modifier.height(10.dp))

            Text(
                text = Formatters.titleCase(request.priority) + " priority  ·  " +
                    Formatters.timestamp(request.createdAt),
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )

            if (!request.followUpNote.isNullOrBlank()) {
                Spacer(Modifier.height(10.dp))
                Text(
                    text = "Update from BCTVI: " + request.followUpNote.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurface,
                )
            }
        }
    }
}
