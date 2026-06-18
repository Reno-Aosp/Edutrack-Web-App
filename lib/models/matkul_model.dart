class MatkulModel {
  final int id;
  final String nama;
  final String kode;
  final int sks;

  MatkulModel({
    required this.id,
    required this.nama,
    required this.kode,
    required this.sks,
  });

  factory MatkulModel.fromJson(Map<String, dynamic> json) {
    return MatkulModel(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? '',
      kode: json['kode'] ?? '',
      sks: json['sks'] ?? 0,
    );
  }
}
