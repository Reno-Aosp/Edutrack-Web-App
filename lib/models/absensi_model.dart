class AbsensiModel {
  final int id;
  final int matkulId;
  final String mataKuliah;
  final String kelas;
  final String tanggal;
  final String status;
  final String keterangan;
  final String? fotoUrl; // ← URL foto surat dari Supabase Storage

  AbsensiModel({
    required this.id,
    required this.matkulId,
    required this.mataKuliah,
    required this.kelas,
    required this.tanggal,
    required this.status,
    required this.keterangan,
    this.fotoUrl,
  });

  factory AbsensiModel.fromJson(Map<String, dynamic> json) {
    return AbsensiModel(
      id: json['id'] ?? 0,
      matkulId: json['matkul_id'] ?? 0,
      mataKuliah: json['mata_kuliah'] ?? json['mataKuliah'] ?? '-',
      kelas: json['kelas'] ?? '-',
      tanggal: json['tanggal'] ?? '',
      status: json['status'] ?? '',
      keterangan: json['keterangan'] ?? '',
      fotoUrl: json['foto_url'],
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'matkul_id': matkulId,
    'mata_kuliah': mataKuliah,
    'kelas': kelas,
    'tanggal': tanggal,
    'status': status,
    'keterangan': keterangan,
    'foto_url': fotoUrl,
  };
}
