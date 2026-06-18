import 'dart:async';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import '../models/absensi_model.dart';
import '../services/api_service.dart';

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
    String pesanKehadiran = '';

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
      alasan = 'Keterangan mengandung kata kunci izin resmi.';
    }

    if (alphaCount >= batasAlphaMaksimal) {
      levelWarning = 'danger';
    } else if (alphaCount >= batasAlphaKritis) {
      levelWarning = 'warning';
    }

    if (persentaseHadir < batasMinKehadiran && total > 0) {
      pesanKehadiran =
          'Kehadiran ${(persentaseHadir * 100).toStringAsFixed(0)}% — di bawah 75%!';
      if (levelWarning == 'normal') {
        levelWarning = 'warning';
      }
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

class _AbsensiScreenState extends State<AbsensiScreen> {
  List<AbsensiModel> _absensiList = [];
  List<Map<String, dynamic>> _sesiAktif = [];
  bool _isLoading = true;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _loadAll();
    _timer = Timer.periodic(
      const Duration(seconds: 15),
      (_) => _cekSesiAktif(),
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  String _formatJam(String? jam) {
    if (jam == null || jam.isEmpty) return '--';
    try {
      final parts = jam.split(':');
      if (parts.length >= 2) return '${parts[0]}:${parts[1]}';
      return jam;
    } catch (_) {
      return jam;
    }
  }

  Future<void> _loadAll() async {
    setState(() => _isLoading = true);
    try {
      final absensi = await ApiService.getAbsensi();
      final sesi = await ApiService.getSesiAktif();
      if (!mounted) return;
      setState(() {
        _absensiList = absensi;
        _sesiAktif = _filterSesiMasihBuka(sesi);
        _isLoading = false;
      });
    } catch (e) {
      debugPrint('LOAD ERROR: $e');
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<Map<String, dynamic>> _filterSesiMasihBuka(
    List<Map<String, dynamic>> sesi,
  ) {
    return sesi.where((s) {
      if (s['jam_tutup'] == null || s['jam_tutup'].toString().isEmpty) {
        return true;
      }
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
      if (!mounted) return;
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
      } else if (sesiValid.length < sebelumnya) {
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
    } catch (e) {
      debugPrint('CEK SESI ERROR: $e');
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

  // =========================================================
  // MODAL INPUT ABSENSI
  // =========================================================
  void _showInputAbsensi({required Map<String, dynamic> sesiDipilih}) {
    final Map<String, dynamic> selectedSesi = sesiDipilih;
    String selectedStatus = 'hadir';
    String rekomendasiStatus = 'hadir';
    String alasanRekomendasi = '';
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
                  Container(
                    padding: const EdgeInsets.all(12),
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE91E8C).withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: const Color(0xFFE91E8C).withValues(alpha: 0.4),
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
                                'Jam: ${_formatJam(selectedSesi['jam_buka'])} - ${selectedSesi['jam_tutup'] != null ? _formatJam(selectedSesi['jam_tutup']) : 'sekarang'}',
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
                      hintText: 'Contoh: sakit demam',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (alasanRekomendasi.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE91E8C).withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(10),
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
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: () async {
                        try {
                          final profile = await ApiService.getProfile();
                          final mahasiswaId = profile?.mahasiswa?.id;
                          if (!ctx.mounted) return;
                          if (mahasiswaId == null) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(
                                content: Text('Data mahasiswa tidak ditemukan'),
                                backgroundColor: Colors.red,
                              ),
                            );
                            return;
                          }
                          final result = await ApiService.inputAbsensi(
                            mahasiswaId: mahasiswaId,
                            matkulId: selectedSesi['matkul_id'],
                            sesiId: selectedSesi['id'],
                            status: selectedStatus,
                            keterangan: keteranganController.text,
                          );
                          if (!ctx.mounted) return;
                          Navigator.pop(ctx);
                          if (!mounted) return;
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                result['success'] == true
                                    ? '✅ Absensi berhasil dicatat!'
                                    : (result['message'] ??
                                          'Gagal input absensi!'),
                              ),
                              backgroundColor: result['success'] == true
                                  ? Colors.green
                                  : Colors.red,
                            ),
                          );
                          if (result['success'] == true) _loadAll();
                        } catch (e) {
                          debugPrint('ERROR ABSENSI: $e');
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Terjadi error: $e'),
                                backgroundColor: Colors.red,
                              ),
                            );
                          }
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

  // =========================================================
  // MODAL UPLOAD SURAT
  // Pakai XFile + Image.memory agar preview jalan di Web & Mobile
  // =========================================================
  void _showUploadSurat(AbsensiModel absensi) {
    XFile? selectedXFile;
    Uint8List? selectedBytes;
    bool isUploading = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) {
          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              left: 20,
              right: 20,
              top: 20,
            ),
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
                  'Upload Surat Keterangan',
                  style: GoogleFonts.poppins(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF5C1033),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${absensi.mataKuliah} · ${absensi.tanggal}',
                  style: GoogleFonts.poppins(color: Colors.grey, fontSize: 12),
                ),
                const SizedBox(height: 20),

                // ─── Area pilih foto ────────────────────────────────
                GestureDetector(
                  onTap: () async {
                    final picker = ImagePicker();
                    final picked = await picker.pickImage(
                      source: ImageSource.gallery,
                      imageQuality: 80,
                    );
                    if (picked != null) {
                      // readAsBytes() works on web & mobile
                      final bytes = await picked.readAsBytes();
                      setModalState(() {
                        selectedXFile = picked;
                        selectedBytes = bytes;
                      });
                    }
                  },
                  child: Container(
                    width: double.infinity,
                    height: 160,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: selectedBytes != null
                            ? const Color(0xFFE91E8C)
                            : Colors.grey.shade300,
                        width: selectedBytes != null ? 2 : 1,
                      ),
                    ),
                    // ── Image.memory: support web & mobile ──────────
                    child: selectedBytes != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(11),
                            child: Image.memory(
                              selectedBytes!,
                              fit: BoxFit.cover,
                            ),
                          )
                        : Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.add_photo_alternate_rounded,
                                size: 48,
                                color: Colors.grey.shade400,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'Tap untuk pilih foto surat',
                                style: GoogleFonts.poppins(
                                  color: Colors.grey,
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),

                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: (selectedXFile == null || isUploading)
                        ? null
                        : () async {
                            setModalState(() => isUploading = true);
                            final result = await ApiService.uploadFotoSurat(
                              xFile: selectedXFile!,
                              mahasiswaId: widget.mahasiswaId,
                              matkulId: absensi.matkulId,
                              tanggal: absensi.tanggal,
                              status: absensi.status,
                            );
                            if (!ctx.mounted) return;
                            Navigator.pop(ctx);
                            if (!mounted) return;
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  result['success'] == true
                                      ? '✅ Surat berhasil diupload!'
                                      : '❌ ${result['message'] ?? 'Gagal upload'}',
                                ),
                                backgroundColor: result['success'] == true
                                    ? Colors.green
                                    : Colors.red,
                              ),
                            );
                            if (result['success'] == true) _loadAll();
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFE91E8C),
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: Colors.grey.shade300,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: isUploading
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 2,
                            ),
                          )
                        : Text(
                            'Upload Surat',
                            style: GoogleFonts.poppins(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  // =========================================================
  // CARD SESI AKTIF
  // =========================================================
  Widget _buildSesiAktifCard(Map<String, dynamic> s) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        gradient: const LinearGradient(
          colors: [Color(0xFFE91E63), Color(0xFFAD1457)],
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.pink.withValues(alpha: 0.2),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            s['matkul_nama'] ?? '-',
            style: GoogleFonts.poppins(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            s['kelas_nama'] ?? '-',
            style: GoogleFonts.poppins(color: Colors.white70, fontSize: 12),
          ),
          const SizedBox(height: 12),
          Text(
            'Buka: ${_formatJam(s['jam_buka'])} | Tutup: ${s['jam_tutup'] != null ? _formatJam(s['jam_tutup']) : 'belum ditutup'} | Pertemuan ${s['pertemuan_ke'] ?? '-'}',
            style: GoogleFonts.poppins(color: Colors.white, fontSize: 11),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => _showInputAbsensi(sesiDipilih: s),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: const Color(0xFFE91E63),
              ),
              child: const Text('Isi Absensi'),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================
  // CARD RIWAYAT ABSENSI
  // =========================================================
  Widget _buildHistoryCard(AbsensiModel a) {
    final statusColor = _getStatusColor(a.status);
    final statusIcon = _getStatusIcon(a.status);
    final bolehUpload =
        a.status.toLowerCase() == 'sakit' || a.status.toLowerCase() == 'izin';
    final sudahUpload = a.fotoUrl != null && a.fotoUrl!.isNotEmpty;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(statusIcon, color: statusColor, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      a.mataKuliah,
                      style: GoogleFonts.poppins(
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                        color: const Color(0xFF5C1033),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      a.tanggal,
                      style: GoogleFonts.poppins(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    if (a.keterangan.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        a.keterangan,
                        style: GoogleFonts.poppins(
                          fontSize: 11,
                          color: Colors.grey.shade400,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  a.status.toUpperCase(),
                  style: GoogleFonts.poppins(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),

          // ─── Upload Surat ───────────────────────────────────────
          if (bolehUpload) ...[
            const SizedBox(height: 10),
            if (sudahUpload)
              Row(
                children: [
                  const Icon(
                    Icons.check_circle_rounded,
                    color: Colors.green,
                    size: 16,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Surat sudah diupload',
                      style: GoogleFonts.poppins(
                        fontSize: 12,
                        color: Colors.green,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  GestureDetector(
                    onTap: () => _showUploadSurat(a),
                    child: Text(
                      'Ganti',
                      style: GoogleFonts.poppins(
                        fontSize: 12,
                        color: const Color(0xFFE91E8C),
                        fontWeight: FontWeight.w600,
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ),
                ],
              )
            else
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => _showUploadSurat(a),
                  icon: const Icon(Icons.upload_file_rounded, size: 16),
                  label: Text(
                    'Upload Surat Keterangan',
                    style: GoogleFonts.poppins(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFE91E8C),
                    side: const BorderSide(color: Color(0xFFE91E8C)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }

  // =========================================================
  // BUILD
  // =========================================================
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F8F8),
      appBar: AppBar(
        title: Text(
          'Absensi',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF5C1033),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadAll,
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFE91E8C)),
            )
          : RefreshIndicator(
              color: const Color(0xFFE91E8C),
              onRefresh: _loadAll,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // ─── Sesi Aktif ─────────────────────────────────
                  Row(
                    children: [
                      Container(
                        width: 4,
                        height: 18,
                        decoration: BoxDecoration(
                          color: const Color(0xFFE91E8C),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Sesi Aktif',
                        style: GoogleFonts.poppins(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF5C1033),
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (_sesiAktif.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE91E8C),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            '${_sesiAktif.length}',
                            style: GoogleFonts.poppins(
                              fontSize: 11,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (_sesiAktif.isEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        vertical: 20,
                        horizontal: 16,
                      ),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Center(
                        child: Text(
                          'Tidak ada sesi aktif saat ini',
                          style: GoogleFonts.poppins(
                            color: Colors.grey,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    )
                  else
                    ..._sesiAktif.map((s) => _buildSesiAktifCard(s)),

                  const SizedBox(height: 8),

                  // ─── Riwayat Absensi ────────────────────────────
                  Row(
                    children: [
                      Container(
                        width: 4,
                        height: 18,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade400,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Riwayat Absensi',
                        style: GoogleFonts.poppins(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF5C1033),
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (_absensiList.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade400,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            '${_absensiList.length}',
                            style: GoogleFonts.poppins(
                              fontSize: 11,
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (_absensiList.isEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        vertical: 20,
                        horizontal: 16,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Center(
                        child: Text(
                          'Belum ada riwayat absensi',
                          style: GoogleFonts.poppins(
                            color: Colors.grey,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    )
                  else
                    ..._absensiList.map((a) => _buildHistoryCard(a)),

                  const SizedBox(height: 20),
                ],
              ),
            ),
    );
  }
}
