import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:shared_preferences/shared_preferences.dart';
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
  defaultValue: 'https://pos.onetech.bd',
);
const _baseUrlPreferenceKey = 'onepos_base_url';
const _downloadChannel = MethodChannel('bd.onetech.pos/download');

class MobileLoginTicket {
  const MobileLoginTicket({required this.baseUri, required this.value});

  final Uri baseUri;
  final String value;
}

Future<MobileLoginTicket> _requestTicket(
  Uri baseUri, {
  required String path,
  Map<String, String> body = const {},
}) async {
  final client = HttpClient()..connectionTimeout = const Duration(seconds: 12);
  try {
    final request = await client.postUrl(baseUri.resolve(path));
    request.followRedirects = false;
    request.headers.set(HttpHeaders.acceptHeader, 'application/json');
    request.headers.set(HttpHeaders.userAgentHeader, 'ONEPOSApp/1.0');
    request.headers.contentType = ContentType('application', 'json');
    request.write(jsonEncode(body));

    final response = await request.close().timeout(const Duration(seconds: 18));
    final responseBody = await utf8.decoder.bind(response).join();
    final decoded = jsonDecode(responseBody);
    final responseData = decoded is Map<String, dynamic>
        ? decoded
        : <String, dynamic>{};
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = responseData['message'];
      throw FormatException(
        message is String && message.isNotEmpty
            ? message
            : 'Sign-in failed (${response.statusCode}).',
      );
    }

    final data = responseData['data'];
    final ticket = data is Map ? data['ticket'] : null;
    if (ticket is! String || ticket.length != 64) {
      throw const FormatException(
        'The server returned an invalid sign-in ticket.',
      );
    }
    return MobileLoginTicket(baseUri: baseUri, value: ticket);
  } on SocketException {
    throw const FormatException(
      'Could not connect to this server. Check the URL and connection.',
    );
  } on HandshakeException {
    throw const FormatException('The server HTTPS certificate is not trusted.');
  } on TimeoutException {
    throw const FormatException('The server took too long to respond.');
  } on FormatException {
    rethrow;
  } catch (_) {
    throw const FormatException(
      'Could not communicate with this OnePOS server.',
    );
  } finally {
    client.close(force: true);
  }
}

Uri _parseBaseUri(String value) {
  var normalized = value.trim();
  if (!normalized.contains('://')) normalized = 'https://$normalized';
  final uri = Uri.tryParse(normalized);
  if (uri == null ||
      !uri.hasAuthority ||
      uri.host.isEmpty ||
      (uri.scheme != 'http' && uri.scheme != 'https') ||
      uri.userInfo.isNotEmpty ||
      uri.hasQuery ||
      uri.hasFragment) {
    throw const FormatException('Enter a valid OnePOS server URL.');
  }

  if (uri.path.isNotEmpty && uri.path != '/' && uri.path != '/login') {
    throw const FormatException('Enter the server domain or its /login page.');
  }

  if (uri.scheme == 'http' && !kDebugMode) {
    throw const FormatException('Use HTTPS for production servers.');
  }

  if (!kDebugMode &&
      (InternetAddress.tryParse(uri.host) != null || !uri.host.contains('.'))) {
    throw const FormatException(
      'Use a public domain with a valid HTTPS certificate.',
    );
  }

  return uri.replace(path: '', query: null, fragment: null);
}

bool _isPublicAddress(InternetAddress address) {
  if (address.isLoopback || address.isLinkLocal || address.isMulticast) {
    return false;
  }
  final bytes = address.rawAddress;
  if (address.type == InternetAddressType.IPv4) {
    final a = bytes[0];
    final b = bytes[1];
    if (a == 0 || a == 10 || a == 127 || a >= 224) return false;
    if (a == 100 && b >= 64 && b <= 127) return false;
    if (a == 169 && b == 254) return false;
    if (a == 172 && b >= 16 && b <= 31) return false;
    if (a == 192 && (b == 168 || b == 0 || b == 2)) return false;
    if (a == 198 && (b == 18 || b == 19 || b == 51)) return false;
    if (a == 203 && b == 0) return false;
  } else {
    // Publicly routable IPv6 space is currently allocated from 2000::/3.
    // This also rejects mapped IPv4, ULA, link-local, multicast, and loopback.
    if ((bytes[0] & 0xe0) != 0x20) return false;
    if (bytes[0] == 0x20 &&
        bytes[1] == 0x01 &&
        bytes[2] == 0x0d &&
        bytes[3] == 0xb8) {
      return false;
    }
  }
  return true;
}

