package com.cctn.app.ui.screens.auth

import android.annotation.SuppressLint
import android.content.Intent
import android.os.Handler
import android.os.Looper
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebResourceResponse
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Close
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.key
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberUpdatedState
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.semantics.Role
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import com.cctn.app.BuildConfig
import com.cctn.app.ui.components.ErrorState
import com.cctn.app.ui.theme.FeedbackSuccess

/** Where a form's "I'm not a robot" check stands. */
sealed interface RecaptchaStatus {
    /** Still to do. [note] says why, when an earlier check was used up or timed out. */
    data class Unverified(val note: String? = null) : RecaptchaStatus

    /** Done. [token] goes with the next submission; Google accepts it once, within two minutes. */
    data class Verified(val token: String) : RecaptchaStatus

    /** reCAPTCHA is switched off on the server, so the form goes without it. */
    data object NotRequired : RecaptchaStatus
}

val RecaptchaStatus.token: String? get() = (this as? RecaptchaStatus.Verified)?.token

val RecaptchaStatus.isDone: Boolean get() = this !is RecaptchaStatus.Unverified

/**
 * The check once its token has been sent. Google accepts a token only once, so
 * whatever the server made of the rest of the form, the next try needs a new one.
 */
fun RecaptchaStatus.spent(): RecaptchaStatus =
    if (this is RecaptchaStatus.Verified) RecaptchaStatus.Unverified("Please verify again.") else this

/** The check once [RECAPTCHA_TOKEN_LIFETIME_MS] has passed without the form being sent. */
fun RecaptchaStatus.timedOut(): RecaptchaStatus =
    if (this is RecaptchaStatus.Verified) {
        RecaptchaStatus.Unverified("That check timed out. Please verify again.")
    } else {
        this
    }

/** Google's tokens last two minutes; ours are dropped a little before theirs lapse. */
const val RECAPTCHA_TOKEN_LIFETIME_MS = 110_000L

/** The page the website serves for this, from resources/views/mobile/recaptcha.blade.php. */
private const val PAGE_PATH = "mobile/recaptcha"

/** The name the page looks for on `window`. */
private const val BRIDGE_NAME = "CctnRecaptcha"

/**
 * The "I'm not a robot" row, drawn in the shape of Google's checkbox. Tapping it
 * opens the real widget in a dialog, which has the room a picture puzzle needs;
 * the token that comes back is reported through [onVerified].
 */
@Composable
fun RecaptchaField(
    status: RecaptchaStatus,
    onVerified: (String) -> Unit,
    onNotRequired: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    error: String? = null,
) {
    var dialogOpen by rememberSaveable { mutableStateOf(false) }
    val note = error ?: (status as? RecaptchaStatus.Unverified)?.note
    val shape = MaterialTheme.shapes.small

    Column(modifier.fillMaxWidth()) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .clip(shape)
                .border(
                    width = 1.dp,
                    color = if (note != null) {
                        MaterialTheme.colorScheme.error
                    } else {
                        MaterialTheme.colorScheme.outline
                    },
                    shape = shape,
                )
                .clickable(
                    enabled = enabled && status is RecaptchaStatus.Unverified,
                    onClickLabel = "Verify",
                    role = Role.Button,
                ) { dialogOpen = true }
                .padding(horizontal = 14.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            if (status is RecaptchaStatus.Unverified) {
                Box(
                    Modifier
                        .size(26.dp)
                        .background(MaterialTheme.colorScheme.surface, RoundedCornerShape(3.dp))
                        .border(2.dp, MaterialTheme.colorScheme.outline, RoundedCornerShape(3.dp))
                )
            } else {
                Icon(
                    imageVector = Icons.Filled.CheckCircle,
                    contentDescription = null,
                    tint = FeedbackSuccess,
                    modifier = Modifier.size(26.dp),
                )
            }
            Spacer(Modifier.width(12.dp))
            Column(Modifier.weight(1f)) {
                Text(
                    text = when (status) {
                        is RecaptchaStatus.Unverified -> "I'm not a robot"
                        is RecaptchaStatus.Verified -> "Verified"
                        RecaptchaStatus.NotRequired -> "No check needed right now"
                    },
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                if (status is RecaptchaStatus.Unverified) {
                    Text(
                        text = "Tap to verify",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                    )
                }
            }
            Text(
                text = "reCAPTCHA",
                style = MaterialTheme.typography.labelSmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }

        if (note != null) {
            Text(
                text = note,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.error,
                modifier = Modifier.padding(start = 4.dp, top = 6.dp),
            )
        }
    }

    if (dialogOpen) {
        RecaptchaDialog(
            onVerified = { token ->
                dialogOpen = false
                onVerified(token)
            },
            onNotRequired = {
                dialogOpen = false
                onNotRequired()
            },
            onDismiss = { dialogOpen = false },
        )
    }
}

/** Google's widget, full screen, loaded from the website so the site key's domain matches. */
@Composable
private fun RecaptchaDialog(
    onVerified: (String) -> Unit,
    onNotRequired: () -> Unit,
    onDismiss: () -> Unit,
) {
    val theme = if (MaterialTheme.colorScheme.surface.luminance() < 0.5f) "dark" else "light"
    val url = BuildConfig.WEB_BASE_URL + PAGE_PATH + "?theme=" + theme

    // Bumped by "Try again": a fresh WebView rather than a reload of one that failed.
    var attempt by remember { mutableIntStateOf(0) }
    var loading by remember(attempt) { mutableStateOf(true) }
    var failed by remember(attempt) { mutableStateOf(false) }

    Dialog(
        onDismissRequest = onDismiss,
        properties = DialogProperties(usePlatformDefaultWidth = false),
    ) {
        Surface(
            modifier = Modifier.fillMaxSize(),
            color = MaterialTheme.colorScheme.surface,
        ) {
            Column(Modifier.fillMaxSize()) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(start = 4.dp, top = 8.dp, end = 16.dp),
                    verticalAlignment = Alignment.CenterVertically,
                ) {
                    IconButton(onClick = onDismiss) {
                        Icon(Icons.Filled.Close, contentDescription = "Close")
                    }
                    Text(
                        text = "Security check",
                        style = MaterialTheme.typography.titleLarge,
                        color = MaterialTheme.colorScheme.onSurface,
                    )
                }
                Text(
                    text = "Tick “I'm not a robot” below. If Google shows a picture puzzle, solve it to continue.",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp),
                )

                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .weight(1f),
                ) {
                    if (failed) {
                        ErrorState(
                            message = "The security check couldn't load. Check your connection and try again.",
                            onRetry = { attempt++ },
                        )
                    } else {
                        key(attempt) {
                            RecaptchaWebView(
                                url = url,
                                onToken = onVerified,
                                onNotRequired = onNotRequired,
                                onLoaded = { loading = false },
                                onFailed = { failed = true },
                            )
                        }
                        if (loading) {
                            CircularProgressIndicator(
                                color = MaterialTheme.colorScheme.primary,
                                modifier = Modifier
                                    .align(Alignment.TopCenter)
                                    .padding(top = 40.dp),
                            )
                        }
                    }
                }
            }
        }
    }
}

