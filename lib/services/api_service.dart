import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:image_picker/image_picker.dart';

import '../config/api_config.dart';
import '../models/user_model.dart';
import '../models/nilai_model.dart';
import '../models/absensi_model.dart';
import '../models/jadwal_model.dart';
import '../models/rapot_model.dart';
import '../models/matkul_model.dart';
import 'supabase_service.dart';


class ApiService {
  // ── TOKEN ─────────────────────────────────────────────────────────────────
  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
  }

  static Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // ── AUTH ──────────────────────────────────────────────────────────────────
  static Future<Map<String, dynamic>> login(
    String email,
    String password,
  ) async {
    try {
      final response = await http.post(
        Uri.parse(ApiConfig.login),
        headers: ApiConfig.defaultHeaders,
        body: jsonEncode({'email': email, 'password': password}),
      );

      return jsonDecode(response.body);
    } catch (e) {
      return {'error': 'Koneksi gagal. Pastikan server menyala.'};
    }
  }

  static Future<void> logout() async {
    try {
      final token = await getToken();

      if (token != null) {
        await http.post(
          Uri.parse(ApiConfig.logout),
          headers: ApiConfig.headers(token),
        );
      }
    } catch (_) {}

    await removeToken();
  }

  // ── PROFILE ───────────────────────────────────────────────────────────────
  static Future<UserModel?> getProfile() async {
    try {
      final token = await getToken();

      if (token == null) return null;

      final response = await http.get(
        Uri.parse(ApiConfig.profile),
        headers: ApiConfig.headers(token),
      );

      final data = jsonDecode(response.body);

      if (data['user'] != null) {
        return UserModel.fromJson(data['user']);
      }

      return null;
    } catch (e) {
      return null;
    }
  }

  static Future<bool> updateProfile({
    required String name,
    required String email,
    required String prodi,
  }) async {
    try {
      final token = await getToken();

      if (token == null) return false;

      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/profile/update'),
        headers: ApiConfig.headers(token),
        body: jsonEncode({'name': name, 'email': email, 'prodi': prodi}),
      );

      final data = jsonDecode(response.body);

      return response.statusCode == 200 || data['success'] == true;
    } catch (e) {
      return false;
    }
  }

  // ── UPLOAD PROFILE PHOTO (Mobile - file path → Supabase) ─────────────────
  static Future<Map<String, dynamic>> uploadProfilePhoto(String filePath) async {
    try {
      final token = await getToken();
      if (token == null) return {'success': false, 'message': 'Belum login'};

      // Get user id from profile first
      final profile = await getProfile();
      final userId = profile?.id ?? 0;

      final xFile = XFile(filePath);

      // 1. Upload to Supabase 'profile-photos' bucket
      final photoUrl = await SupabaseService.uploadProfilePhoto(
        xFile: xFile,
        userId: userId,
      );

      if (photoUrl == null) return {'success': false, 'message': 'Gagal upload ke Storage. Cek RLS Supabase.'};

      // 2. Save URL to Laravel DB
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/profile/update/photo-url'),
        headers: ApiConfig.headers(token),
        body: jsonEncode({'profile_photo': photoUrl}),
      );

      final data = jsonDecode(response.body);
      return {
        'success': response.statusCode == 200 || data['success'] == true,
        'message': data['message'] ?? 'Server error ${response.statusCode}'
      };
    } catch (e) {
      return {'success': false, 'message': 'Exception: $e'};
    }
  }

  // ── UPLOAD PROFILE PHOTO (Web - XFile bytes → Supabase) ──────────────────
  static Future<Map<String, dynamic>> uploadProfilePhotoXFile(XFile xFile) async {
    try {
      final token = await getToken();
      if (token == null) return {'success': false, 'message': 'Belum login'};

      final profile = await getProfile();
      final userId = profile?.id ?? 0;

      // 1. Upload to Supabase 'profile-photos' bucket
      final photoUrl = await SupabaseService.uploadProfilePhoto(
        xFile: xFile,
        userId: userId,
      );

      if (photoUrl == null) return {'success': false, 'message': 'Gagal upload ke Storage. Cek RLS Supabase.'};

      // 2. Save URL to Laravel DB
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/profile/update/photo-url'),
        headers: ApiConfig.headers(token),
        body: jsonEncode({'profile_photo': photoUrl}),
      );

      final data = jsonDecode(response.body);
      return {
        'success': response.statusCode == 200 || data['success'] == true,
        'message': data['message'] ?? 'Server error ${response.statusCode}'
      };
    } catch (e) {
      return {'success': false, 'message': 'Exception: $e'};
    }
  }

  // ── NILAI ─────────────────────────────────────────────────────────────────
  static Future<List<NilaiModel>> getNilai() async {
    try {
      final token = await getToken();

      if (token == null) return [];

      final response = await http.get(
        Uri.parse(ApiConfig.nilai),
        headers: ApiConfig.headers(token),
      );

      final data = jsonDecode(response.body);

      final List list = data['data'] ?? [];

      return list.map((e) => NilaiModel.fromJson(e)).toList();
    } catch (e) {
      return [];
    }
  }

  // ── ABSENSI ───────────────────────────────────────────────────────────────
  static Future<List<AbsensiModel>> getAbsensi() async {
    try {
      final token = await getToken();

      if (token == null) return [];

      final response = await http.get(
        Uri.parse(ApiConfig.absensi),
        headers: ApiConfig.headers(token),
      );

      final data = jsonDecode(response.body);

      final List list = data['data'] ?? [];

      return list.map((e) => AbsensiModel.fromJson(e)).toList();
    } catch (e) {
      return [];
    }
  }

  // INPUT ABSENSI
  static Future<Map<String, dynamic>> inputAbsensi({
    required int mahasiswaId,
    required int matkulId,
    required int sesiId,
    required String status,
    String keterangan = '',
  }) async {
    try {
      final token = await getToken();

      if (token == null) {
        return {'success': false, 'message': 'Token tidak ditemukan'};
      }

      final response = await http.post(
        Uri.parse(ApiConfig.absensi),
        headers: ApiConfig.headers(token),
        body: jsonEncode({
          'mahasiswa_id': mahasiswaId,
          'matkul_id': matkulId,
          'sesi_id': sesiId,
          'status': status,
          'keterangan': keterangan,
        }),
      );

      final data = jsonDecode(response.body);

      return {
        'success': response.statusCode == 201 || data['success'] == true,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Koneksi gagal'};
    }
  }

  // ── UPLOAD FOTO SURAT (SUPABASE STORAGE) ─────────────────────────────────
  static Future<Map<String, dynamic>> uploadFotoSurat({
    required XFile xFile, // ← GANTI dari filePath ke xFile (support web)
    required int mahasiswaId,
    required int matkulId,
    required String tanggal,
    String status = 'sakit',
  }) async {
    try {
      final token = await getToken();

      if (token == null) {
        return {'success': false, 'message': 'Token tidak ditemukan'};
      }

      // 1. Upload ke Supabase Storage pakai XFile
      final fotoUrl = await SupabaseService.uploadSurat(
        xFile: xFile, // ← kirim XFile langsung
        mahasiswaId: mahasiswaId,
        tanggal: tanggal,
      );

      if (fotoUrl == null) {
        return {'success': false, 'message': 'Gagal upload foto ke storage'};
      }

      // 2. Simpan URL ke Laravel
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/absensi/upload-surat'),
        headers: ApiConfig.headers(token),
        body: jsonEncode({
          'mahasiswa_id': mahasiswaId,
          'matkul_id': matkulId,
          'tanggal': tanggal,
          'status': status,
          'foto_url': fotoUrl,
        }),
      );

      final data = jsonDecode(response.body);

      // 3. Kirim notifikasi ke Supabase
      if (response.statusCode == 200 || data['success'] == true) {
        await SupabaseService.kirimNotifikasiDosen(
          namaMahasiswa: data['nama_mahasiswa'] ?? 'Mahasiswa',
          mataKuliah: data['mata_kuliah'] ?? '-',
          tanggal: tanggal,
          status: status,
          fotoUrl: fotoUrl,
        );
      }

      return {
        'success': response.statusCode == 200 || data['success'] == true,
        'foto_url': fotoUrl,
        'message': data['message'] ?? '',
      };
    } catch (e) {
      return {'success': false, 'message': 'Error: $e'};
    }
  }

  // ── SESI ABSENSI ──────────────────────────────────────────────────────────
  static Future<List<Map<String, dynamic>>> getSesiAktif() async {
    try {
      final token = await getToken();

      if (token == null) return [];

      final response = await http.get(
        Uri.parse(ApiConfig.sesiAktif),
        headers: ApiConfig.headers(token),
      );

      if (response.statusCode != 200) return [];

      final data = jsonDecode(response.body);

      final List list = data['data'] ?? data['sesi_aktif'] ?? [];

      return list.map((e) => Map<String, dynamic>.from(e)).toList();
    } catch (e) {
      return [];
    }
  }

  // ── MATA KULIAH ───────────────────────────────────────────────────────────
  static Future<List<MatkulModel>> getMatakuliah({String? mahasiswaId}) async {
    try {
      final token = await getToken();

      if (token == null) return [];

      String url = ApiConfig.mataKuliah;

      if (mahasiswaId != null && mahasiswaId.isNotEmpty) {
        url += '?mahasiswa_id=$mahasiswaId';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: ApiConfig.headers(token),
      );

      if (response.statusCode != 200) return [];

      final data = jsonDecode(response.body);

      final List list = data is List ? data : (data['data'] ?? []);

      return list.map((e) => MatkulModel.fromJson(e)).toList();
    } catch (e) {
      return [];
    }
  }

  // ── JADWAL ────────────────────────────────────────────────────────────────
  static Future<List<JadwalModel>> getJadwal() async {
    try {
      final token = await getToken();

      if (token == null) return [];

      final response = await http.get(
        Uri.parse(ApiConfig.jadwal),
        headers: ApiConfig.headers(token),
      );

      final data = jsonDecode(response.body);

      final List list = data is List ? data : (data['data'] ?? []);

      return list.map((e) => JadwalModel.fromJson(e)).toList();
    } catch (e) {
      return [];
    }
  }

  // ── RAPOT ─────────────────────────────────────────────────────────────────
  static Future<RapotResponse?> getRapot({String? semester}) async {
    try {
      final token = await getToken();

      if (token == null) return null;

      String url = ApiConfig.rapot;

      if (semester != null && semester.isNotEmpty) {
        url += '?semester=${Uri.encodeComponent(semester)}';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: ApiConfig.headers(token),
      );

      final data = jsonDecode(response.body);

      return RapotResponse.fromJson(data);
    } catch (e) {
      return null;
    }
  }
}
