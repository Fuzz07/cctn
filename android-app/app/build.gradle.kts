import java.util.Properties

plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
    id("org.jetbrains.kotlin.plugin.serialization")
    id("com.google.dagger.hilt.android")
    id("com.google.devtools.ksp")
}

// Release signing is read from keystore.properties, which is deliberately not
// in version control. Without it a release build is still produced, just
// unsigned, so CI and a fresh clone never fail on a missing secret.
val keystorePropertiesFile = rootProject.file("keystore.properties")
val keystoreProperties = Properties().apply {
    if (keystorePropertiesFile.exists()) {
        keystorePropertiesFile.inputStream().use { load(it) }
    }
}
val hasSigningConfig = keystoreProperties.getProperty("storeFile") != null

val localPropertiesFile = rootProject.file("local.properties")
val localProperties = Properties().apply {
    if (localPropertiesFile.exists()) {
        localPropertiesFile.inputStream().use { load(it) }
    }
}
val googleWebClientId = (project.findProperty("googleWebClientId") as String?)
    ?: localProperties.getProperty("googleWebClientId")
    ?: ""

val productionApiBaseUrl = "https://bctibantayan.com/api/v1/"

// Debug builds can be pointed elsewhere without editing this file, e.g.
//   ./gradlew assembleDebug -PapiBaseUrl=http://10.0.2.2:8000/api/v1/
// Release is always the production API; there is no property to override it.
val debugApiBaseUrl = (project.findProperty("apiBaseUrl") as String?) ?: productionApiBaseUrl

// The website behind that API. The app is customer-only and has no screen for
// password recovery — there is no endpoint for it — so the one link that leaves
// the app hands that page to a browser, on whichever host the build points at.
fun webBaseUrlOf(apiBaseUrl: String): String =
    apiBaseUrl.removeSuffix("/").removeSuffix("/api/v1") + "/"

android {
    namespace = "com.cctn.app"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.cctn.app"
        minSdk = 26
        targetSdk = 34
        versionCode = 10
        versionName = "2.0.8"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"
        resourceConfigurations += setOf("en")
    }

    signingConfigs {
        if (hasSigningConfig) {
            create("release") {
                storeFile = rootProject.file(keystoreProperties.getProperty("storeFile"))
                storePassword = keystoreProperties.getProperty("storePassword")
                keyAlias = keystoreProperties.getProperty("keyAlias")
                keyPassword = keystoreProperties.getProperty("keyPassword")
            }
        }
    }

    buildTypes {
        debug {
            applicationIdSuffix = ".debug"
            versionNameSuffix = "-debug"
            isMinifyEnabled = false
            buildConfigField("String", "API_BASE_URL", "\"$debugApiBaseUrl\"")
            buildConfigField("String", "WEB_BASE_URL", "\"${webBaseUrlOf(debugApiBaseUrl)}\"")
            buildConfigField("String", "GOOGLE_WEB_CLIENT_ID", "\"$googleWebClientId\"")
        }
        release {
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
            buildConfigField("String", "API_BASE_URL", "\"$productionApiBaseUrl\"")
            buildConfigField("String", "WEB_BASE_URL", "\"${webBaseUrlOf(productionApiBaseUrl)}\"")
            buildConfigField("String", "GOOGLE_WEB_CLIENT_ID", "\"$googleWebClientId\"")
            if (hasSigningConfig) {
                signingConfig = signingConfigs.getByName("release")
            }
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions {
        jvmTarget = "17"
    }
    buildFeatures {
        compose = true
        buildConfig = true
    }
    composeOptions {
        kotlinCompilerExtensionVersion = "1.5.8"
    }
    packaging {
        resources {
            excludes += "/META-INF/{AL2.0,LGPL2.1}"
        }
    }
    lint {
        warningsAsErrors = false
        abortOnError = true
        disable += setOf("GradleDependency", "ObsoleteLintCustomCheck")
    }
}

dependencies {
    // AndroidX core
    implementation("androidx.core:core-ktx:1.13.1")
    implementation("androidx.appcompat:appcompat:1.6.1")
    implementation("androidx.activity:activity-compose:1.9.0")
    implementation("androidx.core:core-splashscreen:1.0.1")

    // Compose
    implementation(platform("androidx.compose:compose-bom:2024.06.00"))
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.ui:ui-graphics")
    implementation("androidx.compose.ui:ui-tooling-preview")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.material:material-icons-extended")
    debugImplementation("androidx.compose.ui:ui-tooling")

    // Lifecycle / ViewModel
    implementation("androidx.lifecycle:lifecycle-runtime-ktx:2.8.3")
    implementation("androidx.lifecycle:lifecycle-runtime-compose:2.8.3")
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.8.3")

    // Navigation
    implementation("androidx.navigation:navigation-compose:2.7.7")

    // Hilt
    implementation("com.google.dagger:hilt-android:2.51")
    ksp("com.google.dagger:hilt-android-compiler:2.51")
    implementation("androidx.hilt:hilt-navigation-compose:1.2.0")

    // Networking
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")
    implementation("org.jetbrains.kotlinx:kotlinx-serialization-json:1.6.3")
    implementation("com.jakewharton.retrofit:retrofit2-kotlinx-serialization-converter:1.0.0")

    // Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3")

    // Encrypted token storage
    implementation("androidx.security:security-crypto:1.1.0-alpha06")

    // Images
    implementation("io.coil-kt:coil-compose:2.6.0")
    implementation("com.google.android.gms:play-services-auth:20.7.0")

    // Testing
    testImplementation("junit:junit:4.13.2")
    testImplementation("org.jetbrains.kotlinx:kotlinx-coroutines-test:1.7.3")
    androidTestImplementation("androidx.test.ext:junit:1.1.5")
    androidTestImplementation(platform("androidx.compose:compose-bom:2024.06.00"))
    androidTestImplementation("androidx.compose.ui:ui-test-junit4")
    debugImplementation("androidx.compose.ui:ui-test-manifest")
}
