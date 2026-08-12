package com.cctn.app

import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.OnBackPressedCallback
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.splashscreen.SplashScreen.Companion.installSplashScreen
import androidx.webkit.WebSettingsCompat
import androidx.webkit.WebViewFeature
import com.cctn.app.databinding.ActivityMainBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null

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

        // Load the production website
        webView.loadUrl(SITE_URL)
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
    }
}
