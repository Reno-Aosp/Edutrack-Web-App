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

class RapotNilaiItem {
  final String mataKuliah;
  final String kodeMatKul;
  final int sks;
  final double nilaiTugas;
  final double nilaiUts;
  final double nilaiUas;
  final double nilaiAkhir;
  final String gradeLetter;
  final String semester;
  final int hadir;
  final int totalPertemuan;

  RapotNilaiItem({
    required this.mataKuliah,
    required this.kodeMatKul,
    required this.sks,
    required this.nilaiTugas,
    required this.nilaiUts,
    required this.nilaiUas,
    required this.nilaiAkhir,
    required this.gradeLetter,
    required this.semester,
    required this.hadir,
    required this.totalPertemuan,
  });

  factory RapotNilaiItem.fromJson(Map<String, dynamic> json) {
    return RapotNilaiItem(
      mataKuliah: json['matkul'] ?? '-',
      kodeMatKul: json['kode'] ?? '-',
      sks: json['sks'] ?? 0,
      nilaiTugas: (json['nilai_tugas'] ?? 0).toDouble(),
      nilaiUts: (json['nilai_uts'] ?? 0).toDouble(),
      nilaiUas: (json['nilai_uas'] ?? 0).toDouble(),
      nilaiAkhir: (json['nilai_akhir'] ?? 0).toDouble(),
      gradeLetter: json['grade'] ?? '-',
      semester: json['semester'] ?? '-',
      hadir: json['hadir'] ?? 0,
      totalPertemuan: json['total_pertemuan'] ?? 0,
    );
  }
}

class RapotResponse {
  final MahasiswaModel? mahasiswa;
  final List<RapotNilaiItem> nilai;
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
          .map((e) => RapotNilaiItem.fromJson(e))
          .toList(),
      ipk: (json['ipk'] ?? 0).toDouble(),
      totalSks: json['total_sks'] ?? 0,
    );
  }
}
