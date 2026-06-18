import 'package:flutter/material.dart';
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

  static const Color _pink = Color(0xFFD4537E);
  static const Color _darkBg = Color(0xFF1A0A2E);

  Future<void> _login() async {
    setState(() => _isLoading = true);
    try {
      final result = await ApiService.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      // Laravel Sanctum return 'token' kalau berhasil
      final token = result['token'] ?? result['access_token'];

      if (token != null && mounted) {
        await ApiService.saveToken(token); // simpan token
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => const HomeScreen()),
        );
      } else {
        _showError(
          result['message'] ?? 'Login gagal. Periksa email & password.',
        );
      }
    } catch (e) {
      _showError('Koneksi gagal: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showError(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.red.shade700),
    );
  }

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.of(context).size.height;

    return Scaffold(
      backgroundColor: _darkBg,
      body: SafeArea(
        child: SizedBox(
          height: screenHeight,
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 48),
                _buildLogo(),
                const SizedBox(height: 48),
                _buildHeadline(),
                const SizedBox(height: 32),
                _buildFeatureGrid(),
                SizedBox(height: screenHeight * 0.06),
                _buildLoginSection(),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogo() {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: _pink,
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Icon(
            Icons.school_rounded,
            color: Colors.white,
            size: 20,
          ),
        ),
        const SizedBox(width: 10),
        const Text(
          'EduTrack',
          style: TextStyle(
            color: Colors.white,
            fontSize: 17,
            fontWeight: FontWeight.w500,
            letterSpacing: 0.3,
          ),
        ),
      ],
    );
  }

  Widget _buildHeadline() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
          decoration: BoxDecoration(
            color: _pink.withOpacity(0.15),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: _pink.withOpacity(0.4), width: 0.5),
          ),
          child: const Text(
            'Portal Mahasiswa',
            style: TextStyle(
              color: Color(0xFFED93B1),
              fontSize: 12,
              fontWeight: FontWeight.w500,
              letterSpacing: 0.5,
            ),
          ),
        ),
        const SizedBox(height: 18),
        RichText(
          text: const TextSpan(
            style: TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.w500,
              height: 1.25,
              letterSpacing: -0.3,
            ),
            children: [
              TextSpan(
                text: 'Pantau Akademikmu\n',
                style: TextStyle(color: Colors.white),
              ),
              TextSpan(
                text: 'Kapan Saja.',
                style: TextStyle(color: _pink),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        Text(
          'Satu platform untuk absensi, nilai, jadwal, dan rapor — real-time, terintegrasi langsung dengan sistem kampus.',
          style: TextStyle(
            color: Colors.white.withOpacity(0.5),
            fontSize: 14,
            height: 1.7,
          ),
        ),
      ],
    );
  }

  Widget _buildFeatureGrid() {
    final features = [
      {
        'icon': Icons.check_box_rounded,
        'title': 'Absensi',
        'sub': 'Rekap kehadiran',
      },
      {'icon': Icons.star_rounded, 'title': 'Nilai', 'sub': 'Semua matakuliah'},
      {
        'icon': Icons.calendar_month_rounded,
        'title': 'Jadwal',
        'sub': 'Kuliah harian',
      },
      {
        'icon': Icons.description_rounded,
        'title': 'Rapor',
        'sub': 'Per semester',
      },
    ];
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 10,
      crossAxisSpacing: 10,
      childAspectRatio: 1.5,
      children: features.map((f) => _buildFeatureCard(f)).toList(),
    );
  }

  Widget _buildFeatureCard(Map f) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.08), width: 0.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: _pink.withOpacity(0.2),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(f['icon'] as IconData, color: _pink, size: 15),
          ),
          const SizedBox(height: 8),
          Text(
            f['title'] as String,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 13,
              fontWeight: FontWeight.w500,
            ),
          ),
          Text(
            f['sub'] as String,
            style: TextStyle(
              color: Colors.white.withOpacity(0.4),
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }

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
      child: OutlinedButton(
        onPressed: () => setState(() => _showLoginBox = true),
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.white,
          side: BorderSide(color: Colors.white.withOpacity(0.15), width: 0.5),
          backgroundColor: Colors.white.withOpacity(0.06),
          padding: const EdgeInsets.symmetric(vertical: 15),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: const Text(
          'Masuk ke Akun',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
        ),
      ),
    );
  }

  Widget _buildLoginBox() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.06),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withOpacity(0.12), width: 0.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Masuk ke akun mahasiswa',
            style: TextStyle(
              color: Colors.white.withOpacity(0.5),
              fontSize: 12,
            ),
          ),
          const SizedBox(height: 16),
          _buildTextField(
            _emailController,
            'Email',
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
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _login,
              style: ElevatedButton.styleFrom(
                backgroundColor: _pink,
                foregroundColor: Colors.white,
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
                        color: Colors.white,
                        strokeWidth: 2,
                      ),
                    )
                  : const Text(
                      'Masuk',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTextField(
    TextEditingController ctrl,
    String hint,
    IconData icon,
    bool isPassword,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.07),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.white.withOpacity(0.1), width: 0.5),
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
            color: Colors.white.withOpacity(0.3),
            fontSize: 14,
          ),
          prefixIcon: Icon(
            icon,
            color: Colors.white.withOpacity(0.3),
            size: 18,
          ),
          suffixIcon: isPassword
              ? IconButton(
                  icon: Icon(
                    _obscurePassword
                        ? Icons.visibility_off_outlined
                        : Icons.visibility_outlined,
                    color: Colors.white.withOpacity(0.3),
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

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
