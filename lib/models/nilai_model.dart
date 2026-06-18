class NilaiModel {
  final int id;
  final String mataKuliah;
  final String kodeMatKul;
  final double nilaiTugas;
  final double nilaiUts;
  final double nilaiUas;
  final double nilaiAkhir;
  final int semester;

  NilaiModel({
    required this.id,
    required this.mataKuliah,
    required this.kodeMatKul,
    required this.nilaiTugas,
    required this.nilaiUts,
    required this.nilaiUas,
    required this.nilaiAkhir,
    required this.semester,
  });

  factory NilaiModel.fromJson(Map<String, dynamic> json) {
    return NilaiModel(
      id: json['id'],
      mataKuliah: json['mata_kuliah']?['nama'] ?? '-',
      kodeMatKul: json['mata_kuliah']?['kode'] ?? '-',
      nilaiTugas: double.tryParse(json['nilai_tugas'].toString()) ?? 0,
      nilaiUts: double.tryParse(json['nilai_uts'].toString()) ?? 0,
      nilaiUas: double.tryParse(json['nilai_uas'].toString()) ?? 0,
      nilaiAkhir: double.tryParse(json['nilai_akhir'].toString()) ?? 0,
      semester: int.tryParse(json['semester'].toString()) ?? 0,
    );
  }

  String get gradeLetter {
    if (nilaiAkhir >= 85) return 'A';
    if (nilaiAkhir >= 75) return 'B';
    if (nilaiAkhir >= 60) return 'C';
    if (nilaiAkhir >= 50) return 'D';
    return 'E';
  }
}
