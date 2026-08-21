package com.cctn.app.network

import android.content.Context
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkCapabilities
import android.net.NetworkRequest
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.channels.Channel
import kotlinx.coroutines.channels.awaitClose
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.buffer
import kotlinx.coroutines.flow.callbackFlow
import kotlinx.coroutines.flow.distinctUntilChanged
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Reports whether the device can actually reach the internet.
 *
 * A network only counts as online once the system has validated it, so Wi-Fi
 * that is associated but has no working connection — a dead router, a captive
 * portal that has not been signed into — reads as offline, the same as Wi-Fi
 * and mobile data both being switched off.
 */
@Singleton
class NetworkMonitor @Inject constructor(
    @ApplicationContext context: Context
) {
    private val connectivityManager = context.getSystemService(ConnectivityManager::class.java)

    /** Emits `true` while the internet is reachable and `false` while it is not. */
    val isOnline: Flow<Boolean> = callbackFlow {
        val manager = connectivityManager
        if (manager == null) {
            // No connectivity service to listen to: report offline and stay put.
            trySend(false)
            awaitClose { }
            return@callbackFlow
        }

        // ConnectivityManager delivers these callbacks serially on its own
        // thread, so a plain set is enough to track which networks are usable.
        val usable = mutableSetOf<Network>()

        val callback = object : ConnectivityManager.NetworkCallback() {
            override fun onCapabilitiesChanged(network: Network, caps: NetworkCapabilities) {
                if (caps.isUsable()) usable += network else usable -= network
                trySend(usable.isNotEmpty())
            }

            override fun onLost(network: Network) {
                usable -= network
                trySend(usable.isNotEmpty())
            }

            override fun onUnavailable() {
                usable.clear()
                trySend(false)
            }
        }

        val request = NetworkRequest.Builder()
            .addCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
            .build()
        manager.registerNetworkCallback(request, callback)

        // Seed the current state so the UI is right before the first callback lands.
        trySend(isCurrentlyOnline())

        awaitClose {
            // Unregistering twice throws, so swallow the race with a torn-down activity.
            runCatching { manager.unregisterNetworkCallback(callback) }
        }
    }.buffer(Channel.CONFLATED).distinctUntilChanged()

    /** One-off check, for decisions that cannot wait for the next emission. */
    fun isCurrentlyOnline(): Boolean {
        val manager = connectivityManager ?: return false
        val caps = manager.getNetworkCapabilities(manager.activeNetwork) ?: return false
        return caps.isUsable()
    }

    private fun NetworkCapabilities.isUsable(): Boolean =
        hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET) &&
            hasCapability(NetworkCapabilities.NET_CAPABILITY_VALIDATED)
}
