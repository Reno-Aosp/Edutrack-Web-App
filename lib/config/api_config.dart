class ApiConfig {
  static const String baseUrl = 'http://127.0.0.1:8000/api';

  // Auth
  static const String login = '$baseUrl/login';
  static const String logout = '$baseUrl/logout';
  static const String profile = '$baseUrl/profile';
  static const String updateProfile = '$baseUrl/profile/update';

  // Nilai
  static const String nilai = '$baseUrl/nilai';

  // Absensi
  static const String absensi = '$baseUrl/absensi';

  // Matkul
  static const String matkul = '$baseUrl/mata-kuliah';

  // Jadwal
  static const String jadwal = '$baseUrl/jadwal';

  // Rapot
  static const String rapot = '$baseUrl/rapot';

  // Headers
  static Map<String, String> headers(String token) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };

  static const Map<String, String> defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
}
