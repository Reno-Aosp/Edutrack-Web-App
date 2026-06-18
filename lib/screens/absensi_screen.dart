import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../models/absensi_model.dart';

class SistemPakarAbsensi {
  static const int batasAlphaKritis = 3;
  static const int batasAlphaMaksimal = 5;
  static const double batasMinKehadiran = 0.75;

  static Map<String, dynamic> diagnosa({
    required List<AbsensiModel> riwayat,
    required int matkulId,
    required String keteranganInput,
  }) {
    final riwayatMatkul = riwayat.where((a) => a.matkulId == matkulId).toList();
    final total = riwayatMatkul.length;
    final alphaCount = riwayatMatkul.where((a) => a.status == 'alpha').length;
    final hadirCount = riwayatMatkul.where((a) => a.status == 'hadir').length;
    final persentaseHadir = total == 0 ? 1.0 : hadirCount / total;

    final ket = keteranganInput.toLowerCase();
    String statusRekomendasi = 'hadir';
    String alasan = '';
    String levelWarning = 'normal';

    if (ket.contains('sakit') ||
        ket.contains('demam') ||
        ket.contains('flu') ||
        ket.contains('opname') ||
        ket.contains('rawat') ||
        ket.contains('dokter')) {
      statusRekomendasi = 'sakit';
      alasan = 'Keterangan mengandung kata kunci kondisi kesehatan.';
    } else if (ket.contains('izin') ||
        ket.contains('keperluan') ||
        ket.contains('keluarga') ||
        ket.contains('urusan') ||
        ket.contains('tugas luar') ||
        ket.contains('dispensasi')) {
      statusRekomendasi = 'izin';
      alasan = 'Keterangan mengandung kata kunci keperluan/izin resmi.';
    }

    if (alphaCount >= batasAlphaMaksimal) {
      levelWarning = 'danger';
    } else if (alphaCount >= batasAlphaKritis) {
      levelWarning = 'warning';
    }

    String pesanKehadiran = '';
    if (persentaseHadir < batasMinKehadiran && total > 0) {
      pesanKehadiran =
          'Kehadiran ${(persentaseHadir * 100).toStringAsFixed(0)}% — di bawah 75%!';
      if (levelWarning == 'normal') levelWarning = 'warning';
    }

    return {
      'statusRekomendasi': statusRekomendasi,
      'alasan': alasan,
      'levelWarning': levelWarning,
      'alphaCount': alphaCount,
      'pesanKehadiran': pesanKehadiran,
    };
  }
}

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
      final absensi = await ApiService.getAbsensi();
      final sesi = await ApiService.getSesiAktif();
      if (!mounted) return;
      setState(() {
        _absensiList = absensi;
        _sesiAktif = _filterSesiMasihBuka(sesi); // ✅ filter jam
        _isLoading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // ✅ Filter sesi yang jam tutupnya belum lewat
  List<Map<String, dynamic>> _filterSesiMasihBuka(
    List<Map<String, dynamic>> sesi,
  ) {
    return sesi.where((s) {
      if (s['jam_tutup'] == null || s['jam_tutup'].toString().isEmpty)
        return true;
      try {
        final now = TimeOfDay.now();
        final parts = s['jam_tutup'].toString().split(':');
        final tutup = TimeOfDay(
          hour: int.parse(parts[0]),
          minute: int.parse(parts[1]),
        );
        final nowMin = now.hour * 60 + now.minute;
        final tutupMin = tutup.hour * 60 + tutup.minute;
        return nowMin <= tutupMin;
      } catch (_) {
        return true;
      }
    }).toList();
  }

  Future<void> _cekSesiAktif() async {
    try {
      final sesi = await ApiService.getSesiAktif();
      final sesiValid = _filterSesiMasihBuka(sesi);
      if (!mounted) return;
      final sebelumnya = _sesiAktif.length;
      setState(() => _sesiAktif = sesiValid);

      if (sesiValid.length > sebelumnya) {
        final s = sesiValid.last;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '🔔 Sesi ${s['matkul_nama']} baru dibuka!',
              style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
            ),
            backgroundColor: const Color(0xFFE91E8C),
            duration: const Duration(seconds: 4),
          ),
        );
      }
      if (sesiValid.length < sebelumnya) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '🔒 Sesi absensi telah ditutup.',
              style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
            ),
            backgroundColor: Colors.grey.shade700,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (_) {}
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
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
    switch (status.toLowerCase()) {
      case 'hadir':
        return Icons.check_circle_rounded;
      case 'sakit':
        return Icons.local_hospital_rounded;
      case 'izin':
        return Icons.info_rounded;
      case 'alpha':
        return Icons.cancel_rounded;
      default:
        return Icons.help_rounded;
    }
  }

  // ── Modal Input Absensi ──────────────────────────────────────────────────
  void _showInputAbsensi({required Map<String, dynamic> sesiDipilih}) {
    Map<String, dynamic> selectedSesi = sesiDipilih;
    String selectedStatus = 'hadir';
    String rekomendasiStatus = 'hadir';
    String alasanRekomendasi = '';
    String levelWarning = 'normal';
    String pesanKehadiran = '';
    final keteranganController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          void jalankanSistemPakar() {
            final hasil = SistemPakarAbsensi.diagnosa(
              riwayat: _absensiList,
              matkulId: selectedSesi['matkul_id'],
              keteranganInput: keteranganController.text,
            );
            setModalState(() {
              rekomendasiStatus = hasil['statusRekomendasi'];
              alasanRekomendasi = hasil['alasan'];
              levelWarning = hasil['levelWarning'];
              pesanKehadiran = hasil['pesanKehadiran'];
              selectedStatus = rekomendasiStatus;
            });
          }

          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
              left: 20,
              right: 20,
              top: 20,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  Text(
                    'Input Absensi',
                    style: GoogleFonts.poppins(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF5C1033),
                    ),
                  ),
                  Text(
                    DateFormat('dd MMMM yyyy').format(DateTime.now()),
                    style: GoogleFonts.poppins(
                      color: Colors.grey,
                      fontSize: 12,
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Info sesi
                  Container(
                    padding: const EdgeInsets.all(12),
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE91E8C).withOpacity(0.08),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: const Color(0xFFE91E8C).withOpacity(0.4),
                      ),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.circle,
                          color: Color(0xFFE91E8C),
                          size: 10,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${selectedSesi['matkul_nama']} - ${selectedSesi['kelas_nama']}',
                                style: GoogleFonts.poppins(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: const Color(0xFF5C1033),
                                ),
                              ),
                              Text(
                                'Jam: ${selectedSesi['jam_buka']} - ${selectedSesi['jam_tutup'] ?? 'sekarang'}  |  '
                                'Pertemuan ke-${selectedSesi['pertemuan_ke'] ?? '-'}',
                                style: GoogleFonts.poppins(
                                  fontSize: 11,
                                  color: Colors.grey,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Keterangan
                  Text(
                    'Keterangan (opsional)',
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF5C1033),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: keteranganController,
                    onChanged: (_) => jalankanSistemPakar(),
                    decoration: InputDecoration(
                      hintText:
                          'Contoh: sakit demam, izin keperluan keluarga...',
                      hintStyle: GoogleFonts.poppins(
                        fontSize: 12,
                        color: Colors.grey,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE91E8C)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Rekomendasi sistem pakar
                  if (alasanRekomendasi.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE91E8C).withOpacity(0.08),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: const Color(0xFFE91E8C).withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.psychology_rounded,
                            color: Color(0xFFE91E8C),
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Rekomendasi: ${rekomendasiStatus.toUpperCase()}',
                                  style: GoogleFonts.poppins(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF5C1033),
                                  ),
                                ),
                                Text(
                                  alasanRekomendasi,
                                  style: GoogleFonts.poppins(
                                    fontSize: 11,
                                    color: Colors.grey,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),

                  if (levelWarning == 'danger')
                    _warningBox(
                      icon: Icons.dangerous_rounded,
                      warna: Colors.red,
                      pesan:
                          'BAHAYA: Alpha sudah melebihi batas! Risiko tidak bisa ujian.',
                    ),
                  if (levelWarning == 'warning')
                    _warningBox(
                      icon: Icons.warning_rounded,
                      warna: Colors.orange,
                      pesan: pesanKehadiran.isNotEmpty
                          ? pesanKehadiran
                          : 'Perhatian: Alpha sudah ${SistemPakarAbsensi.batasAlphaKritis}x!',
                    ),

                  // ✅ Status — HAPUS Alpha
                  Text(
                    'Status Kehadiran',
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF5C1033),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: ['hadir', 'sakit', 'izin'].map((s) {
                      // ✅ alpha dihapus
                      final isSelected = selectedStatus == s;
                      return Expanded(
                        child: GestureDetector(
                          onTap: () => setModalState(() => selectedStatus = s),
                          child: Container(
                            margin: const EdgeInsets.symmetric(horizontal: 4),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? _getStatusColor(s)
                                  : Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Column(
                              children: [
                                Icon(
                                  _getStatusIcon(s),
                                  color: isSelected
                                      ? Colors.white
                                      : Colors.grey,
                                  size: 22,
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  s[0].toUpperCase() + s.substring(1),
                                  style: GoogleFonts.poppins(
                                    fontSize: 12,
                                    color: isSelected
                                        ? Colors.white
                                        : Colors.grey,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 20),

                  // Submit
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: () async {
                        final profile = await ApiService.getProfile();
                        final mahasiswaId =
                            profile?.mahasiswa?.id ?? widget.mahasiswaId;

                        final result = await ApiService.inputAbsensi(
                          mahasiswaId: mahasiswaId,
                          matkulId: selectedSesi['matkul_id'],
                          sesiId: selectedSesi['id'],
                          status: selectedStatus,
                          keterangan: keteranganController.text,
                        );

                        if (mounted) {
                          Navigator.pop(ctx);
                          final success = result['success'] == true;
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                success
                                    ? 'Absensi berhasil dicatat!'
                                    : result['message'] ??
                                          'Gagal input absensi!',
                              ),
                              backgroundColor: success
                                  ? Colors.green
                                  : Colors.red,
                            ),
                          );
                          if (success) _loadAll();
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFE91E8C),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: Text(
                        'Simpan Absensi',
                        style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _warningBox({
    required IconData icon,
    required Color warna,
    required String pesan,
  }) {
    return Container(
      padding: const EdgeInsets.all(10),
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: warna.withOpacity(0.08),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: warna.withOpacity(0.4)),
      ),
      child: Row(
        children: [
          Icon(icon, color: warna, size: 18),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              pesan,
              style: GoogleFonts.poppins(
                fontSize: 11,
                color: warna,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final hadir = _absensiList.where((a) => a.status == 'hadir').length;
    final sakit = _absensiList.where((a) => a.status == 'sakit').length;
    final izin = _absensiList.where((a) => a.status == 'izin').length;
    final alpha = _absensiList.where((a) => a.status == 'alpha').length;

    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFE91E8C),
        foregroundColor: Colors.white,
        title: Text(
          'Absensi',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadAll),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(text: 'Riwayat'),
            Tab(text: 'Rekap'),
          ],
        ),
      ),
      // ✅ Hapus FAB Input Absensi
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFE91E8C)),
            )
          : Column(
              children: [
                // ── Banner sesi aktif — klik untuk absen ──────────────────
                if (_sesiAktif.isNotEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                    decoration: const BoxDecoration(color: Color(0xFFE91E8C)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(
                              Icons.notifications_active,
                              color: Colors.white,
                              size: 16,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              'Ketuk untuk absen:',
                              style: GoogleFonts.poppins(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        ..._sesiAktif.map(
                          (s) => GestureDetector(
                            onTap: () => _showInputAbsensi(sesiDipilih: s),
                            child: Container(
                              margin: const EdgeInsets.only(top: 4),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.2),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                children: [
                                  const Icon(
                                    Icons.touch_app,
                                    color: Colors.white,
                                    size: 16,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '${s['matkul_nama']} - ${s['kelas_nama']}',
                                          style: GoogleFonts.poppins(
                                            color: Colors.white,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                        Text(
                                          '${s['jam_buka']} s/d ${s['jam_tutup'] ?? 'sekarang'}  |  Pertemuan ke-${s['pertemuan_ke'] ?? '-'}',
                                          style: GoogleFonts.poppins(
                                            color: Colors.white70,
                                            fontSize: 11,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const Icon(
                                    Icons.arrow_forward_ios,
                                    color: Colors.white70,
                                    size: 14,
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                    color: Colors.grey.shade100,
                    child: Row(
                      children: [
                        const Icon(
                          Icons.info_outline,
                          color: Colors.grey,
                          size: 16,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Tidak ada sesi absensi aktif',
                          style: GoogleFonts.poppins(
                            color: Colors.grey,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),

                Expanded(
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      // Tab Riwayat
                      _absensiList.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.calendar_today_outlined,
                                    size: 80,
                                    color: Colors.pink.shade200,
                                  ),
                                  const SizedBox(height: 16),
                                  Text(
                                    'Belum ada data absensi',
                                    style: GoogleFonts.poppins(
                                      color: Colors.grey,
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              color: const Color(0xFFE91E8C),
                              onRefresh: _loadAll,
                              child: ListView.builder(
                                padding: const EdgeInsets.all(16),
                                itemCount: _absensiList.length,
                                itemBuilder: (context, index) {
                                  final a = _absensiList[index];
                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 10),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(14),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.pink.shade50,
                                          blurRadius: 6,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: ListTile(
                                      leading: Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: _getStatusColor(
                                            a.status,
                                          ).withOpacity(0.1),
                                          shape: BoxShape.circle,
                                        ),
                                        child: Icon(
                                          _getStatusIcon(a.status),
                                          color: _getStatusColor(a.status),
                                        ),
                                      ),
                                      title: Text(
                                        a.mataKuliah,
                                        style: GoogleFonts.poppins(
                                          fontWeight: FontWeight.bold,
                                          color: const Color(0xFF5C1033),
                                          fontSize: 13,
                                        ),
                                      ),
                                      subtitle: Text(
                                        a.tanggal,
                                        style: GoogleFonts.poppins(
                                          color: Colors.grey,
                                          fontSize: 11,
                                        ),
                                      ),
                                      trailing: Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 10,
                                          vertical: 5,
                                        ),
                                        decoration: BoxDecoration(
                                          color: _getStatusColor(
                                            a.status,
                                          ).withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(
                                            20,
                                          ),
                                          border: Border.all(
                                            color: _getStatusColor(a.status),
                                          ),
                                        ),
                                        child: Text(
                                          a.status.toUpperCase(),
                                          style: TextStyle(
                                            color: _getStatusColor(a.status),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 11,
                                          ),
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),

                      // Tab Rekap
                      SingleChildScrollView(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [
                                    Color(0xFFE91E8C),
                                    Color(0xFF5C1033),
                                  ],
                                ),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Column(
                                children: [
                                  Text(
                                    'Total Pertemuan',
                                    style: GoogleFonts.poppins(
                                      color: Colors.white70,
                                      fontSize: 13,
                                    ),
                                  ),
                                  Text(
                                    _absensiList.length.toString(),
                                    style: GoogleFonts.poppins(
                                      color: Colors.white,
                                      fontSize: 40,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceAround,
                                    children: [
                                      _rekapItem('Hadir', hadir, Colors.green),
                                      _rekapItem('Sakit', sakit, Colors.orange),
                                      _rekapItem('Izin', izin, Colors.blue),
                                      _rekapItem('Alpha', alpha, Colors.red),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 20),
                            _progressBar(
                              'Hadir',
                              hadir,
                              _absensiList.length,
                              Colors.green,
                            ),
                            _progressBar(
                              'Sakit',
                              sakit,
                              _absensiList.length,
                              Colors.orange,
                            ),
                            _progressBar(
                              'Izin',
                              izin,
                              _absensiList.length,
                              Colors.blue,
                            ),
                            _progressBar(
                              'Alpha',
                              alpha,
                              _absensiList.length,
                              Colors.red,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _rekapItem(String label, int count, Color color) {
    return Column(
      children: [
        Text(
          count.toString(),
          style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        Text(
          label,
          style: GoogleFonts.poppins(color: Colors.white70, fontSize: 12),
        ),
      ],
    );
  }

  Widget _progressBar(String label, int count, int total, Color color) {
    final percent = total == 0 ? 0.0 : count / total;
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF5C1033),
                ),
              ),
              Text(
                '$count/$total',
                style: GoogleFonts.poppins(color: Colors.grey, fontSize: 12),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: percent,
              backgroundColor: color.withOpacity(0.1),
              valueColor: AlwaysStoppedAnimation<Color>(color),
              minHeight: 10,
            ),
          ),
        ],
      ),
    );
  }
}
