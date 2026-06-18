import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/user_model.dart';
import '../models/nilai_model.dart';
import '../models/absensi_model.dart';
import '../models/jadwal_model.dart';
import '../models/rapot_model.dart';
import '../models/matkul_model.dart';

class ApiService {
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

  static Future<UserModel?> getProfile() async {
    try {
      final token = await getToken();
      if (token == null) return null;
      final response = await http.get(
        Uri.parse(ApiConfig.profile),
        headers: ApiConfig.headers(token),
      );
      final data = jsonDecode(response.body);
      if (data['user'] != null) return UserModel.fromJson(data['user']);
      return null;
    } catch (e) {
      print('getProfile error: $e');
      return null;
    }
  }

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
      print('getNilai error: $e');
      return [];
    }
  }

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
      print('getAbsensi error: $e');
      return [];
    }
  }

  static Future<bool> inputAbsensi({
    required int mahasiswaId,
    required int matkulId,
    required String tanggal,
    required String status,
    String keterangan = '',
  }) async {
    try {
      final token = await getToken();
      if (token == null) return false;
      final response = await http.post(
        Uri.parse(ApiConfig.absensi),
        headers: ApiConfig.headers(token),
        body: jsonEncode({
          'mahasiswa_id': mahasiswaId,
          'matkul_id': matkulId,
          'tanggal': tanggal,
          'status': status,
          'keterangan': keterangan,
        }),
      );
      final data = jsonDecode(response.body);
      return response.statusCode == 201 || data['success'] == true;
    } catch (e) {
      print('inputAbsensi error: $e');
      return false;
    }
  }

  static Future<List<JadwalModel>> getJadwal() async {
    try {
      final token = await getToken();
      if (token == null) return [];
      final response = await http.get(
        Uri.parse(ApiConfig.jadwal),
        headers: ApiConfig.headers(token),
      );
      final data = jsonDecode(response.body);
      final List list = data['data'] ?? [];
      return list.map((e) => JadwalModel.fromJson(e)).toList();
    } catch (e) {
      print('getJadwal error: $e');
      return [];
    }
  }

  static Future<List<MatkulModel>> getMatakuliah({String? mahasiswaId}) async {
    try {
      final token = await getToken();
      if (token == null) {
        return [];
      }
      Uri uri = Uri.parse(ApiConfig.matkul);
      if (mahasiswaId != null && mahasiswaId.isNotEmpty) {
        uri = uri.replace(queryParameters: {'mahasiswa_id': mahasiswaId});
      }
      print('[Matkul] Fetching from: $uri');
      final response = await http.get(uri, headers: ApiConfig.headers(token));
      print('[Matkul] Status: ${response.statusCode}');

      if (response.statusCode != 200) {
        print('[Matkul] Error status: ${response.statusCode}');
        return [];
      }

      final responseData = jsonDecode(response.body);
      print('[Matkul] Response type: ${responseData.runtimeType}');

      List<dynamic> list = [];
      if (responseData is List) {
        list = responseData;
      } else if (responseData is Map && responseData['data'] is List) {
        list = responseData['data'];
      }

      print('[Matkul] Parsed ${list.length} items');
      return list
          .map((e) => MatkulModel.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      print('[Matkul] Error: $e');
      return [];
    }
  }

  static Future<RapotResponse?> getRapot({String? semester}) async {
    try {
      final token = await getToken();
      if (token == null) {
        return null;
      }
      final uri = Uri.parse(ApiConfig.rapot).replace(
        queryParameters: semester != null ? {'semester': semester} : null,
      );
      final response = await http.get(uri, headers: ApiConfig.headers(token));
      final data = jsonDecode(response.body);
      return RapotResponse.fromJson(data);
    } catch (e) {
      print('getRapot error: $e');
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
        Uri.parse(ApiConfig.updateProfile),
        headers: ApiConfig.headers(token),
        body: jsonEncode({'name': name, 'email': email, 'prodi': prodi}),
      );
      final data = jsonDecode(response.body);
      return response.statusCode == 200 || data['success'] == true;
    } catch (e) {
      print('updateProfile error: $e');
      return false;
    }
  }

  static Future<bool> uploadProfilePhoto(String filePath) async {
    try {
      final token = await getToken();
      if (token == null) return false;

      var request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiConfig.updateProfile}/photo'),
      );

      request.headers.addAll(ApiConfig.headers(token));
      request.files.add(
        await http.MultipartFile.fromPath('profile_photo', filePath),
      );

      final response = await request.send();
      final responseBody = await response.stream.bytesToString();
      final data = jsonDecode(responseBody);

      return response.statusCode == 200 || data['success'] == true;
    } catch (e) {
      print('uploadProfilePhoto error: $e');
      return false;
    }
  }
}
