package com.cctn.app.ui.components

import androidx.compose.foundation.layout.size
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp

/**
 * The top bar every signed-in screen shares.
 *
 * Refresh is an explicit button rather than a pull gesture: Material 3 at the
 * version this app builds against has no pull-to-refresh component, and a
 * visible control is the one affordance that is also reachable with a
 * screen reader and a switch device.
 */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CctnTopBar(
    title: String,
    modifier: Modifier = Modifier,
    refreshing: Boolean = false,
    onRefresh: (() -> Unit)? = null,
) {
    TopAppBar(
        modifier = modifier,
        title = { Text(title) },
        colors = TopAppBarDefaults.topAppBarColors(
            containerColor = MaterialTheme.colorScheme.background,
            titleContentColor = MaterialTheme.colorScheme.onBackground,
        ),
        actions = {
            if (onRefresh != null) {
                if (refreshing) {
                    // Occupies the same box as the button, so the title does not
                    // shift sideways every time a refresh starts.
                    IconButton(onClick = {}, enabled = false) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(20.dp),
                            strokeWidth = 2.dp,
                            color = MaterialTheme.colorScheme.primary,
                        )
                    }
                } else {
                    IconButton(onClick = onRefresh) {
                        Icon(Icons.Filled.Refresh, contentDescription = "Refresh")
                    }
                }
            }
        },
    )
}
