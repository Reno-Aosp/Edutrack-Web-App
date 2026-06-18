import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:webview_flutter/webview_flutter.dart';

class LifestyleScreen extends StatefulWidget {
  const LifestyleScreen({super.key});

  @override
  State<LifestyleScreen> createState() => _LifestyleScreenState();
}

class _LifestyleScreenState extends State<LifestyleScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFFFCE4EC))
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageFinished: (String url) {
            setState(() {
              _isLoading = false;
            });
          },
        ),
      )
      ..loadRequest(Uri.parse('https://edutrack.serveblog.net/lifestyle-assessment'));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFCE4EC),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD81B60),
        foregroundColor: Colors.white,
        title: Text(
          'Gaya Hidup & Kesehatan',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
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
