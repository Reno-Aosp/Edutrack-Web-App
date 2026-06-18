import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../models/absensi_model.dart';
import '../models/matkul_model.dart';

// ── SISTEM PAKAR: Rekomendasi Status Kehadiran ────────────────────────────
class SistemPakarAbsensi {
  // Knowledge Base: aturan-aturan (IF-THEN)
  static const int batasAlphaKritis = 3;
  static const int batasAlphaMaksimal = 5;
  static const double batasMinKehadiran = 0.75;

  static Map<String, dynamic> diagnosa({
    required List<AbsensiModel> riwayat,
    required int matkulId,
    required String keteranganInput,
  }) {
    // Filter riwayat untuk matkul ini
    final riwayatMatkul = riwayat.where((a) => a.matkulId == matkulId).toList();

    final total = riwayatMatkul.length;
    final alphaCount = riwayatMatkul.where((a) => a.status == 'alpha').length;
    final hadirCount = riwayatMatkul.where((a) => a.status == 'hadir').length;
    final persentaseHadir = total == 0 ? 1.0 : hadirCount / total;

    // NLP sederhana: analisis kata kunci di keterangan
    final ket = keteranganInput.toLowerCase();
    String statusRekomendasi = 'hadir';
    String alasan = '';
    String levelWarning = 'normal'; // normal, warning, danger

    // Rule 1: Deteksi kata kunci sakit
    if (ket.contains('sakit') ||
        ket.contains('demam') ||
        ket.contains('flu') ||
        ket.contains('opname') ||
        ket.contains('rawat') ||
        ket.contains('dokter')) {
      statusRekomendasi = 'sakit';
      alasan = 'Keterangan mengandung kata kunci kondisi kesehatan.';
    }
    // Rule 2: Deteksi kata kunci izin
    else if (ket.contains('izin') ||
        ket.contains('keperluan') ||
        ket.contains('keluarga') ||
        ket.contains('urusan') ||
        ket.contains('tugas luar') ||
        ket.contains('dispensasi')) {
      statusRekomendasi = 'izin';
      alasan = 'Keterangan mengandung kata kunci keperluan/izin resmi.';
    }

    // Rule 3: Cek level bahaya alpha
    if (alphaCount >= batasAlphaMaksimal) {
      levelWarning = 'danger';
    } else if (alphaCount >= batasAlphaKritis) {
      levelWarning = 'warning';
    }

    // Rule 4: Cek persentase kehadiran
    String pesanKehadiran = '';
    if (persentaseHadir < batasMinKehadiran && total > 0) {
      pesanKehadiran =
          'Kehadiran ${(persentaseHadir * 100).toStringAsFixed(0)}% — '
          'di bawah batas minimum 75%!';
      if (levelWarning == 'normal') levelWarning = 'warning';
    }

    return {
      'statusRekomendasi': statusRekomendasi,
      'alasan': alasan,
      'levelWarning': levelWarning,
      'alphaCount': alphaCount,
      'persentaseHadir': persentaseHadir,
      'pesanKehadiran': pesanKehadiran,
      'totalMatkul': total,
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
  List<MatkulModel> _matkulList = [];
  bool _isLoading = true;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadAbsensiAndMatkul();
  }

  Future<void> _loadAbsensiAndMatkul() async {
    try {
      print('[AbsensiScreen] Starting load...');
      final absensiData = await ApiService.getAbsensi();
      print('[AbsensiScreen] Loaded absensi: ${absensiData.length} records');

      final profileData = await ApiService.getProfile();
      print('[AbsensiScreen] Profile nama: ${profileData?.name}');
      print(
        '[AbsensiScreen] Profile mahasiswa ID: ${profileData?.mahasiswa?.id}',
      );

      String? mahasiswaId;
      if (profileData?.mahasiswa?.id != null) {
        mahasiswaId = profileData!.mahasiswa!.id.toString();
      }
      print('[AbsensiScreen] Mahasiswa ID: $mahasiswaId');

      final matkulData = await ApiService.getMatakuliah(
        mahasiswaId: mahasiswaId,
      );
      print('[AbsensiScreen] Loaded matkul: ${matkulData.length} records');
      if (matkulData.isNotEmpty) {
        print('[AbsensiScreen] First matkul: ${matkulData.first.nama}');
      }

      setState(() {
        _absensiList = absensiData;
        _matkulList = matkulData;
        _isLoading = false;
      });
    } catch (e) {
      print('[AbsensiScreen] Error loading data: $e');
      setState(() {
        _isLoading = false;
      });
    }
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

  void _showInputAbsensi() {
    int? selectedMatkulId;
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
            if (selectedMatkulId == null) return;
            final hasil = SistemPakarAbsensi.diagnosa(
              riwayat: _absensiList,
              matkulId: selectedMatkulId!,
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
                  const SizedBox(height: 20),

                  // ── Pilih Mata Kuliah ──
                  Text(
                    'Mata Kuliah',
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF5C1033),
                    ),
                  ),
                  const SizedBox(height: 8),
                  if (_matkulList.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(12),
                        color: Colors.grey.shade50,
                      ),
                      child: Center(
                        child: _isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    Color(0xFFE91E8C),
                                  ),
                                ),
                              )
                            : Text(
                                'Tidak ada mata kuliah tersedia',
                                style: GoogleFonts.poppins(
                                  fontSize: 13,
                                  color: Colors.grey,
                                ),
                              ),
                      ),
                    )
                  else
                    DropdownButtonFormField<int>(
                      value: selectedMatkulId,
                      hint: const Text('Pilih Mata Kuliah'),
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(
                            color: Color(0xFFE91E8C),
                          ),
                        ),
                      ),
                      items: _matkulList
                          .map(
                            (mk) => DropdownMenuItem<int>(
                              value: mk.id,
                              child: Text(
                                mk.nama,
                                style: GoogleFonts.poppins(fontSize: 13),
                              ),
                            ),
                          )
                          .toList(),
                      onChanged: (val) {
                        setModalState(() => selectedMatkulId = val);
                        jalankanSistemPakar();
                      },
                    ),
                  const SizedBox(height: 16),

                  // ── Keterangan (NLP input) ──
                  Text(
                    'Keterangan',
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

                  // ── Rekomendasi Sistem Pakar ──
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
                                  'Sistem Pakar merekomendasikan: '
                                  '${rekomendasiStatus.toUpperCase()}',
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

                  // ── Warning level bahaya ──
                  if (levelWarning == 'danger')
                    _warningBox(
                      icon: Icons.dangerous_rounded,
                      warna: Colors.red,
                      pesan:
                          'BAHAYA: Alpha sudah melebihi batas! '
                          'Risiko tidak bisa mengikuti ujian.',
                    ),
                  if (levelWarning == 'warning')
                    _warningBox(
                      icon: Icons.warning_rounded,
                      warna: Colors.orange,
                      pesan: pesanKehadiran.isNotEmpty
                          ? pesanKehadiran
                          : 'Perhatian: Alpha sudah ${SistemPakarAbsensi.batasAlphaKritis}x. Jaga kehadiran!',
                    ),

                  // ── Status ──
                  Text(
                    'Status Kehadiran',
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF5C1033),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: ['hadir', 'sakit', 'izin', 'alpha'].map((s) {
                      final isSelected = selectedStatus == s;
                      return Expanded(
                        child: GestureDetector(
                          onTap: () => setModalState(() => selectedStatus = s),
                          child: Container(
                            margin: const EdgeInsets.symmetric(horizontal: 3),
                            padding: const EdgeInsets.symmetric(vertical: 10),
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
                                  size: 20,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  s[0].toUpperCase() + s.substring(1),
                                  style: GoogleFonts.poppins(
                                    fontSize: 11,
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

                  // ── Submit ──
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: () async {
                        if (selectedMatkulId == null) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Pilih mata kuliah dulu!'),
                            ),
                          );
                          return;
                        }
                        final success = await ApiService.inputAbsensi(
                          mahasiswaId: widget.mahasiswaId,
                          matkulId: selectedMatkulId!,
                          tanggal: DateFormat(
                            'yyyy-MM-dd',
                          ).format(DateTime.now()),
                          status: selectedStatus,
                          keterangan: keteranganController.text,
                        );
                        if (mounted) {
                          Navigator.pop(ctx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                success
                                    ? 'Absensi berhasil dicatat!'
                                    : 'Gagal input absensi!',
                              ),
                              backgroundColor: success
                                  ? Colors.green
                                  : Colors.red,
                            ),
                          );
                          if (success) _loadAbsensiAndMatkul();
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
    int hadir = _absensiList.where((a) => a.status == 'hadir').length;
    int sakit = _absensiList.where((a) => a.status == 'sakit').length;
    int izin = _absensiList.where((a) => a.status == 'izin').length;
    int alpha = _absensiList.where((a) => a.status == 'alpha').length;

    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFE91E8C),
        foregroundColor: Colors.white,
        title: Text(
          'Absensi',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
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
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showInputAbsensi,
        backgroundColor: const Color(0xFFE91E8C),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: Text(
          'Input Absensi',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFE91E8C)),
            )
          : TabBarView(
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
                              style: GoogleFonts.poppins(color: Colors.grey),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
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
                                  borderRadius: BorderRadius.circular(20),
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

                // Tab Rekap
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      // Pie/Summary
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFFE91E8C), Color(0xFF5C1033)],
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
                              mainAxisAlignment: MainAxisAlignment.spaceAround,
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
                      // Progress bar
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
    );
  }

  Widget _rekapItem(String label, int count, Color color) {
    return Column(
      children: [
        Text(
          count.toString(),
          style: TextStyle(
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
                '$count/${total}',
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
