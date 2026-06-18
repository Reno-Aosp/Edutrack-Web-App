class AbsensiModel {
  final int id;
  final String mataKuliah;
  final String tanggal;
  final String status;
  final String keterangan;
  final int mahasiswaId;
  final int matkulId;

  AbsensiModel({
    required this.id,
    required this.mataKuliah,
    required this.tanggal,
    required this.status,
    required this.keterangan,
    required this.mahasiswaId,
    required this.matkulId,
  });

  factory AbsensiModel.fromJson(Map<String, dynamic> json) {
    return AbsensiModel(
      id: json['id'],
      mataKuliah: json['mata_kuliah']?['nama'] ?? '-',
      tanggal: json['tanggal'] ?? '-',
      status: json['status'] ?? '-',
      keterangan: json['keterangan'] ?? '',
      mahasiswaId: json['mahasiswa_id'] ?? 0,
      matkulId: json['matkul_id'] ?? 0,
    );
  }
}
