import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';

const _brandColor = Color(0xFF308E87);
const _backgroundColor = Color(0xFFF7FAFC);
const _launchBackgroundColor = Color(0xFF1B6F65);
const _launchAnimationDuration = Duration(seconds: 4);
const _startupSplashFadeDuration = Duration(milliseconds: 280);
const _launchSystemUiStyle = SystemUiOverlayStyle(
  statusBarColor: _launchBackgroundColor,
  statusBarIconBrightness: Brightness.light,
  systemNavigationBarColor: _launchBackgroundColor,
  systemNavigationBarIconBrightness: Brightness.light,
);
const _appSystemUiStyle = SystemUiOverlayStyle(
  statusBarColor: Colors.white,
  statusBarIconBrightness: Brightness.dark,
  systemNavigationBarColor: Colors.white,
  systemNavigationBarIconBrightness: Brightness.dark,
);
const _baseUrl = String.fromEnvironment(
  'BASE_URL',
  defaultValue: kDebugMode ? 'http://10.0.2.2:8000' : 'https://pos.onetech.bd',
);
const _downloadChannel = MethodChannel('bd.onetech.pos/download');

Uri get _configuredBaseUri {
  final uri = Uri.tryParse(_baseUrl);
  if (uri == null || !uri.hasAuthority || uri.host.isEmpty) {
    throw StateError('BASE_URL must be a complete HTTP(S) URL.');
  }

  if (uri.scheme != 'https' && !kDebugMode) {
    throw StateError('HTTP is only available in debug builds.');
  }

  if (kReleaseMode && uri.host.toLowerCase() != 'pos.onetech.bd') {
    throw StateError('Release builds must use https://pos.onetech.bd.');
  }

  return uri;
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final baseUri = _configuredBaseUri;

  if (!kDebugMode && baseUri.scheme != 'https') {
    throw StateError('Non-debug builds must use HTTPS.');
  }

  SystemChrome.setSystemUIOverlayStyle(_launchSystemUiStyle);

  runApp(const OnePosApp());
}

class OnePosApp extends StatelessWidget {
  const OnePosApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ONEPOS',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: _brandColor),
        scaffoldBackgroundColor: _backgroundColor,
        useMaterial3: true,
      ),
      home: const OnePosWebView(),
    );
  }
}

class OnePosWebView extends StatefulWidget {
  const OnePosWebView({super.key});

  @override
  State<OnePosWebView> createState() => _OnePosWebViewState();
}

