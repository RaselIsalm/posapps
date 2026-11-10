import java.util.Properties
import org.jetbrains.kotlin.gradle.dsl.JvmTarget

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "bd.onetech.pos"
    compileSdk = 36
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        applicationId = "bd.onetech.pos"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        // Flutter 3.38.1 sets API 24 as its minimum supported Android version.
        minSdk = 24
        targetSdk = 36
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    signingConfigs {
        create("release") {
            val signingPropertiesFile = rootProject.file("key.properties")
            val signingProperties = Properties().apply {
                if (signingPropertiesFile.exists()) {
                    signingPropertiesFile.inputStream().use { load(it) }
                }
            }
            storeFile = rootProject.file(signingProperties.getProperty("storeFile", "missing-upload-key.jks"))
            storePassword = signingProperties.getProperty("storePassword", "missing-upload-key")
            keyAlias = signingProperties.getProperty("keyAlias", "missing-upload-key")
            keyPassword = signingProperties.getProperty("keyPassword", "missing-upload-key")
        }
    }

    buildTypes {
        release {
            signingConfig = signingConfigs.getByName("release")
        }
    }
}

tasks.register("validateReleaseSigning") {
    doLast {
        val propertiesFile = rootProject.file("key.properties")
        val properties = Properties().apply {
            if (propertiesFile.exists()) propertiesFile.inputStream().use { load(it) }
        }
        val storePath = properties.getProperty("storeFile")
        if (storePath.isNullOrBlank() || !rootProject.file(storePath).exists()) {
            throw GradleException("Configure a private upload keystore in android/key.properties before building a release.")
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget.set(JvmTarget.JVM_17)
    }
}

tasks.matching { it.name == "preReleaseBuild" }.configureEach {
    dependsOn("validateReleaseSigning")
}

flutter {
    source = "../.."
}
