package com.cctn.app.data.local

import android.content.Context
import android.content.SharedPreferences
import android.util.Log
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import com.cctn.app.data.remote.dto.ClientDto
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.serialization.json.Json
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Persists the API token and the last known profile.
 *
 * The store is backed by EncryptedSharedPreferences so the token is not
 * readable from a device image or an adb backup. Keystore initialisation does
 * fail on a small number of devices with a damaged key entry, and a crash on
 * launch is worse than the weaker guarantee, so that case falls back to plain
 * app-private preferences — still inside the app sandbox, and excluded from
 * backup by the manifest.
 */
@Singleton
class SessionStore @Inject constructor(
    @ApplicationContext context: Context,
    private val json: Json,
) {
    private val prefs: SharedPreferences = createPreferences(context)

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        private set(value) = prefs.edit().apply {
            if (value == null) remove(KEY_TOKEN) else putString(KEY_TOKEN, value)
        }.apply()

    fun cachedClient(): ClientDto? {
        val raw = prefs.getString(KEY_CLIENT, null) ?: return null
        return runCatching { json.decodeFromString(ClientDto.serializer(), raw) }.getOrNull()
    }

    fun save(token: String, client: ClientDto) {
        this.token = token
        saveClient(client)
    }

    fun saveClient(client: ClientDto) {
        prefs.edit()
            .putString(KEY_CLIENT, json.encodeToString(ClientDto.serializer(), client))
            .apply()
    }

    fun clear() {
        prefs.edit().remove(KEY_TOKEN).remove(KEY_CLIENT).apply()
    }

    private fun createPreferences(context: Context): SharedPreferences = try {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()

        EncryptedSharedPreferences.create(
            context,
            ENCRYPTED_FILE,
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM,
        )
    } catch (e: Exception) {
        Log.w(TAG, "Encrypted storage unavailable, falling back to private preferences")
        context.getSharedPreferences(FALLBACK_FILE, Context.MODE_PRIVATE)
    }

    private companion object {
        const val TAG = "SessionStore"
        const val ENCRYPTED_FILE = "cctn_session"
        const val FALLBACK_FILE = "cctn_session_plain"
        const val KEY_TOKEN = "api_token"
        const val KEY_CLIENT = "client"
    }
}
