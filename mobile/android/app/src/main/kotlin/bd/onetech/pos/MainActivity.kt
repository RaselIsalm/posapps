package bd.onetech.pos

import android.app.DownloadManager
import android.net.Uri
import android.os.Environment
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.android.FlutterActivity
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "bd.onetech.pos/download")
            .setMethodCallHandler { call, result ->
                if (call.method != "download") {
                    result.notImplemented()
                    return@setMethodCallHandler
                }

                try {
                    val url = call.argument<String>("url")
                        ?: throw IllegalArgumentException("Missing download URL")
                    val fileName = (call.argument<String>("fileName") ?: "onepos-download")
                        .replace(Regex("[\\\\/:*?\"<>|]"), "_")
                        .take(160)
                        .ifBlank { "onepos-download" }
                    val mimeType = call.argument<String>("mimeType")
                    val headers = call.argument<Map<String, String>>("headers") ?: emptyMap()

                    val request = DownloadManager.Request(Uri.parse(url))
                        .setTitle(fileName)
                        .setDescription("Downloading from ONEPOS")
                        .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                        .setAllowedOverMetered(true)
                        .setAllowedOverRoaming(false)
                        .setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName)

                    if (!mimeType.isNullOrBlank()) request.setMimeType(mimeType)
                    headers.forEach { (name, value) -> request.addRequestHeader(name, value) }

                    val manager = getSystemService(DOWNLOAD_SERVICE) as DownloadManager
                    result.success(manager.enqueue(request))
                } catch (error: Exception) {
                    result.error("DOWNLOAD_FAILED", error.message ?: "Unable to start download", null)
                }
            }
    }
}
