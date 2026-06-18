import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'home_screen.dart';
import '../services/api_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _obscurePassword = true;
  bool _isLoading = false;
  bool _showLoginBox = false;

  static const _gradientColors = [
    Color(0xFFE91E8C),
    Color(0xFF9C1B5E),
    Color(0xFF5C1033),
  ];

  // Akun user testing
  static const _testAccounts = [
    ('mhs@campus.com', '123mhs'),
    ('mhs1@campus.com', '1234mhs'),
    ('mhs2@campus.com', 'mhs2345'),
    ('budi@campus.com', 'budii123'),
    ('av@campus.com', 'ave1234'),
  ];

  // =========================================================
  // LOGIN
  // =========================================================
  Future<void> _login() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      _showError('Email dan password tidak boleh kosong.');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final result = await ApiService.login(email, password);

      if (!mounted) return;

      if (result['message'] != null && result['token'] == null) {
        _showError(result['message']);
        return;
      }

      final token = result['token'] ?? result['access_token'];
      final user = result['user'];

      if (token == null) {
        _showError(
          result['message'] ?? 'Login gagal. Periksa email & password.',
        );
        return;
      }

      final role = user?['role'] ?? '';
      if (role != 'mahasiswa') {
        _showError('Akses ditolak. Gunakan portal admin untuk login.');
        return;
      }

      await ApiService.saveToken(token);

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const HomeScreen()),
      );
    } catch (e) {
      if (mounted) _showError('Koneksi gagal: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red.shade700),
    );
  }

  // Tap akun testing → langsung isi form
  void _fillAccount(String email, String password) {
    _emailController.text = email;
    _passwordController.text = password;
    setState(() {
      _showLoginBox = true;
    });
  }

  // =========================================================
  // BUILD
  // =========================================================
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF5C1033),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: _gradientColors,
          ),
        ),
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(24, 32, 24, 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildTopRow(),
                      const SizedBox(height: 28),
                      _buildHeadline(),
                      const SizedBox(height: 18),
                      _buildPills(),
                      const SizedBox(height: 24),
                      _buildLoginSection(),
                    ],
                  ),
                ),
              ),
              AnimatedContainer(
                duration: const Duration(milliseconds: 350),
                curve: Curves.easeInOut,
                height: _showLoginBox ? 0 : 190,
                child: ClipRect(child: _buildCampusPhoto()),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // =========================================================
  // TOP ROW
  // =========================================================
  Widget _buildTopRow() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.18),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.28),
                  width: 0.5,
                ),
              ),
              child: const Icon(
                Icons.school_rounded,
                color: Colors.white,
                size: 26,
              ),
            ),
            const SizedBox(width: 10),
            Text(
              'EduTrack',
              style: GoogleFonts.poppins(
                color: Colors.white,
                fontSize: 17,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.2,
              ),
            ),
          ],
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: Colors.white.withValues(alpha: 0.25),
              width: 0.5,
            ),
          ),
          child: Text(
            'Portal Mahasiswa',
            style: GoogleFonts.poppins(
              color: Colors.white.withValues(alpha: 0.9),
              fontSize: 11,
            ),
          ),
        ),
      ],
    );
  }

  // =========================================================
  // HEADLINE
  // =========================================================
  Widget _buildHeadline() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Pantau\nAkademikmu.',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontSize: 38,
            fontWeight: FontWeight.w800,
            height: 1.1,
            letterSpacing: -1,
          ),
        ),
        const SizedBox(height: 14),
        Text(
          'Satu platform lengkap untuk mahasiswa — pantau absensi, cek nilai semua mata kuliah, lihat jadwal kuliah harian, hingga unduh rapor per semester.',
          style: GoogleFonts.poppins(
            color: Colors.white.withValues(alpha: 0.7),
            fontSize: 13,
            height: 1.7,
          ),
        ),
      ],
    );
  }

  // =========================================================
  // PILLS
  // =========================================================
  Widget _buildPills() {
    final items = [
      (Icons.check_circle_outline_rounded, 'Absensi'),
      (Icons.star_outline_rounded, 'Nilai'),
      (Icons.calendar_month_outlined, 'Jadwal'),
      (Icons.description_outlined, 'Rapor'),
    ];
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: [
        // Pill biasa
        ...items.map((item) {
          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(30),
              border: Border.all(
                color: Colors.white.withValues(alpha: 0.25),
                width: 0.5,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(item.$1, color: Colors.white, size: 13),
                const SizedBox(width: 5),
                Text(
                  item.$2,
                  style: GoogleFonts.poppins(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          );
        }),

        // ── Pill "User Testing" ──────────────────────────
        GestureDetector(
          onTap: _showTestingDialog,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFE91E8C).withValues(alpha: 0.25),
              borderRadius: BorderRadius.circular(30),
              border: Border.all(
                color: const Color(0xFFE91E8C).withValues(alpha: 0.5),
                width: 0.8,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.science_outlined,
                  color: Colors.white,
                  size: 13,
                ),
                const SizedBox(width: 5),
                Text(
                  'User Testing',
                  style: GoogleFonts.poppins(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ── Dialog daftar akun testing ───────────────────────────
  void _showTestingDialog() {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: const Color(0xFF5C1033),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Akun User Testing',
                    style: GoogleFonts.poppins(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  GestureDetector(
                    onTap: () => Navigator.pop(ctx),
                    child: Icon(
                      Icons.close_rounded,
                      color: Colors.white.withValues(alpha: 0.5),
                      size: 20,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                'Tap akun untuk mengisi form login otomatis',
                style: GoogleFonts.poppins(
                  color: Colors.white.withValues(alpha: 0.4),
                  fontSize: 10,
                ),
              ),
              const SizedBox(height: 14),
              ..._testAccounts.map(
                (acc) => GestureDetector(
                  onTap: () {
                    Navigator.pop(ctx);
                    _fillAccount(acc.$1, acc.$2);
                  },
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.07),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: Colors.white.withValues(alpha: 0.12),
                        width: 0.5,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            color: const Color(
                              0xFFE91E8C,
                            ).withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(
                            Icons.person_outline_rounded,
                            color: Colors.white70,
                            size: 15,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                acc.$1,
                                style: GoogleFonts.poppins(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              Text(
                                acc.$2,
                                style: GoogleFonts.poppins(
                                  color: Colors.white.withValues(alpha: 0.45),
                                  fontSize: 10,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Icon(
                          Icons.arrow_forward_ios_rounded,
                          color: Colors.white.withValues(alpha: 0.3),
                          size: 12,
                        ),
                      ],
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

  // =========================================================
  // LOGIN SECTION
  // =========================================================
  Widget _buildLoginSection() {
    return AnimatedSize(
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeInOut,
      child: _showLoginBox ? _buildLoginBox() : _buildLoginButton(),
    );
  }

  Widget _buildLoginButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: () => setState(() => _showLoginBox = true),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.white,
          foregroundColor: const Color(0xFFE91E8C),
          padding: const EdgeInsets.symmetric(vertical: 15),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          elevation: 0,
        ),
        child: Text(
          'Masuk ke Akun →',
          style: GoogleFonts.poppins(fontSize: 15, fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  Widget _buildLoginBox() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: Colors.white.withValues(alpha: 0.22),
          width: 0.5,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Masuk ke akun',
                style: GoogleFonts.poppins(
                  color: Colors.white,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              GestureDetector(
                onTap: () => setState(() => _showLoginBox = false),
                child: Icon(
                  Icons.close_rounded,
                  color: Colors.white.withValues(alpha: 0.5),
                  size: 18,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _buildTextField(
            _emailController,
            'Email kampus',
            Icons.mail_outline_rounded,
            false,
          ),
          const SizedBox(height: 10),
          _buildTextField(
            _passwordController,
            'Password',
            Icons.lock_outline_rounded,
            true,
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _login,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFFE91E8C),
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                elevation: 0,
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                        color: Color(0xFFE91E8C),
                        strokeWidth: 2,
                      ),
                    )
                  : Text(
                      'Masuk',
                      style: GoogleFonts.poppins(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================
  // =========================================================
  // TEXT FIELD
  // =========================================================
  Widget _buildTextField(
    TextEditingController ctrl,
    String hint,
    IconData icon,
    bool isPassword,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: Colors.white.withValues(alpha: 0.2),
          width: 0.5,
        ),
      ),
      child: TextField(
        controller: ctrl,
        obscureText: isPassword && _obscurePassword,
        keyboardType: isPassword
            ? TextInputType.visiblePassword
            : TextInputType.emailAddress,
        style: const TextStyle(color: Colors.white, fontSize: 14),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: TextStyle(
            color: Colors.white.withValues(alpha: 0.35),
            fontSize: 13,
          ),
          prefixIcon: Icon(
            icon,
            color: Colors.white.withValues(alpha: 0.4),
            size: 18,
          ),
          suffixIcon: isPassword
              ? IconButton(
                  icon: Icon(
                    _obscurePassword
                        ? Icons.visibility_off_outlined
                        : Icons.visibility_outlined,
                    color: Colors.white.withValues(alpha: 0.4),
                    size: 18,
                  ),
                  onPressed: () =>
                      setState(() => _obscurePassword = !_obscurePassword),
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 14,
            vertical: 13,
          ),
        ),
      ),
    );
  }

  // =========================================================
  // FOTO KAMPUS
  // =========================================================
  Widget _buildCampusPhoto() {
    return Stack(
      fit: StackFit.expand,
      children: [
        Image.asset(
          'assets/images/kampus.jpg',
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => Container(
            color: Colors.white.withValues(alpha: 0.08),
            child: Icon(
              Icons.image_outlined,
              color: Colors.white.withValues(alpha: 0.2),
              size: 40,
            ),
          ),
        ),
        Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [Color(0xCC5C1033), Color(0x445C1033)],
            ),
          ),
        ),
        Positioned(
          bottom: 16,
          left: 16,
          right: 16,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              color: Colors.black.withValues(alpha: 0.35),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE91E8C).withValues(alpha: 0.3),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.location_city_rounded,
                      color: Colors.white,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Politeknik Elektronika Negeri Surabaya',
                        style: GoogleFonts.poppins(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      Text(
                        'Sistem akademik terintegrasi',
                        style: GoogleFonts.poppins(
                          color: Colors.white60,
                          fontSize: 10,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
