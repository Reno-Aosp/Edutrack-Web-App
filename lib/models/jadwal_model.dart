class JadwalModel {
  final String hari;
  final String jamMulai;
  final String jamSelesai;
  final String matkul;
  final String dosen;
  final String ruangan;
  final String kode;

  JadwalModel({
    required this.hari,
    required this.jamMulai,
    required this.jamSelesai,
    required this.matkul,
    required this.dosen,
    required this.ruangan,
    required this.kode,
  });

  factory JadwalModel.fromJson(Map<String, dynamic> json) {
    return JadwalModel(
      hari: json['hari'] ?? '',
      jamMulai: json['jam_mulai'] ?? '',
      jamSelesai: json['jam_selesai'] ?? '',
      matkul: json['matkul'] ?? '',
      dosen: json['dosen'] ?? '',
      ruangan: json['ruangan'] ?? '',
      kode: json['kode'] ?? '',
    );
  }
}