Future<void> _validateServerAddress(Uri baseUri) async {
  if (!kDebugMode) {
    final addresses = await InternetAddress.lookup(baseUri.host);
    if (addresses.isEmpty ||
        addresses.any((address) => !_isPublicAddress(address))) {
      throw const FormatException(
        'Use a public server domain, not a private network address.',
      );
    }
  }
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  Uri? savedBaseUri;
  final configuredUrl = preferences.getString(_baseUrlPreferenceKey);
  if (configuredUrl != null) {
    try {
      savedBaseUri = _parseBaseUri(configuredUrl);
    } on FormatException {
      await preferences.remove(_baseUrlPreferenceKey);
    }
  }

  SystemChrome.setSystemUIOverlayStyle(_launchSystemUiStyle);

  runApp(OnePosApp(initialCustomBaseUri: savedBaseUri));
}

class OnePosApp extends StatefulWidget {
  const OnePosApp({super.key, required this.initialCustomBaseUri});
  final Uri? initialCustomBaseUri;
  @override
  State<OnePosApp> createState() => _OnePosAppState();
}

class _OnePosAppState extends State<OnePosApp> {
  late Uri _baseUri;
  @override
  void initState() {
    super.initState();
    _baseUri = widget.initialCustomBaseUri ?? Uri.parse(_baseUrl);
  }

  Future<void> _verifyServerCompatibility(Uri baseUri) async {
    final client = HttpClient()..connectionTimeout = const Duration(seconds: 12);
    try {
      final request = await client.getUrl(
        baseUri.resolve('/api/v1/mobile/bootstrap'),
      );
      request.followRedirects = false;
      request.headers.set(HttpHeaders.acceptHeader, 'application/json');
      request.headers.set(HttpHeaders.userAgentHeader, 'ONEPOSApp/1.0');
      final response = await request.close().timeout(
        const Duration(seconds: 18),
      );
      final responseBody = await utf8.decoder.bind(response).join();
      final payload = jsonDecode(responseBody);
      final data = payload is Map<String, dynamic> ? payload['data'] : null;
      if (response.statusCode != 200 ||
          data is! Map ||
          data['application'] != 'ONEPOS' ||
          data['web_login'] != true ||
          data['api_version'] != 1) {
        throw const FormatException(
          'This server is not compatible with the ONEPOS app.',
        );
      }
    } on SocketException {
      throw const FormatException(
        'Could not reach this server. Check its domain and internet access.',
      );
    } on HandshakeException {
      throw const FormatException(
        'The server HTTPS certificate is not trusted.',
      );
    } on TimeoutException {
      throw const FormatException('The server took too long to respond.');
    } on FormatException catch (error) {
      if (error.message.startsWith('This server')) rethrow;
      throw const FormatException(
        'This server returned an invalid compatibility response.',
      );
    } catch (_) {
      throw const FormatException(
        'This server does not support the ONEPOS app.',
      );
    } finally {
      client.close(force: true);
    }
  }

  Future<MobileLoginTicket> _loginUser(
    String baseUrl,
    String identifier,
    String password,
  ) async {
    final baseUri = _parseBaseUri(baseUrl);
    await _validateServerAddress(baseUri);
    await _verifyServerCompatibility(baseUri);
    final ticket = await _requestTicket(
      baseUri,
      path: '/api/v1/mobile/login',
      body: {'identifier': identifier, 'password': password},
    );
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString(_baseUrlPreferenceKey, baseUri.toString());

    return ticket;
  }

  Future<MobileLoginTicket> _loginDemo() async {
    final demoUri = Uri.parse(_baseUrl);
    await _validateServerAddress(demoUri);
    return _requestTicket(demoUri, path: '/api/v1/demo/guest-session');
  }

  Future<void> _changeDomain() async {
    final preferences = await SharedPreferences.getInstance();
    await preferences.remove(_baseUrlPreferenceKey);
    if (mounted) setState(() => _baseUri = Uri.parse(_baseUrl));
  }

