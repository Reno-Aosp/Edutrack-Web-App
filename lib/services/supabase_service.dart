import 'package:flutter/foundation.dart';
import 'package:image_picker/image_picker.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import '../config/supabase_config.dart';

class SupabaseService {
  static final _client = Supabase.instance.client;

  // =========================================================
  // Upload foto surat ke Supabase Storage
  // Pakai XFile + readAsBytes() agar support Web & Mobile
  // Return: public URL atau null kalau gagal
  // =========================================================
  static Future<String?> uploadSurat({
    required XFile xFile, // ← XFile dari ImagePicker
    required int mahasiswaId,
    required String tanggal,
  }) async {
    try {
      // readAsBytes() works di web & mobile (tidak perlu File)
      final Uint8List bytes = await xFile.readAsBytes();

      final ext = xFile.name.split('.').last.toLowerCase();
      final mimeType = ext == 'png' ? 'image/png' : 'image/jpeg';

      // nama file unik di folder mahasiswa
      final fileName =
          '$mahasiswaId/${tanggal}_${DateTime.now().millisecondsSinceEpoch}.$ext';

      debugPrint('UPLOAD FILE: $fileName (${bytes.length} bytes)');

      // upload bytes ke Supabase Storage
      await _client.storage
          .from(SupabaseConfig.bucketSurat)
          .uploadBinary(
            fileName,
            bytes,
            fileOptions: FileOptions(contentType: mimeType, upsert: true),
          );

      // ambil public URL
      final url = _client.storage
          .from(SupabaseConfig.bucketSurat)
          .getPublicUrl(fileName);

      debugPrint('UPLOAD BERHASIL: $url');

      return url;
    } catch (e) {
      debugPrint('SUPABASE UPLOAD ERROR: $e');
      return null;
    }
  }

  // =========================================================
  // Upload foto profil ke Supabase Storage bucket: profile-photos
  // Return: public URL atau null kalau gagal
  // =========================================================
  static Future<String?> uploadProfilePhoto({
    required XFile xFile,
    required int userId,
  }) async {
    try {
      final Uint8List bytes = await xFile.readAsBytes();
      final ext = xFile.name.split('.').last.toLowerCase();
      final mimeType = ext == 'png' ? 'image/png' : 'image/jpeg';

      final fileName = '$userId/profile_${DateTime.now().millisecondsSinceEpoch}.$ext';

      debugPrint('UPLOAD PROFILE PHOTO: $fileName (${bytes.length} bytes)');

      await _client.storage
          .from(SupabaseConfig.bucketProfil)
          .uploadBinary(
            fileName,
            bytes,
            fileOptions: FileOptions(contentType: mimeType, upsert: true),
          );

      final url = _client.storage
          .from(SupabaseConfig.bucketProfil)
          .getPublicUrl(fileName);

      debugPrint('PROFILE PHOTO URL: $url');
      return url;
    } catch (e) {
      debugPrint('SUPABASE PROFILE PHOTO ERROR: $e');
      throw Exception('SupaError: $e');
    }
  }

  // =========================================================
  // Insert notifikasi ke tabel notifications di Supabase
  // =========================================================
  static Future<void> kirimNotifikasiDosen({
    required String namaMahasiswa,
    required String mataKuliah,
    required String tanggal,
    required String status,
    required String fotoUrl,
  }) async {
    try {
      debugPrint('MENGIRIM NOTIFIKASI DOSEN...');

      await _client.from('notifications').insert({
        'judul': 'Surat Keterangan Baru',
        'pesan':
            '$namaMahasiswa mengupload surat $status untuk $mataKuliah pada $tanggal',
        'foto_url': fotoUrl,
        'tipe': 'surat_izin',
        'is_read': false,
        'created_at': DateTime.now().toIso8601String(),
      });

      debugPrint('NOTIFIKASI BERHASIL DIKIRIM');
    } catch (e) {
      debugPrint('SUPABASE NOTIF ERROR: $e');
    }
  }
}
