import 'package:flutter/material.dart';
import 'package:web/web.dart' as web;
import 'dart:ui_web' as ui_web;
import 'package:google_fonts/google_fonts.dart';

class LifestyleScreen extends StatefulWidget {
  const LifestyleScreen({super.key});

  @override
  State<LifestyleScreen> createState() => _LifestyleScreenState();
}

class _LifestyleScreenState extends State<LifestyleScreen> {
  final String _viewId = 'lifestyle-${DateTime.now().millisecondsSinceEpoch}';
  bool _registered = false;
  web.HTMLIFrameElement? _iframe;

  @override
  void initState() {
    super.initState();
    _setupIframe('https://edutracks.duckdns.org/lifestyle-assessment');
  }

  void _setupIframe(String url) {
    if (_registered) return;
    _registered = true;

    final el = web.document.createElement('iframe') as web.HTMLIFrameElement;
    el.setAttribute('src', url);
    el.style.setProperty('border', '0');
    el.style.setProperty('width', '100%');
    el.style.setProperty('height', '100%');
    // Allow scripts and top navigation so the iframe can navigate itself smoothly
    el.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms allow-top-navigation');

    _iframe = el;

    ui_web.platformViewRegistry.registerViewFactory(
      _viewId,
      (int id) => _iframe!,
    );
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
      body: HtmlElementView(viewType: _viewId),
    );
  }
}