  Future<void> _resetToMain() async {
    final preferences = await SharedPreferences.getInstance();
    await preferences.setString(_baseUrlPreferenceKey, _baseUrl);
    if (mounted) setState(() => _baseUri = Uri.parse(_baseUrl));
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'ONEPOS',
    debugShowCheckedModeBanner: false,
    theme: ThemeData(
      colorScheme: ColorScheme.fromSeed(seedColor: _brandColor),
      scaffoldBackgroundColor: _backgroundColor,
      useMaterial3: true,
    ),
    home: OnePosWebView(
      key: ValueKey(_baseUri.origin),
      baseUri: _baseUri,
      onUserLogin: _loginUser,
      onDemoLogin: _loginDemo,
      onChangeDomain: _changeDomain,
      onResetToMain: _resetToMain,
    ),
  );
}

class OnePosWebView extends StatefulWidget {
  const OnePosWebView({
    super.key,
    required this.baseUri,
    required this.onChangeDomain,
    required this.onResetToMain,
    required this.onUserLogin,
    required this.onDemoLogin,
  });
  final Uri baseUri;
  final Future<MobileLoginTicket> Function(String, String, String) onUserLogin;
  final Future<MobileLoginTicket> Function() onDemoLogin;
  final Future<void> Function() onChangeDomain;
  final Future<void> Function() onResetToMain;
  @override
  State<OnePosWebView> createState() => _OnePosWebViewState();
}

