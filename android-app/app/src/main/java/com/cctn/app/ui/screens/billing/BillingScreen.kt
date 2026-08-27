package com.cctn.app.ui.screens.billing

import androidx.compose.foundation.background
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
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ReceiptLong
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.core.Formatters
import com.cctn.app.data.remote.dto.BillingStatementDto
import com.cctn.app.ui.components.CctnTopBar
import com.cctn.app.ui.components.PageHeading
import com.cctn.app.ui.components.DetailRow
import com.cctn.app.ui.components.EmptyState
import com.cctn.app.ui.components.ErrorState
import com.cctn.app.ui.components.LoadingState
import com.cctn.app.ui.components.SectionCard
import com.cctn.app.ui.components.StatusChip
import com.cctn.app.ui.theme.BrandRed
import com.cctn.app.ui.theme.BrandRedDark

@Composable
fun BillingScreen(
    onOpenAssistant: () -> Unit,
    viewModel: BillingViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

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
    ) { padding ->
        val content = Modifier
            .fillMaxSize()
            .padding(padding)

        when {
            state.loading -> LoadingState(content)

            state.statements.isEmpty() && state.error != null -> ErrorState(
                message = state.error.orEmpty(),
                onRetry = viewModel::refresh,
                modifier = content,
            )

            else -> LazyColumn(
                modifier = content,
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                item {
                    PageHeading(
                        title = "Billing",
                        subtitle = "Your balance and every statement BCTVI has issued.",
                    )
                }

                item {
                    BalanceCard(
                        balance = state.balance,
                        unpaidCount = state.unpaid.size,
                    )
                }

                if (state.statements.isEmpty()) {
                    item {
                        EmptyState(
                            icon = Icons.AutoMirrored.Outlined.ReceiptLong,
                            title = "No statements yet",
                            body = "Your monthly bills will appear here once your account is active.",
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(280.dp),
                        )
                    }
                } else {
                    item {
                        Text(
                            text = "Statements",
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.onBackground,
                        )
                    }

                    items(state.statements, key = { it.id }) { statement ->
                        StatementCard(statement)
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
    }
}

@Composable
private fun BalanceCard(balance: Double, unpaidCount: Int) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(18.dp))
            .background(Brush.linearGradient(listOf(BrandRed, BrandRedDark)))
            .padding(22.dp),
    ) {
        Text(
            text = "Total outstanding",
            style = MaterialTheme.typography.bodySmall,
            color = Color.White.copy(alpha = 0.85f),
        )
        Spacer(Modifier.height(4.dp))
        Text(
            text = Formatters.peso(balance),
            style = MaterialTheme.typography.headlineMedium,
            color = Color.White,
        )
        Spacer(Modifier.height(6.dp))
        Text(
            text = when (unpaidCount) {
                0 -> "Nothing due. Thank you."
                1 -> "1 unpaid statement"
                else -> "$unpaidCount unpaid statements"
            },
            style = MaterialTheme.typography.bodySmall,
            color = Color.White.copy(alpha = 0.85f),
        )
        Spacer(Modifier.height(14.dp))
        Text(
            text = "Payments are settled at the BCTVI office. This screen reflects what the office has recorded.",
            style = MaterialTheme.typography.bodySmall,
            color = Color.White.copy(alpha = 0.75f),
        )
    }
}

@Composable
private fun StatementCard(statement: BillingStatementDto) {
    SectionCard {
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top,
            ) {
                Column(Modifier.weight(1f)) {
                    Text(
                        text = statement.statementPeriod?.takeIf { it.isNotBlank() }
                            ?: "Statement",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                    )
                    Spacer(Modifier.height(2.dp))
                    Text(
                        text = Formatters.peso(statement.totalAmountDue),
                        style = MaterialTheme.typography.headlineSmall,
                        color = MaterialTheme.colorScheme.primary,
                    )
                }
                StatusChip(statement.status)
            }

            Spacer(Modifier.height(10.dp))

            DetailRow("Amount due", Formatters.peso(statement.amountDue))

            if (statement.penaltyAmount > 0) {
                DetailRow("Penalty", Formatters.peso(statement.penaltyAmount))
            }

            DetailRow("Due date", Formatters.date(statement.dueDate))

            if (statement.paidAt != null) {
                DetailRow("Paid on", Formatters.timestamp(statement.paidAt))
            }

            if (!statement.notes.isNullOrBlank()) {
                Spacer(Modifier.height(8.dp))
                Text(
                    text = statement.notes.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }
        }
    }
}
