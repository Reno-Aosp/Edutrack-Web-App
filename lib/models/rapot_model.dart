import 'nilai_model.dart';

class MahasiswaModel {
  final String nama;
  final String nim;
  final String prodi;

  MahasiswaModel({required this.nama, required this.nim, required this.prodi});

  factory MahasiswaModel.fromJson(Map<String, dynamic> json) {
    return MahasiswaModel(
      nama: json['nama'] ?? '',
      nim: json['nim'] ?? '',
      prodi: json['prodi'] ?? '',
    );
  }
}

class RapotResponse {
  final MahasiswaModel? mahasiswa;
  final List<NilaiModel> nilai;
  final double ipk;
  final int totalSks;

  RapotResponse({
    this.mahasiswa,
    required this.nilai,
    required this.ipk,
    required this.totalSks,
  });

  factory RapotResponse.fromJson(Map<String, dynamic> json) {
    return RapotResponse(
      mahasiswa: json['mahasiswa'] != null
          ? MahasiswaModel.fromJson(json['mahasiswa'])
          : null,
      nilai: (json['nilai'] as List<dynamic>? ?? [])
          .map((e) => NilaiModel.fromJson(e))
          .toList(),
      ipk: (json['ipk'] ?? 0).toDouble(),
      totalSks: json['total_sks'] ?? 0,
    );
  }
}
