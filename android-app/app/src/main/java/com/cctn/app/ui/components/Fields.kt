package com.cctn.app.ui.components

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsFocusedAsState
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.RowScope
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.KeyboardArrowDown
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.ExposedDropdownMenuBox
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp

/*
 * The site's form control, rebuilt in Compose.
 *
 * The web draws a field as three separate things stacked up — a bold label, a
 * bordered box, then a message underneath — which is not what Material's
 * outlined field does with its floating label cutting through the border. So
 * the box here is assembled from a BasicTextField rather than adapted from
 * OutlinedTextField, and matches .auth-input / .c-input:
 *
 *     border: 1px solid #cbd5e1;  border-radius: 8px;  background: #f8fafc;
 *     :focus { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
 *
 * The focus ring is a 3dp ground behind the box rather than padding on it, so
 * a field is exactly as tall focused as it is at rest and nothing on the form
 * shifts when it is tapped.
 */

private val FieldShape = RoundedCornerShape(8.dp)
private val RingShape = RoundedCornerShape(11.dp)
private const val RING_ALPHA = 0.10f

/** The gap under a field, so a stack of them spaces itself. */
private val FieldGap = 14.dp

/** `.auth-input-group label` — 0.85rem / 700, above the box. */
@Composable
fun FieldLabel(text: String, modifier: Modifier = Modifier) {
    Text(
        text = text,
        style = MaterialTheme.typography.labelMedium,
        color = MaterialTheme.colorScheme.onSurface,
        modifier = modifier.padding(bottom = 6.dp),
    )
}

/**
 * The bordered box itself, without knowing what sits inside it.
 *
 * Everything that has to read as a field on these screens goes through here —
 * typed input, the read-only date and age, the dropdowns — so they cannot
 * drift apart.
 */
@Composable
fun FieldBox(
    focused: Boolean,
    hasError: Boolean,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    content: @Composable RowScope.() -> Unit,
) {
    val scheme = MaterialTheme.colorScheme
    val border = when {
        hasError -> scheme.error
        focused -> scheme.primary
        else -> scheme.outline
    }
    val ring = when {
        hasError -> scheme.error.copy(alpha = RING_ALPHA)
        focused -> scheme.primary.copy(alpha = RING_ALPHA)
        else -> Color.Transparent
    }
    // Focus turns the ground white on the site; at rest it is the page colour.
    val fill = when {
        !enabled -> scheme.surfaceVariant
        focused -> scheme.surface
        else -> scheme.background
    }

    Box(
        modifier = modifier
            .fillMaxWidth()
            .background(ring, RingShape)
            .padding(3.dp),
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .background(fill, FieldShape)
                .border(1.dp, border, FieldShape)
                .padding(horizontal = 14.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
            content = content,
        )
    }
}

/** The message slot under a field: the error if there is one, else the hint. */
@Composable
fun FieldMessage(error: String?, supportingText: String?) {
    val message = error ?: supportingText ?: return
    Text(
        text = message,
        style = MaterialTheme.typography.bodySmall,
        color = if (error != null) {
            MaterialTheme.colorScheme.error
        } else {
            MaterialTheme.colorScheme.onSurfaceVariant
        },
        modifier = Modifier.padding(top = 5.dp, start = 3.dp),
    )
}

