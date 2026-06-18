import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:webview_flutter/webview_flutter.dart';

class LifestyleScreen extends StatefulWidget {
  const LifestyleScreen({super.key});

  @override
  State<LifestyleScreen> createState() => _LifestyleScreenState();
}

class _LifestyleScreenState extends State<LifestyleScreen> {
  WebViewController? _controller;
  bool _isLoading = true;
  bool _showSuggestion = false;
  String _assessmentHtml = '';
  String _suggestionHtml = '';

  @override
  void initState() {
    super.initState();
    _initWebView();
  }

  Future<void> _initWebView() async {
    _assessmentHtml = await rootBundle.loadString(
      'assets/lifestyle/LifestyleAssessment.html',
    );
    _suggestionHtml = await rootBundle.loadString(
      'assets/lifestyle/LifeStyleSuggestionPage.html',
    );

    final controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFFFCE4EC))
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (mounted) setState(() => _isLoading = true);
          },
          onPageFinished: (_) {
            if (mounted) setState(() => _isLoading = false);
          },
          onNavigationRequest: (req) {
            final url = req.url;
            if (url.contains('LifeStyleSuggestionPage')) {
              _loadSuggestion(url);
              return NavigationDecision.prevent;
            }
            if (url.contains('LifestyleAssessment')) {
              _backToAssessment();
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..addJavaScriptChannel(
        'FlutterBridge',
        onMessageReceived: (msg) {
          debugPrint('JS: ${msg.message}');
        },
      );

    await controller.loadHtmlString(_assessmentHtml);

    if (mounted) setState(() => _controller = controller);
  }

  Future<void> _loadSuggestion(String url) async {
    String mh = '0', burn = '0';
    try {
      final uri = Uri.parse(url);
      mh = uri.queryParameters['mentalHealth'] ?? '0';
      burn = uri.queryParameters['burnout'] ?? '0';
    } catch (_) {}

    String html = _suggestionHtml;
    final inject =
        '''
<script>
window.addEventListener('DOMContentLoaded', function() {
  var mhEl = document.getElementById('mental_health_score');
  var bEl  = document.getElementById('burnout_level');
  if (mhEl) { mhEl.value = $mh; mhEl.classList.add('autofilled'); }
  if (bEl)  { bEl.value  = $burn; bEl.classList.add('autofilled'); }
  var badge = document.getElementById('autofillBadge');
  if (badge) badge.classList.add('show');
});
</script>''';
    html = html.replaceFirst('</head>', '$inject</head>');

    setState(() => _showSuggestion = true);
    await _controller?.loadHtmlString(html);
  }

  Future<void> _backToAssessment() async {
    setState(() => _showSuggestion = false);
    await _controller?.loadHtmlString(_assessmentHtml);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFCE4EC),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD81B60),
        foregroundColor: Colors.white,
        title: Text(
          _showSuggestion ? 'Saran Gaya Hidup' : 'Asesmen Kesehatan',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: _showSuggestion
              ? _backToAssessment
              : () => Navigator.pop(context),
        ),
      ),
      body: _controller == null
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFD81B60)),
            )
          : Stack(
              children: [
                WebViewWidget(controller: _controller!),
                if (_isLoading)
                  Container(
                    color: const Color(0xFFFCE4EC),
                    child: const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFFD81B60),
                      ),
                    ),
                  ),
              ],
            ),
    );
  }
}