@SuppressLint("SetJavaScriptEnabled")
@Composable
private fun RecaptchaWebView(
    url: String,
    onToken: (String) -> Unit,
    onNotRequired: () -> Unit,
    onLoaded: () -> Unit,
    onFailed: () -> Unit,
) {
    // The WebView outlives recompositions, so it calls through these
    // rather than holding on to the first lambdas it was given.
    val currentOnToken by rememberUpdatedState(onToken)
    val currentOnNotRequired by rememberUpdatedState(onNotRequired)
    val currentOnLoaded by rememberUpdatedState(onLoaded)
    val currentOnFailed by rememberUpdatedState(onFailed)

    AndroidView(
        modifier = Modifier.fillMaxSize(),
        factory = { context ->
            WebView(context).apply {
                setBackgroundColor(android.graphics.Color.TRANSPARENT)
                settings.javaScriptEnabled = true
                settings.domStorageEnabled = true
                // The widget runs in a google.com frame inside our page. Without
                // Google's cookie there, every visitor looks new to it and is
                // handed more picture puzzles.
                CookieManager.getInstance().setAcceptThirdPartyCookies(this, true)
                addJavascriptInterface(
                    RecaptchaBridge(
                        deliverToken = { currentOnToken(it) },
                        reportNotRequired = { currentOnNotRequired() },
                        reportError = { currentOnFailed() },
                    ),
                    BRIDGE_NAME,
                )
                webViewClient = RecaptchaWebViewClient(
                    pageUrl = url,
                    onLoaded = { currentOnLoaded() },
                    onFailed = { currentOnFailed() },
                )
                loadUrl(url)
            }
        },
        onRelease = { it.destroy() },
    )
}

/**
 * What the page reports on `window.CctnRecaptcha`. JavaScript calls these on a
 * background thread, so each one hops to the main thread before touching state.
 * Internal rather than private: the WebView finds these methods by reflection.
 */
internal class RecaptchaBridge(
    private val deliverToken: (String) -> Unit,
    private val reportNotRequired: () -> Unit,
    private val reportError: () -> Unit,
) {
    private val mainThread = Handler(Looper.getMainLooper())

    @JavascriptInterface
    fun onToken(token: String) {
        if (token.isNotBlank()) mainThread.post { deliverToken(token) }
    }

    @JavascriptInterface
    fun onNotRequired() {
        mainThread.post { reportNotRequired() }
    }

    @JavascriptInterface
    fun onError() {
        mainThread.post { reportError() }
    }
}

private class RecaptchaWebViewClient(
    private val pageUrl: String,
    private val onLoaded: () -> Unit,
    private val onFailed: () -> Unit,
) : WebViewClient() {

    override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean {
        if (!request.isForMainFrame || request.url.toString() == pageUrl) return false
        // Google's "Privacy" and "Terms" links would replace the check in this
        // WebView; they open in the browser instead.
        runCatching { view.context.startActivity(Intent(Intent.ACTION_VIEW, request.url)) }
        return true
    }

    override fun onPageFinished(view: WebView, url: String) = onLoaded()

    override fun onReceivedError(
        view: WebView,
        request: WebResourceRequest,
        error: WebResourceError,
    ) {
        if (request.isForMainFrame) onFailed()
    }

    override fun onReceivedHttpError(
        view: WebView,
        request: WebResourceRequest,
        errorResponse: WebResourceResponse,
    ) {
        if (request.isForMainFrame) onFailed()
    }
}
