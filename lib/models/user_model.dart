class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String? profilePhoto;
  final MahasiswaModel? mahasiswa;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.profilePhoto,
    this.mahasiswa,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? 'mahasiswa',
      profilePhoto: json['profile_photo'],
      mahasiswa: json['mahasiswa'] != null
          ? MahasiswaModel.fromJson(json['mahasiswa'])
          : null,
    );
  }
}

class MahasiswaModel {
  final int id;
  final String nama;
  final String nim;
  final String prodi;
  final String angkatan;

  MahasiswaModel({
    required this.id,
    required this.nama,
    required this.nim,
    required this.prodi,
    required this.angkatan,
  });

  factory MahasiswaModel.fromJson(Map<String, dynamic> json) {
    return MahasiswaModel(
      id: json['id'],
      nama: json['nama'] ?? '',
      nim: json['nim'] ?? '',
      prodi: json['prodi'] ?? '',
      angkatan: json['angkatan'].toString(),
    );
  }
}
