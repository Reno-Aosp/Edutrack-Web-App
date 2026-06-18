import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';
import 'dart:typed_data';
import '../models/user_model.dart';
import '../services/api_service.dart';

class EditProfilScreen extends StatefulWidget {
  final UserModel? user;
  const EditProfilScreen({super.key, this.user});

  @override
  State<EditProfilScreen> createState() => _EditProfilScreenState();
}

class _EditProfilScreenState extends State<EditProfilScreen> {
  late TextEditingController nameController;
  late TextEditingController emailController;
  late TextEditingController prodiController;
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = false;
  File? _selectedImage;
  Uint8List? _selectedImageBytes;
  XFile? _selectedXFile; // keep XFile for cross-platform upload
  final ImagePicker _imagePicker = ImagePicker();

  @override
  void initState() {
    super.initState();
    nameController = TextEditingController(text: widget.user?.name ?? '');
    emailController = TextEditingController(text: widget.user?.email ?? '');
    prodiController = TextEditingController(text: widget.user?.mahasiswa?.prodi ?? '');
  }

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    prodiController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    try {
      final pickedFile = await _imagePicker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );
      if (pickedFile != null) {
        final bytes = await pickedFile.readAsBytes();
        if (!mounted) return;
        setState(() {
          _selectedXFile = pickedFile;
          _selectedImageBytes = bytes;
          if (!kIsWeb) _selectedImage = File(pickedFile.path);
        });
      }
    } catch (e) {
      // FIX: avoid_print — use debugPrint
      debugPrint('Error picking image: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal memilih foto'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _updateProfile() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    // 1. If a new photo was selected, upload it first
    if (_selectedXFile != null || _selectedImage != null) {
      Map<String, dynamic> photoResult;
      if (kIsWeb && _selectedXFile != null) {
        photoResult = await ApiService.uploadProfilePhotoXFile(_selectedXFile!);
      } else if (_selectedImage != null) {
        photoResult = await ApiService.uploadProfilePhoto(_selectedImage!.path);
      } else {
        photoResult = {'success': false, 'message': 'No photo selected'};
      }

      if (!mounted) return;

      if (photoResult['success'] != true) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal upload foto: ${photoResult['message']}'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
        return;
      }
    }

    // 2. Save name/email/prodi
    final success = await ApiService.updateProfile(
      name: nameController.text,
      email: emailController.text,
      prodi: prodiController.text,
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profil berhasil diperbarui ✅'),
          backgroundColor: Colors.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Gagal memperbarui profil'),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFE91E8C),
        foregroundColor: Colors.white,
        title: Text('Edit Profil', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Foto Profil
              Center(
                child: Column(
                  children: [
                    Container(
                      width: 120,
                      height: 120,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.grey.shade200,
                        border: Border.all(color: const Color(0xFFE91E8C), width: 2),
                      ),
                      child: _selectedImageBytes != null
                          ? ClipOval(
                              child: Image.memory(_selectedImageBytes!, fit: BoxFit.cover),
                            )
                          : (widget.user?.profilePhoto != null &&
                                  widget.user!.profilePhoto!.isNotEmpty
                              ? ClipOval(
                                  child: Image.network(
                                    widget.user!.profilePhoto!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) =>
                                        const Icon(Icons.person, size: 60),
                                  ),
                                )
                              : const Icon(Icons.person, size: 60)),
                    ),
                    const SizedBox(height: 12),
                    // "Pilih Foto" button replaces the whole section
                    SizedBox(
                      width: double.infinity,
                      height: 45,
                      child: ElevatedButton.icon(
                        onPressed: _pickImage,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFE91E8C),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: const Icon(Icons.photo_camera),
                        label: Text(
                          _selectedImage != null || _selectedImageBytes != null
                              ? 'Ganti Pilihan Foto'
                              : 'Pilih Foto Profil',
                          style: GoogleFonts.poppins(fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Nama
              Text('Nama Lengkap',
                  style: GoogleFonts.poppins(
                      fontSize: 14, fontWeight: FontWeight.w600, color: const Color(0xFF5C1033))),
              const SizedBox(height: 8),
              TextFormField(
                controller: nameController,
                decoration: InputDecoration(
                  hintText: 'Masukkan nama lengkap',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE91E8C), width: 2),
                  ),
                  prefixIcon: const Icon(Icons.person, color: Color(0xFFE91E8C)),
                ),
                validator: (value) =>
                    (value?.isEmpty ?? true) ? 'Nama tidak boleh kosong' : null,
              ),
              const SizedBox(height: 20),

              // Email
              Text('Email',
                  style: GoogleFonts.poppins(
                      fontSize: 14, fontWeight: FontWeight.w600, color: const Color(0xFF5C1033))),
              const SizedBox(height: 8),
              TextFormField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  hintText: 'Masukkan email',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE91E8C), width: 2),
                  ),
                  prefixIcon: const Icon(Icons.email, color: Color(0xFFE91E8C)),
                ),
                validator: (value) {
                  if (value?.isEmpty ?? true) return 'Email tidak boleh kosong';
                  if (!value!.contains('@')) return 'Format email tidak valid';
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Program Studi
              Text('Program Studi',
                  style: GoogleFonts.poppins(
                      fontSize: 14, fontWeight: FontWeight.w600, color: const Color(0xFF5C1033))),
              const SizedBox(height: 8),
              TextFormField(
                controller: prodiController,
                decoration: InputDecoration(
                  hintText: 'Masukkan program studi',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Color(0xFFE91E8C), width: 2),
                  ),
                  prefixIcon: const Icon(Icons.school, color: Color(0xFFE91E8C)),
                ),
                validator: (value) =>
                    (value?.isEmpty ?? true) ? 'Program studi tidak boleh kosong' : null,
              ),
              const SizedBox(height: 32),

              // Simpan
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _updateProfile,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE91E8C),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    disabledBackgroundColor: Colors.grey.shade300,
                  ),
                  child: _isLoading
                      ? const SizedBox(
                          height: 24,
                          width: 24,
                          child: CircularProgressIndicator(
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            strokeWidth: 2,
                          ),
                        )
                      : Text('Simpan Perubahan',
                          style: GoogleFonts.poppins(
                              fontWeight: FontWeight.bold, fontSize: 16)),
                ),
              ),
              const SizedBox(height: 12),

              // Batal
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton(
                  onPressed: _isLoading ? null : () => Navigator.pop(context),
                  style: OutlinedButton.styleFrom(
                    backgroundColor: Colors.white,
                    side: const BorderSide(color: Color(0xFFE91E8C), width: 2),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text(
                    'Batal',
                    style: GoogleFonts.poppins(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: const Color(0xFFE91E8C)),
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
