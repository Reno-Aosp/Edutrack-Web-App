import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../models/absensi_model.dart';

// ── SISTEM PAKAR ─────────────────────────────────────────
class SistemPakarAbsensi {
  static const int batasAlphaKritis = 3;
  static const int batasAlphaMaksimal = 5;
  static const double batasMinKehadiran = 0.75;

  static Map<String, dynamic> diagnosa({
    required List<AbsensiModel> riwayat,
    required int matkulId,
    required String keteranganInput,
  }) {
    final riwayatMatkul =
        riwayat.where((a) => a.matkulId == matkulId).toList();

    final total = riwayatMatkul.length;
    final alphaCount =
        riwayatMatkul.where((a) => a.status == 'alpha').length;
    final hadirCount =
        riwayatMatkul.where((a) => a.status == 'hadir').length;

    final persentaseHadir = total == 0 ? 1.0 : hadirCount / total;

    final ket = keteranganInput.toLowerCase();

    String statusRekomendasi = 'hadir';
    String alasan = '';
    String levelWarning = 'normal';

    if (ket.contains('sakit') ||
        ket.contains('demam') ||
        ket.contains('flu') ||
        ket.contains('dokter')) {
      statusRekomendasi = 'sakit';
      alasan = 'Terdeteksi kondisi kesehatan.';
    } else if (ket.contains('izin') ||
        ket.contains('keluarga') ||
        ket.contains('keperluan')) {
      statusRekomendasi = 'izin';
      alasan = 'Terdeteksi keperluan izin.';
    }

    if (alphaCount >= batasAlphaMaksimal) {
      levelWarning = 'danger';
    } else if (alphaCount >= batasAlphaKritis) {
      levelWarning = 'warning';
    }

    String pesanKehadiran = '';
    if (persentaseHadir < batasMinKehadiran && total > 0) {
      pesanKehadiran =
          'Kehadiran ${(persentaseHadir * 100).toStringAsFixed(0)}% (di bawah 75%)';
    }

    return {
      'statusRekomendasi': statusRekomendasi,
      'alasan': alasan,
      'levelWarning': levelWarning,
      'pesanKehadiran': pesanKehadiran,
    };
  }
}

// ── SCREEN ───────────────────────────────────────────────
class AbsensiScreen extends StatefulWidget {
  final int mahasiswaId;
  const AbsensiScreen({super.key, required this.mahasiswaId});

  @override
  State<AbsensiScreen> createState() => _AbsensiScreenState();
}

class _AbsensiScreenState extends State<AbsensiScreen>
    with SingleTickerProviderStateMixin {
  List<AbsensiModel> _absensiList = [];
  List<Map<String, dynamic>> _sesiAktif = [];
  bool _isLoading = true;
  late TabController _tabController;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadAll();

    _timer = Timer.periodic(
      const Duration(seconds: 15),
      (_) => _cekSesiAktif(),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAll() async {
    setState(() => _isLoading = true);
    try {
      final absensiData = await ApiService.getAbsensi();
      final sesiData = await ApiService.getSesiAktif();

      if (!mounted) return;

      setState(() {
        _absensiList = absensiData;
        _sesiAktif = sesiData;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _cekSesiAktif() async {
    try {
      final sesi = await ApiService.getSesiAktif();
      if (!mounted) return;

      setState(() => _sesiAktif = sesi);
    } catch (_) {}
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'hadir':
        return Colors.green;
      case 'sakit':
        return Colors.orange;
      case 'izin':
        return Colors.blue;
      case 'alpha':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'hadir':
        return Icons.check_circle;
      case 'sakit':
        return Icons.local_hospital;
      case 'izin':
        return Icons.info;
      case 'alpha':
        return Icons.cancel;
      default:
        return Icons.help;
    }
  }

  // ── MODAL ABSEN ────────────────────────────────────────
  void _showInputAbsensi({required Map<String, dynamic> sesi}) {
    String selectedStatus = 'hadir';
    final ketController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(ctx).viewInsets.bottom,
            left: 20,
            right: 20,
            top: 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Input Absensi',
                  style: GoogleFonts.poppins(
                      fontWeight: FontWeight.bold)),

              const SizedBox(height: 10),

              TextField(
                controller: ketController,
                decoration: const InputDecoration(
                  hintText: 'Keterangan...',
                ),
              ),

              const SizedBox(height: 10),

              ElevatedButton(
                onPressed: () async {
                  final profile = await ApiService.getProfile();
                  final mahasiswaId =
                      profile?.mahasiswa?.id ?? widget.mahasiswaId;

                  final result = await ApiService.inputAbsensi(
                    mahasiswaId: mahasiswaId,
                    matkulId: sesi['matkul_id'],
                    sesiId: sesi['id'],
                    status: selectedStatus,
                    keterangan: ketController.text,
                  );

                  Navigator.pop(ctx);

                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(result['success'] == true
                          ? 'Berhasil absen'
                          : 'Gagal'),
                    ),
                  );

                  _loadAll();
                },
                child: const Text('Simpan'),
              )
            ],
          ),
        );
      },
    );
  }

  // ── BUILD ──────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final hadir =
        _absensiList.where((a) => a.status == 'hadir').length;
    final sakit =
        _absensiList.where((a) => a.status == 'sakit').length;
    final izin =
        _absensiList.where((a) => a.status == 'izin').length;
    final alpha =
        _absensiList.where((a) => a.status == 'alpha').length;

    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),

      // ❌ FAB DIHAPUS TOTAL
      // floatingActionButton: null,

      appBar: AppBar(
        backgroundColor: const Color(0xFFE91E8C),
        title: Text('Absensi',
            style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Riwayat'),
            Tab(text: 'Rekap')
          ],
        ),
      ),

      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // ✅ BANNER JADI SATU2NYA AKSES ABSEN
                if (_sesiAktif.isNotEmpty)
                  Container(
                    color: Colors.pink,
                    child: Column(
                      children: _sesiAktif.map((s) {
                        return ListTile(
                          title: Text(
                            '${s['matkul_nama']} - ${s['kelas_nama']}',
                            style: const TextStyle(color: Colors.white),
                          ),
                          trailing: const Text('Absen →',
                              style: TextStyle(color: Colors.white)),
                          onTap: () => _showInputAbsensi(sesi: s),
                        );
                      }).toList(),
                    ),
                  )
                else
                  Container(
                    padding: const EdgeInsets.all(10),
                    child: const Text(
                      'Tidak ada sesi aktif',
                    ),
                  ),

                Expanded(
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      ListView.builder(
                        itemCount: _absensiList.length,
                        itemBuilder: (c, i) {
                          final a = _absensiList[i];
                          return ListTile(
                            title: Text(a.mataKuliah),
                            subtitle: Text(a.tanggal),
                            trailing: Text(a.status),
                          );
                        },
                      ),

                      Center(
                        child: Text(
                            'H:$hadir S:$sakit I:$izin A:$alpha'),
                      )
                    ],
                  ),
                )
              ],
            ),
    );
  }
}