class _OnePosWebViewState extends State<OnePosWebView>
    with SingleTickerProviderStateMixin {
  final Uri _baseUri = _configuredBaseUri;
  late final AnimationController _launchAnimationController;
  InAppWebViewController? _controller;
  String? _mainFrameError;
  DateTime? _lastBackPress;
  bool _initialPageReady = false;
  bool _launchAnimationFinished = false;
  bool _startupSplashVisible = true;
  bool _startupSplashMounted = true;

  Uri get _loginUri => _baseUri.resolve('/login');

  @override
  void initState() {
    super.initState();
    _launchAnimationController = AnimationController(
      vsync: this,
      duration: _launchAnimationDuration,
    );
    // Show the first letter on the very first Flutter frame instead of a blank
    // color frame before the word reveal starts.
    _launchAnimationController.value = 0.12;
    _launchAnimationController.addStatusListener(_handleLaunchAnimationStatus);
    _launchAnimationController.forward();
  }

  void _handleLaunchAnimationStatus(AnimationStatus status) {
    if (status != AnimationStatus.completed) return;
    _launchAnimationFinished = true;
    _hideStartupSplashWhenReady();
  }

  void _markInitialPageReady() {
    _initialPageReady = true;
    _hideStartupSplashWhenReady();
  }

  void _hideStartupSplashWhenReady() {
    if (!mounted ||
        !_startupSplashVisible ||
        !_initialPageReady ||
        !_launchAnimationFinished) {
      return;
    }
    setState(() => _startupSplashVisible = false);
  }

  void _finishStartupSplashFade() {
    if (!mounted || _startupSplashVisible || !_startupSplashMounted) return;
    setState(() => _startupSplashMounted = false);
    SystemChrome.setSystemUIOverlayStyle(_appSystemUiStyle);
  }

  bool _isInternal(Uri uri) {
    if (uri.scheme != 'https' && !(kDebugMode && uri.scheme == 'http')) {
      return false;
    }

    return uri.host.toLowerCase() == _baseUri.host.toLowerCase() &&
        uri.port == _baseUri.port;
  }

  Future<void> _handleBack() async {
    final controller = _controller;
    if (controller != null && await controller.canGoBack()) {
      await controller.goBack();
      return;
    }

    final now = DateTime.now();
    final shouldExit =
        _lastBackPress != null &&
        now.difference(_lastBackPress!) < const Duration(seconds: 2);
    _lastBackPress = now;

    if (shouldExit) {
      await SystemNavigator.pop();
      return;
    }

    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        const SnackBar(
          content: Text('Press back again to close ONEPOS.'),
          duration: Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
        ),
      );
  }

  Future<void> _openExternal(Uri uri) async {
    final scheme = uri.scheme.toLowerCase();
    const allowedSchemes = {
      'https',
      'http',
      'mailto',
      'tel',
      'sms',
      'whatsapp',
      'intent',
    };

    if (!allowedSchemes.contains(scheme)) {
      _showMessage('This link cannot be opened on this device.');
      return;
    }

    try {
      final opened = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!opened) _showMessage('No app is available to open this link.');
    } catch (_) {
      _showMessage('Could not open this link.');
    }
  }

  Future<NavigationActionPolicy> _handleNavigation(
    NavigationAction action,
  ) async {
    final url = action.request.url;
    if (url == null) return NavigationActionPolicy.CANCEL;

    final uri = Uri.tryParse(url.toString());
    if (uri == null) return NavigationActionPolicy.CANCEL;

    if (uri.scheme == 'about' && uri.path == 'blank') {
      return NavigationActionPolicy.ALLOW;
    }

    if (_isInternal(uri)) return NavigationActionPolicy.ALLOW;

    unawaited(_openExternal(uri));
    return NavigationActionPolicy.CANCEL;
  }

  Future<bool> _handleCreateWindow(CreateWindowAction action) async {
    final url = action.request.url;
    if (url != null) {
      final uri = Uri.tryParse(url.toString());
      if (uri != null && !_isInternal(uri)) unawaited(_openExternal(uri));
    }
    return false;
  }

  Future<void> _handleDownload(DownloadStartRequest request) async {
    final uri = Uri.tryParse(request.url.toString());
    if (uri == null || !_isInternal(uri)) {
      _showMessage('Downloads from this address are blocked.');
      return;
    }

    try {
      final cookies = await CookieManager.instance().getCookies(
        url: request.url,
      );
      final cookieHeader = cookies
          .map((cookie) => '${cookie.name}=${cookie.value}')
          .join('; ');
      final headers = <String, String>{};
      if (cookieHeader.isNotEmpty) headers['Cookie'] = cookieHeader;
      if (request.userAgent?.isNotEmpty == true) {
        headers['User-Agent'] = request.userAgent!;
      }

      var fileName = request.suggestedFilename?.trim() ?? '';
      if (fileName.isEmpty) {
        fileName = uri.pathSegments.isEmpty
            ? 'onepos-download'
            : uri.pathSegments.last;
      }
      try {
        fileName = Uri.decodeComponent(fileName);
      } catch (_) {
        // Keep the server-provided name if it is not valid percent-encoding.
      }
      fileName = fileName.replaceAll(RegExp(r'[\\/:*?"<>|\u0000-\u001F]'), '_');
      if (fileName.trim().isEmpty) fileName = 'onepos-download';

      await _downloadChannel.invokeMethod<int>('download', {
        'url': uri.toString(),
        'fileName': fileName,
        'mimeType': request.mimeType,
        'headers': headers,
      });
      _showMessage('Download started. Find it in your Downloads folder.');
    } on PlatformException catch (error) {
      _showMessage(error.message ?? 'Unable to start the download.');
    } catch (_) {
      _showMessage('Unable to start the download.');
    }
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
      );
  }

  void _retry() {
    setState(() => _mainFrameError = null);
    _controller?.loadUrl(
      urlRequest: URLRequest(url: WebUri(_loginUri.toString())),
    );
  }

  @override
  void dispose() {
    _launchAnimationController.removeStatusListener(
      _handleLaunchAnimationStatus,
    );
    _launchAnimationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope<Object?>(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) unawaited(_handleBack());
      },
      child: Scaffold(
        backgroundColor: _startupSplashMounted
            ? _launchBackgroundColor
            : _backgroundColor,
        body: SafeArea(
          child: Stack(
            fit: StackFit.expand,
            children: [
              InAppWebView(
                initialUrlRequest: URLRequest(
                  url: WebUri(_loginUri.toString()),
                ),
                initialSettings: InAppWebViewSettings(
                  javaScriptEnabled: true,
                  javaScriptCanOpenWindowsAutomatically: false,
                  domStorageEnabled: true,
                  databaseEnabled: true,
                  supportZoom: false,
                  supportMultipleWindows: true,
                  useOnDownloadStart: true,
                  safeBrowsingEnabled: true,
                  mixedContentMode: MixedContentMode.MIXED_CONTENT_NEVER_ALLOW,
                  allowFileAccess: false,
                  allowContentAccess: true,
                  thirdPartyCookiesEnabled: false,
                  verticalScrollBarEnabled: false,
                  horizontalScrollBarEnabled: false,
                  applicationNameForUserAgent: 'ONEPOSApp/1.0',
                  isInspectable: kDebugMode,
                ),
                onWebViewCreated: (controller) => _controller = controller,
                onLoadStop: (controller, url) {
                  if (!mounted) return;
                  setState(() => _mainFrameError = null);
                  _markInitialPageReady();
                },
                onReceivedError: (controller, request, error) {
                  if (request.isForMainFrame != true || !mounted) return;
                  setState(() {
                    _mainFrameError = error.description;
                  });
                  _markInitialPageReady();
                },
                shouldOverrideUrlLoading: (controller, action) =>
                    _handleNavigation(action),
                onCreateWindow: (controller, action) =>
                    _handleCreateWindow(action),
                onDownloadStartRequest: (controller, request) =>
                    unawaited(_handleDownload(request)),
                onPrintRequest: (controller, url, printJob) async => false,
              ),
              if (_mainFrameError != null)
                Positioned.fill(child: _OfflinePanel(onRetry: _retry)),
              if (_startupSplashMounted)
                Positioned.fill(
                  child: IgnorePointer(
                    child: AnimatedOpacity(
                      opacity: _startupSplashVisible ? 1 : 0,
                      duration: _startupSplashFadeDuration,
                      curve: Curves.easeOut,
                      onEnd: _finishStartupSplashFade,
                      child: _LaunchSplash(
                        controller: _launchAnimationController,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _LaunchSplash extends StatelessWidget {
  const _LaunchSplash({required this.controller});

  final AnimationController controller;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: _launchBackgroundColor,
      child: Center(
        child: AnimatedBuilder(
          animation: controller,
          builder: (context, child) {
            const word = 'ONEPOS';
            final progress = controller.value;

            return Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: List.generate(word.length, (index) {
                    final letterProgress = (progress * word.length - index)
                        .clamp(0.0, 1.0);
                    final easedProgress = Curves.easeOutCubic.transform(
                      letterProgress,
                    );

                    return Transform.translate(
                      offset: Offset(0, (1 - easedProgress) * 14),
                      child: Opacity(
                        opacity: easedProgress,
                        child: Text(
                          word[index],
                          style: const TextStyle(
                            color: Color(0xFFF4FFFC),
                            fontSize: 68,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -2,
                            height: 1,
                          ),
                        ),
                      ),
                    );
                  }),
                ),
                const SizedBox(height: 18),
                SizedBox(
                  width: 260,
                  height: 8,
                  child: Align(
                    alignment: Alignment.center,
                    child: Container(
                      width: 260 * Curves.easeOutCubic.transform(progress),
                      height: 8,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _OfflinePanel extends StatelessWidget {
  const _OfflinePanel({required this.onRetry});

  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: _backgroundColor,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Image.asset(
                'assets/images/onepos-mark.png',
                width: 76,
                height: 76,
              ),
              const SizedBox(height: 24),
              const Icon(Icons.wifi_off_rounded, size: 32, color: _brandColor),
              const SizedBox(height: 12),
              const Text(
                'You are offline',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 8),
              const Text(
                'Check your internet connection and try again.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF667085), height: 1.4),
              ),
              const SizedBox(height: 22),
              FilledButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Try again'),
                style: FilledButton.styleFrom(
                  backgroundColor: _brandColor,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
