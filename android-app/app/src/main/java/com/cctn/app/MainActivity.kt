package com.cctn.app

import android.app.Activity
import android.content.Intent
import android.graphics.Bitmap
import android.net.Uri
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.annotation.ColorRes
import androidx.annotation.DrawableRes
import androidx.annotation.StringRes
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.webkit.WebSettingsCompat
import androidx.webkit.WebViewFeature
import com.cctn.app.databinding.ActivityMainBinding
import com.cctn.app.network.NetworkMonitor
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class MainActivity : AppCompatActivity() {

    @Inject
    lateinit var networkMonitor: NetworkMonitor

    private lateinit var binding: ActivityMainBinding
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null

    /** True while the current page failed to load, so it is worth retrying. */
    private var pageLoadFailed = false

    /** True once the user has actually seen the offline state this session. */
    private var sawOffline = false

    private val mainHandler = Handler(Looper.getMainLooper())
    private val hideBannerRunnable = Runnable { hideBanner() }

    private val fileUploadActivityResultLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (fileUploadCallback != null) {
            val results = if (result.resultCode == Activity.RESULT_OK) {
                val dataString = result.data?.dataString
                val clipData = result.data?.clipData
                if (clipData != null) {
                    val count = clipData.itemCount
                    val uris = Array(count) { i -> clipData.getItemAt(i).uri }
                    uris
                } else if (dataString != null) {
                    arrayOf(Uri.parse(dataString))
                } else {
                    null
                }
            } else {
                null
            }
            fileUploadCallback?.onReceiveValue(results)
            fileUploadCallback = null
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        installSplashScreen()
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupWebView()
        setupBackPressed()
        observeConnectivity()
    }

    private fun setupWebView() {
        val webView = binding.webView
        val swipeRefreshLayout = binding.swipeRefreshLayout
        val progressBar = binding.progressBar

        // WebView Settings
        val settings = webView.settings
        settings.javaScriptEnabled = true
        settings.domStorageEnabled = true
        settings.databaseEnabled = true
        settings.useWideViewPort = true
        settings.loadWithOverviewMode = true
        // Fixed layout like a native app: no pinch-zoom, no system font scaling of the UI
        settings.setSupportZoom(false)
        settings.builtInZoomControls = false
        settings.textZoom = 100
        // The site is HTTPS-only: never pull a sub-resource over plain HTTP
        settings.mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW

        // Google Safe Browsing, checked through the compat layer so it also
        // covers devices whose WebView predates the framework setting. A flagged
        // page shows Google's warning screen instead of loading.
        if (WebViewFeature.isFeatureSupported(WebViewFeature.SAFE_BROWSING_ENABLE)) {
            WebSettingsCompat.setSafeBrowsingEnabled(settings, true)
        }
        settings.cacheMode = WebSettings.LOAD_DEFAULT
        settings.mediaPlaybackRequiresUserGesture = false

        // Enable Cookies
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, true)

        // Custom User Agent to identify app traffic (helpful for server-side checks)
        val defaultUserAgent = settings.userAgentString
        settings.userAgentString = "$defaultUserAgent CCTN-Android-App"

        // WebChromeClient (For loading progress & file uploads)
        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                if (newProgress == 100) {
                    progressBar.visibility = View.GONE
                    swipeRefreshLayout.isRefreshing = false
                } else {
                    progressBar.visibility = View.VISIBLE
                    progressBar.progress = newProgress
                }
            }

            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                fileUploadCallback?.onReceiveValue(null)
                fileUploadCallback = filePathCallback

                val intent = fileChooserParams?.createIntent() ?: Intent(Intent.ACTION_GET_CONTENT).apply {
                    type = "*/*"
                    addCategory(Intent.CATEGORY_OPENABLE)
                }
                try {
                    fileUploadActivityResultLauncher.launch(intent)
                } catch (e: Exception) {
                    fileUploadCallback = null
                    return false
                }
                return true
            }
        }

        // WebViewClient (For intercepting navigation and handling external links)
        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                pageLoadFailed = false
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                swipeRefreshLayout.isRefreshing = false
                // Only reveal the page once it actually arrived, otherwise the
                // offline state stays up over the WebView's own error page.
                if (!pageLoadFailed) binding.offlineView.visibility = View.GONE
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                // A failed image or script should not replace a readable page:
                // only a failed main document counts as the page being down.
                if (request?.isForMainFrame != true) return
                pageLoadFailed = true
                showOfflineScreen()
            }

            @Deprecated("Deprecated in Java")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                return handleUrlOverride(url)
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString()
                return handleUrlOverride(url)
            }

            private fun handleUrlOverride(url: String?): Boolean {
                if (url == null) return false

                // Customer-only app: never navigate into staff/admin areas
                if (url.contains("/admin")) {
                    return true
                }

                // Our own pages stay inside the WebView, and always over HTTPS:
                // an http:// link is re-loaded on the secure scheme instead of
                // failing against the cleartext block in the network config.
                if (url.contains(SITE_HOST)) {
                    if (url.startsWith("http://")) {
                        binding.webView.loadUrl("https://" + url.removePrefix("http://"))
                        return true
                    }
                    return false
                }

                // Local development servers keep their plain-HTTP scheme
                if (url.contains("localhost") || url.contains("10.0.2.2")) {
                    return false
                }

                // Intent for external protocols (tel, mailto, whatsapp, maps, etc.)
                if (url.startsWith("tel:") || url.startsWith("mailto:") || url.startsWith("whatsapp:") || url.startsWith("geo:")) {
                    try {
                        val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                        startActivity(intent)
                        return true
                    } catch (e: Exception) {
                        return false
                    }
                }

                // Otherwise, open external links in browser
                try {
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                    startActivity(intent)
                    return true
                } catch (e: Exception) {
                    return false
                }
            }
        }

        // Pull-to-refresh setup
        swipeRefreshLayout.setOnRefreshListener {
            webView.reload()
        }

        binding.retryButton.setOnClickListener {
            if (networkMonitor.isCurrentlyOnline()) {
                retryLoad()
            } else {
                // Nothing to connect to yet: say so rather than flashing the
                // WebView and dropping straight back to this screen.
                Toast.makeText(this, R.string.offline_still_offline, Toast.LENGTH_SHORT).show()
            }
        }

        // Load the production website
        webView.loadUrl(SITE_URL)
    }

    // ── Connectivity ───────────────────────────────────────────────────

    /**
     * Watches the connection for as long as the activity is on screen. The
     * collection stops in onStop and resumes in onStart, so a backgrounded app
     * is not holding a network callback open.
     */
    private fun observeConnectivity() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                networkMonitor.isOnline.collect(::renderConnectionState)
            }
        }
    }

    private fun renderConnectionState(online: Boolean) {
        if (!online) {
            sawOffline = true
            showBanner(
                background = R.color.connection_offline,
                icon = R.drawable.ic_cloud_off,
                message = R.string.offline_message,
                autoHide = false
            )
            // Nothing loaded to fall back on: replace the WebView's error page.
            if (pageLoadFailed) showOfflineScreen()
            return
        }

        if (sawOffline) {
            sawOffline = false
            showBanner(
                background = R.color.connection_online,
                icon = R.drawable.ic_check_circle,
                message = R.string.online_message,
                autoHide = true
            )
            // A page that died while offline comes back on its own. One that
            // loaded fine is left alone so the user does not lose their place.
            if (pageLoadFailed) retryLoad()
        } else {
            // Online from the start: no banner, nothing to announce.
            hideBanner()
        }
    }

    private fun showBanner(
        @ColorRes background: Int,
        @DrawableRes icon: Int,
        @StringRes message: Int,
        autoHide: Boolean
    ) {
        val banner = binding.connectionBanner
        mainHandler.removeCallbacks(hideBannerRunnable)

        banner.setBackgroundColor(ContextCompat.getColor(this, background))
        binding.bannerIcon.setImageResource(icon)
        binding.bannerText.setText(message)

        if (banner.visibility != View.VISIBLE) {
            banner.alpha = 0f
            banner.visibility = View.VISIBLE
            banner.animate().alpha(1f).setDuration(BANNER_FADE_MS).start()
        }

        if (autoHide) mainHandler.postDelayed(hideBannerRunnable, ONLINE_BANNER_MS)
    }

    private fun hideBanner() {
        val banner = binding.connectionBanner
        mainHandler.removeCallbacks(hideBannerRunnable)
        if (banner.visibility != View.VISIBLE) return
        banner.animate()
            .alpha(0f)
            .setDuration(BANNER_FADE_MS)
            .withEndAction { banner.visibility = View.GONE }
            .start()
    }

    private fun showOfflineScreen() {
        binding.swipeRefreshLayout.isRefreshing = false
        binding.progressBar.visibility = View.GONE
        binding.offlineView.visibility = View.VISIBLE
    }

    private fun retryLoad() {
        pageLoadFailed = false
        binding.offlineView.visibility = View.GONE
        val current = binding.webView.url
        if (current.isNullOrEmpty() || current == "about:blank") {
            binding.webView.loadUrl(SITE_URL)
        } else {
            binding.webView.reload()
        }
    }

    override fun onDestroy() {
        mainHandler.removeCallbacks(hideBannerRunnable)
        super.onDestroy()
    }

    private fun setupBackPressed() {
        onBackPressedDispatcher.addCallback(this, object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                val webView = binding.webView
                if (webView.canGoBack()) {
                    webView.goBack()
                } else {
                    isEnabled = false
                    onBackPressedDispatcher.onBackPressed()
                }
            }
        })
    }

    private companion object {
        const val SITE_HOST = "cctn-two.vercel.app"
        const val SITE_URL = "https://$SITE_HOST"

        /** How long the green "Online" confirmation stays up before fading. */
        const val ONLINE_BANNER_MS = 2_000L
        const val BANNER_FADE_MS = 200L
    }
}