class _OnePosWebViewState extends State<OnePosWebView>
    with SingleTickerProviderStateMixin {
  late Uri _baseUri = widget.baseUri;
  late final AnimationController _launchAnimationController;
  InAppWebViewController? _controller;
  String? _mainFrameError;
  DateTime? _lastBackPress;
  bool _initialPageReady = false;
  bool _launchAnimationFinished = false;
  bool _startupSplashVisible = true;
  bool _startupSplashMounted = true;
  MobileLoginTicket? _pendingTicket;
  bool _ticketSubmitted = false;
  bool _authRequestRunning = false;

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

  Future<void> _sendWebLogout() async {
    final controller = _controller;
    if (controller == null) return;
    try {
      await controller.evaluateJavascript(
        source: '''
        (() => {
          const form = document.querySelector('#logout-form, form[action\$="/logout"]');
          if (form) { form.submit(); return true; }
          const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
          if (!csrf) return false;
          fetch('/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}
          });
          return true;
        })()
      ''',
      );
      await Future<void>.delayed(const Duration(milliseconds: 250));
    } catch (_) {
      // Local cookie and storage cleanup below still closes this device's session.
    }
  }

  Future<void> _leaveForDomainSetup() async {
    await _sendWebLogout();
    await _clearOriginData(_baseUri);
    if (!mounted) return;
    await widget.onChangeDomain();
    _navigateToLogin(Uri.parse(_baseUrl));
  }

  Future<void> _resetToMain() async {
    await _sendWebLogout();
    await _clearOriginData(_baseUri);
    final mainUri = Uri.parse(_baseUrl);
    await _clearOriginData(mainUri);
    if (!mounted) return;
    await widget.onResetToMain();
    _navigateToLogin(mainUri);
  }

  void _navigateToLogin(Uri baseUri) {
    setState(() {
      _baseUri = baseUri;
      _pendingTicket = null;
      _ticketSubmitted = false;
      _mainFrameError = null;
    });
    _controller?.loadUrl(
      urlRequest: URLRequest(url: WebUri(baseUri.resolve('/login').toString())),
    );
  }

  Future<Map<String, dynamic>> _handleAuthBridge(dynamic raw) async {
    if (_authRequestRunning) {
      return {'ok': false, 'message': 'A sign-in request is already running.'};
    }
    final controller = _controller;
    if (controller == null) {
      return {'ok': false, 'message': 'The login page is not ready yet.'};
    }
    final current = Uri.tryParse((await controller.getUrl())?.toString() ?? '');
    if (current == null || !_isInternal(current) || current.path != '/login') {
      return {
        'ok': false,
        'message': 'Sign-in is allowed only from this server.',
      };
    }
    if (raw is! Map) {
      return {'ok': false, 'message': 'Invalid sign-in request.'};
    }
    _authRequestRunning = true;
    try {
      late final MobileLoginTicket ticket;
      if (raw['mode'] == 'demo') {
        ticket = await widget.onDemoLogin();
      } else if (raw['mode'] == 'user') {
        final baseUrl = raw['baseUrl'];
        final identifier = raw['identifier'];
        final password = raw['password'];
        if (baseUrl is! String || identifier is! String || password is! String) {
          return {
            'ok': false,
            'message': 'Enter your server, username and password.',
          };
        }
        ticket = await widget.onUserLogin(baseUrl, identifier, password);
      } else {
        return {'ok': false, 'message': 'Choose Demo or User sign-in.'};
      }
      unawaited(
        Future<void>.delayed(const Duration(milliseconds: 120), () {
          if (mounted) _beginTicketLogin(ticket);
        }),
      );
      return {'ok': true};
    } on FormatException catch (error) {
      return {'ok': false, 'message': error.message};
    } catch (_) {
      return {'ok': false, 'message': 'Could not sign in. Please try again.'};
    } finally {
      _authRequestRunning = false;
    }
  }

  void _beginTicketLogin(MobileLoginTicket ticket) {
    if (!mounted) return;
    setState(() {
      _baseUri = ticket.baseUri;
      _pendingTicket = ticket;
      _ticketSubmitted = false;
      _mainFrameError = null;
    });
    _controller?.loadUrl(
      urlRequest: URLRequest(
        url: WebUri(ticket.baseUri.resolve('/login').toString()),
      ),
    );
  }

  Future<void> _handleAppMenu(String action) async {
    switch (action) {
      case 'logout':
        await _sendWebLogout();
        await _clearOriginData(_baseUri);
        _navigateToLogin(_baseUri);
        break;
      case 'change-domain':
        await _leaveForDomainSetup();
        break;
      case 'reset':
        await _resetToMain();
        break;
    }
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
                onWebViewCreated: (controller) {
                  _controller = controller;
                  controller.addJavaScriptHandler(
                    handlerName: 'oneposAuth',
                    callback: (arguments) => _handleAuthBridge(
                      arguments.isEmpty ? null : arguments.first,
                    ),
                  );
                },
                onLoadStop: (controller, url) {
                  if (!mounted) return;
                  setState(() => _mainFrameError = null);
                  final ticket = _pendingTicket;
                  final loadedUri = Uri.tryParse(url?.toString() ?? '');
                  if (ticket != null &&
                      !_ticketSubmitted &&
                      loadedUri != null &&
                      _isInternal(loadedUri) &&
                      loadedUri.path == '/login') {
                    _ticketSubmitted = true;
                    _pendingTicket = null;
                    final body = Uint8List.fromList(
                      utf8.encode(
                        'ticket=${Uri.encodeComponent(ticket.value)}',
                      ),
                    );
                    unawaited(
                      controller.postUrl(
                        url: WebUri(
                          _baseUri
                              .resolve('/mobile/session/exchange')
                              .toString(),
                        ),
                        postData: body,
                      ),
                    );
                  }
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
                Positioned.fill(
                  child: _OfflinePanel(
                    onRetry: _retry,
                    onChangeServer: _leaveForDomainSetup,
                  ),
                ),
              Positioned(
                top: 8,
                right: 8,
                child: SafeArea(
                  child: PopupMenuButton<String>(
                    tooltip: 'ONEPOS settings',
                    icon: const Icon(Icons.settings_outlined),
                    color: Colors.white,
                    onSelected: (action) => unawaited(_handleAppMenu(action)),
                    itemBuilder: (context) => const [
                      PopupMenuItem(value: 'logout', child: Text('Log out')),
                      PopupMenuItem(
                        value: 'change-domain',
                        child: Text('Change domain'),
                      ),
                      PopupMenuItem(
                        value: 'reset',
                        child: Text('Reset to pos.onetech.bd'),
                      ),
                    ],
                  ),
                ),
              ),
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

Future<void> _clearOriginData(Uri origin) async {
  try {
    final webUri = WebUri(origin.toString());
    await CookieManager.instance().deleteCookies(url: webUri);
    await WebStorageManager.instance().deleteOrigin(origin: origin.origin);
  } catch (_) {
    // A missing WebView storage entry is already equivalent to a clean origin.
  }
}

class _OfflinePanel extends StatelessWidget {
  const _OfflinePanel({required this.onRetry, required this.onChangeServer});

  final VoidCallback onRetry;
  final VoidCallback onChangeServer;

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
              TextButton.icon(
                onPressed: onChangeServer,
                icon: const Icon(Icons.dns_outlined),
                label: const Text('Change server address'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
