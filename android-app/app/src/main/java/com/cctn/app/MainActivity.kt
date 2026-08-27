package com.cctn.app

import android.content.Intent
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cctn.app.data.session.SessionState
import com.cctn.app.ui.CctnApp
import com.cctn.app.ui.theme.CctnTheme
import dagger.hilt.android.AndroidEntryPoint
import androidx.activity.viewModels
import androidx.compose.runtime.getValue

@AndroidEntryPoint
class MainActivity : ComponentActivity() {

    private val viewModel: MainViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        val splashScreen = installSplashScreen()
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        handleAuthIntent(intent)

        // Hold the splash screen until the stored session has been read, so the
        // app opens straight onto the right screen instead of flashing the
        // login form at someone who is already signed in.
        splashScreen.setKeepOnScreenCondition {
            viewModel.sessionState.value == SessionState.Loading
        }

        setContent {
            CctnTheme {
                val sessionState by viewModel.sessionState.collectAsStateWithLifecycle()
                val isOnline by viewModel.isOnline.collectAsStateWithLifecycle()

                CctnApp(sessionState = sessionState, isOnline = isOnline)
            }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleAuthIntent(intent)
    }

    private fun handleAuthIntent(intent: Intent?) {
        runCatching {
            val data = intent?.data ?: return
            if (data.scheme == "cctn" && data.host == "auth") {
                val token = data.getQueryParameter("token")
                if (!token.isNullOrBlank()) {
                    viewModel.handleAuthToken(token)
                }
            }
        }
    }
}
