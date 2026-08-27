# ─── Kotlin / kotlinx.serialization ──────────────────────────────────────────
# The serializer for a @Serializable class is found reflectively through the
# synthetic Companion.serializer() method, so both must survive shrinking.
-keepattributes *Annotation*, InnerClasses, Signature, RuntimeVisible*Annotations
-dontnote kotlinx.serialization.**

-if @kotlinx.serialization.Serializable class **
-keepclassmembers class <1> {
    static <1>$Companion Companion;
    static **$* *;
}
-keepclassmembers class **$* implements kotlinx.serialization.internal.GeneratedSerializer {
    *** descriptor;
}
-keepclasseswithmembers class ** {
    kotlinx.serialization.KSerializer serializer(...);
}

# Every wire model lives in this package: keep the classes and their fields so
# the JSON names still line up after obfuscation.
-keep,includedescriptorclasses class com.cctn.app.data.remote.dto.** { *; }

# ─── Retrofit ────────────────────────────────────────────────────────────────
# Retrofit builds its implementations from the generic signatures and the
# annotations on the interface methods.
-keep,allowobfuscation interface com.cctn.app.data.remote.CctnApi
-keepattributes Exceptions
-keepclassmembers,allowshrinking,allowobfuscation interface * {
    @retrofit2.http.* <methods>;
}
-dontwarn retrofit2.**
-dontwarn javax.annotation.**
# R8 full mode: keep the generic signature of Call/Response return types.
-keep,allowobfuscation,allowshrinking class retrofit2.Response
-keep,allowobfuscation,allowshrinking class kotlin.coroutines.Continuation

# ─── OkHttp / Okio ───────────────────────────────────────────────────────────
-dontwarn okhttp3.internal.platform.**
-dontwarn org.conscrypt.**
-dontwarn org.bouncycastle.**
-dontwarn org.openjsse.**
-dontwarn okio.**

# ─── Tink, used by EncryptedSharedPreferences ────────────────────────────────
-keep class com.google.crypto.tink.** { *; }
-dontwarn com.google.crypto.tink.**
-dontwarn com.google.errorprone.annotations.**

# ─── Coil ────────────────────────────────────────────────────────────────────
-dontwarn coil.**

# ─── Google Play Services / Auth ─────────────────────────────────────────────
-keep class com.google.android.gms.auth.api.signin.** { *; }
-keep class com.google.android.gms.common.** { *; }
-keep class com.google.android.gms.tasks.** { *; }
-dontwarn com.google.android.gms.**

# ─── Crash reports stay readable ─────────────────────────────────────────────
-keepattributes SourceFile,LineNumberTable
-renamesourcefileattribute SourceFile