@Composable
fun CctnTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    error: String? = null,
    enabled: Boolean = true,
    singleLine: Boolean = true,
    minLines: Int = 1,
    keyboardType: KeyboardType = KeyboardType.Text,
    imeAction: ImeAction = ImeAction.Next,
    placeholder: String? = null,
    supportingText: String? = null,
    leadingIcon: ImageVector? = null,
) {
    val interactionSource = remember { MutableInteractionSource() }
    val focused by interactionSource.collectIsFocusedAsState()

    Column(
        modifier
            .fillMaxWidth()
            .padding(bottom = FieldGap)
    ) {
        FieldLabel(label)

        FieldBox(focused = focused, hasError = error != null, enabled = enabled) {
            if (leadingIcon != null) {
                Icon(
                    imageVector = leadingIcon,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(18.dp),
                )
                Spacer(Modifier.width(10.dp))
            }

            Box(Modifier.weight(1f)) {
                if (value.isEmpty() && placeholder != null) {
                    Text(
                        text = placeholder,
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                }
                BasicTextField(
                    value = value,
                    onValueChange = onValueChange,
                    enabled = enabled,
                    singleLine = singleLine,
                    minLines = minLines,
                    textStyle = MaterialTheme.typography.bodyMedium.copy(
                        color = if (enabled) {
                            MaterialTheme.colorScheme.onSurface
                        } else {
                            MaterialTheme.colorScheme.onSurfaceVariant
                        },
                    ),
                    cursorBrush = SolidColor(MaterialTheme.colorScheme.primary),
                    keyboardOptions = KeyboardOptions(
                        keyboardType = keyboardType,
                        imeAction = imeAction,
                    ),
                    interactionSource = interactionSource,
                    modifier = Modifier.fillMaxWidth(),
                )
            }
        }

        FieldMessage(error, supportingText)
    }
}

@Composable
fun CctnPasswordField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    error: String? = null,
    enabled: Boolean = true,
    imeAction: ImeAction = ImeAction.Next,
    supportingText: String? = null,
    placeholder: String? = null,
    leadingIcon: ImageVector? = null,
) {
    var visible by remember { mutableStateOf(false) }
    val interactionSource = remember { MutableInteractionSource() }
    val focused by interactionSource.collectIsFocusedAsState()

    Column(
        modifier
            .fillMaxWidth()
            .padding(bottom = FieldGap)
    ) {
        FieldLabel(label)

        FieldBox(focused = focused, hasError = error != null, enabled = enabled) {
            if (leadingIcon != null) {
                Icon(
                    imageVector = leadingIcon,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(18.dp),
                )
                Spacer(Modifier.width(10.dp))
            }

            Box(Modifier.weight(1f)) {
                if (value.isEmpty() && placeholder != null) {
                    Text(
                        text = placeholder,
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                }
                BasicTextField(
                    value = value,
                    onValueChange = onValueChange,
                    enabled = enabled,
                    singleLine = true,
                    visualTransformation = if (visible) {
                        VisualTransformation.None
                    } else {
                        PasswordVisualTransformation()
                    },
                    textStyle = MaterialTheme.typography.bodyMedium.copy(
                        color = MaterialTheme.colorScheme.onSurface,
                    ),
                    cursorBrush = SolidColor(MaterialTheme.colorScheme.primary),
                    keyboardOptions = KeyboardOptions(
                        keyboardType = KeyboardType.Password,
                        imeAction = imeAction,
                    ),
                    interactionSource = interactionSource,
                    modifier = Modifier.fillMaxWidth(),
                )
            }

            Spacer(Modifier.width(8.dp))
            Icon(
                imageVector = if (visible) Icons.Filled.VisibilityOff else Icons.Filled.Visibility,
                contentDescription = if (visible) "Hide password" else "Show password",
                tint = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier
                    .size(20.dp)
                    .clickable(enabled = enabled) { visible = !visible },
            )
        }

        FieldMessage(error, supportingText)
    }
}

/**
 * A field whose value is real but cannot be typed over — the province on the
 * address step, the age beside a birth date, the date itself.
 */
@Composable
fun CctnReadOnlyField(
    value: String,
    label: String,
    modifier: Modifier = Modifier,
    placeholder: String? = null,
    error: String? = null,
    supportingText: String? = null,
    trailingIcon: ImageVector? = null,
    enabled: Boolean = true,
    onClick: (() -> Unit)? = null,
) {
    Column(
        modifier
            .fillMaxWidth()
            .padding(bottom = FieldGap)
    ) {
        FieldLabel(label)

        FieldBox(
            focused = false,
            hasError = error != null,
            enabled = enabled && onClick != null,
            modifier = if (onClick != null) {
                Modifier.clickable(enabled = enabled, onClick = onClick)
            } else {
                Modifier
            },
        ) {
            Text(
                text = value.ifBlank { placeholder.orEmpty() },
                style = MaterialTheme.typography.bodyMedium,
                color = if (value.isBlank()) {
                    MaterialTheme.colorScheme.onSurfaceVariant
                } else {
                    MaterialTheme.colorScheme.onSurface
                },
                modifier = Modifier.weight(1f),
            )
            if (trailingIcon != null) {
                Icon(
                    imageVector = trailingIcon,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(18.dp),
                )
            }
        }

        FieldMessage(error, supportingText)
    }
}

/** A read-only field that opens a menu of fixed choices — the site's select. */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CctnDropdownField(
    value: String,
    options: List<String>,
    onOptionSelected: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    error: String? = null,
    enabled: Boolean = true,
    placeholder: String? = null,
) {
    var expanded by remember { mutableStateOf(false) }
    val open = expanded && enabled

    Column(
        modifier
            .fillMaxWidth()
            .padding(bottom = FieldGap)
    ) {
        FieldLabel(label)

        ExposedDropdownMenuBox(
            expanded = open,
            onExpandedChange = { if (enabled) expanded = it },
            modifier = Modifier.fillMaxWidth(),
        ) {
            FieldBox(
                focused = open,
                hasError = error != null,
                enabled = enabled,
                modifier = Modifier
                    .menuAnchor()
                    .clickable(enabled = enabled) { expanded = !expanded },
            ) {
                Text(
                    text = value.ifBlank { placeholder ?: "Select" },
                    style = MaterialTheme.typography.bodyMedium,
                    color = if (value.isBlank()) {
                        MaterialTheme.colorScheme.onSurfaceVariant
                    } else {
                        MaterialTheme.colorScheme.onSurface
                    },
                    modifier = Modifier.weight(1f),
                )
                Icon(
                    imageVector = Icons.Filled.KeyboardArrowDown,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.size(20.dp),
                )
            }

            ExposedDropdownMenu(
                expanded = open,
                onDismissRequest = { expanded = false },
            ) {
                options.forEach { option ->
                    DropdownMenuItem(
                        text = { Text(option) },
                        onClick = {
                            onOptionSelected(option)
                            expanded = false
                        },
                    )
                }
            }
        }

        FieldMessage(error, null)
    }
}
