package com.cctn.app.ui.components

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material3.DatePicker
import androidx.compose.material3.DatePickerDialog
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.SelectableDates
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.rememberDatePickerState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.cctn.app.core.Formatters
import java.time.Instant
import java.time.LocalDate
import java.time.ZoneOffset

/**
 * The app's date picker.
 *
 * Material's picker works in UTC milliseconds, so both conversions pin the zone
 * to UTC. Going through the device zone instead would shift the chosen day by
 * one for anyone east or west of it — which for a birthdate is the difference
 * between the right answer and a wrong one.
 */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CctnDatePickerDialog(
    initial: LocalDate?,
    onDismiss: () -> Unit,
    onSelected: (LocalDate) -> Unit,
    earliest: LocalDate? = null,
    latest: LocalDate? = null,
) {
    val pickerState = rememberDatePickerState(
        initialSelectedDateMillis = (initial ?: LocalDate.now()).toUtcMillis(),
        yearRange = (earliest?.year ?: 1900)..(latest?.year ?: (LocalDate.now().year + 5)),
        selectableDates = remember(earliest, latest) {
            object : SelectableDates {
                private val from = earliest?.toUtcMillis()
                private val to = latest?.toUtcMillis()

                override fun isSelectableDate(utcTimeMillis: Long): Boolean =
                    (from == null || utcTimeMillis >= from) &&
                        (to == null || utcTimeMillis <= to)

                override fun isSelectableYear(year: Int): Boolean =
                    (earliest == null || year >= earliest.year) &&
                        (latest == null || year <= latest.year)
            }
        },
    )

    DatePickerDialog(
        onDismissRequest = onDismiss,
        confirmButton = {
            TextButton(
                onClick = {
                    pickerState.selectedDateMillis
                        ?.let { onSelected(it.toUtcLocalDate()) }
                        ?: onDismiss()
                }
            ) {
                Text("Select")
            }
        },
        dismissButton = { TextButton(onClick = onDismiss) { Text("Cancel") } },
        colors = androidx.compose.material3.DatePickerDefaults.colors(
            containerColor = MaterialTheme.colorScheme.surface,
        ),
    ) {
        DatePicker(state = pickerState)
    }
}

fun LocalDate.toUtcMillis(): Long = atStartOfDay(ZoneOffset.UTC).toInstant().toEpochMilli()

fun Long.toUtcLocalDate(): LocalDate =
    Instant.ofEpochMilli(this).atZone(ZoneOffset.UTC).toLocalDate()

/**
 * Birth date beside the age it implies, as the registration form pairs them.
 *
 * Age is never typed and never stored — it is read off the chosen birth date
 * and refreshes the moment that changes, so the two can never disagree. The
 * server recomputes it the same way from the date it is sent.
 */
/**
 * Birth date beside the age it implies.
 *
 * Age is never typed and never stored — it is read off the chosen birth date
 * and refreshes the moment that changes, so the two can never disagree. The
 * server recomputes it the same way from the date it is sent.
 */
@Composable
fun BirthdateAndAge(
    birthdate: LocalDate?,
    age: Int?,
    onBirthdate: (LocalDate) -> Unit,
    modifier: Modifier = Modifier,
    error: String? = null,
    enabled: Boolean = true,
) {
    var showPicker by remember { mutableStateOf(false) }
    val today = LocalDate.now()

    Row(
        modifier = modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        CctnReadOnlyField(
            value = birthdate?.let { Formatters.date(it.format(Formatters.API_DATE)) }.orEmpty(),
            label = "Birth Date *",
            placeholder = "mm/dd/yyyy",
            error = error,
            enabled = enabled,
            trailingIcon = Icons.Filled.CalendarMonth,
            onClick = { showPicker = true },
            modifier = Modifier.weight(1.55f),
        )

        // Read off the birth date beside it, never typed, so the two can never
        // disagree. The server recomputes it the same way from the date it is
        // sent, which is why the page greys this field out too.
        CctnReadOnlyField(
            value = age?.toString().orEmpty(),
            label = "Age *",
            placeholder = "—",
            enabled = false,
            modifier = Modifier.weight(1f),
        )
    }

    if (showPicker) {
        CctnDatePickerDialog(
            initial = birthdate ?: today.minusYears(25),
            onDismiss = { showPicker = false },
            onSelected = {
                showPicker = false
                onBirthdate(it)
            },
            earliest = today.minusYears(120),
            // Nobody has a birth date in the future, and allowing one would
            // only produce a negative age.
            latest = today,
        )
    }
}
