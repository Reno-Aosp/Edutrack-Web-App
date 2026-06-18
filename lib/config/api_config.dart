class ApiConfig {
  static const String baseUrl = 'http://127.0.0.1:8000/api';
  static const String login = '$baseUrl/login';
  static const String logout = '$baseUrl/logout';
  static const String profile = '$baseUrl/profile';
  static const String nilai = '$baseUrl/nilai';
  static const String absensi = '$baseUrl/absensi';
  static const String jadwal = '$baseUrl/jadwal';
  static const String rapot = '$baseUrl/rapot';
  static const String sesiAktif = '$baseUrl/sesi-aktif';
  static const String mataKuliah = '$baseUrl/mata-kuliah';

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
