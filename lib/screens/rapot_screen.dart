import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';
import '../models/rapot_model.dart';

class RapotScreen extends StatefulWidget {
  const RapotScreen({super.key});

  @override
  State<RapotScreen> createState() => _RapotScreenState();
}

class _RapotScreenState extends State<RapotScreen> {
  RapotResponse? _rapot;
  bool _isLoading = true;
  String? _selectedSemester;
  List<String> _semesterList = [];

  @override
  void initState() {
    super.initState();
    _loadRapot();
  }

  // ================================
  // FIX FILTER SEMESTER
  // ================================
  Future<void> _loadRapot({String? semester}) async {
    setState(() => _isLoading = true);

    // Ambil semua data dulu untuk daftar semester
    final allData = await ApiService.getRapot(semester: null);

    // Ambil data sesuai filter
    final filtered = semester != null
        ? await ApiService.getRapot(semester: semester)
        : allData;

    if (!mounted) return;

    setState(() {
      _rapot = filtered;

      // Selalu update daftar semester dari semua data
      if (allData != null) {
        final semesters = allData.nilai.map((n) => n.semester).toSet().toList();

        semesters.sort();
        _semesterList = semesters;
      }

      _isLoading = false;
    });
  }

  Color _gradeColor(String grade) {
    switch (grade) {
      case 'A':
        return Colors.green;
      case 'B':
        return Colors.blue;
      case 'C':
        return Colors.orange;
      case 'D':
        return Colors.deepOrange;
      default:
        return Colors.red;
    }
  }

  @override
  Widget build(BuildContext context) {
    final mahasiswa = _rapot?.mahasiswa;
    final List<RapotNilaiItem> nilaiList = _rapot?.nilai ?? [];
    final ipk = _rapot?.ipk ?? 0;
    final totalSks = _rapot?.totalSks ?? 0;

    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),
      appBar: AppBar(
        title: Text(
          'Rapot Akademik',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFFE91E8C),
        iconTheme: const IconThemeData(color: Colors.white),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFE91E8C)),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ================================
                  // FILTER SEMESTER
                  // ================================
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.grey.withValues(alpha: 0.1),
                          blurRadius: 8,
                        ),
                      ],
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        isExpanded: true,
                        hint: Text(
                          'Semua Semester',
                          style: GoogleFonts.poppins(),
                        ),
                        value: _selectedSemester,
                        items: [
                          DropdownMenuItem<String>(
                            value: null,
                            child: Text(
                              'Semua Semester',
                              style: GoogleFonts.poppins(),
                            ),
                          ),
                          ..._semesterList.map(
                            (s) => DropdownMenuItem<String>(
                              value: s,
                              child: Text(s, style: GoogleFonts.poppins()),
                            ),
                          ),
                        ],
                        onChanged: (val) {
                          setState(() {
                            _selectedSemester = val;
                          });

                          _loadRapot(semester: val);
                        },
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // ================================
                  // INFO MAHASISWA
                  // ================================
                  if (mahasiswa != null)
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFFE91E8C), Color(0xFF5C1033)],
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            mahasiswa.nama,
                            style: GoogleFonts.poppins(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                          Text(
                            mahasiswa.nim,
                            style: GoogleFonts.poppins(
                              color: Colors.white70,
                              fontSize: 13,
                            ),
                          ),
                          Text(
                            mahasiswa.prodi,
                            style: GoogleFonts.poppins(
                              color: Colors.white70,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 12),

                          Row(
                            children: [
                              _infoChip('IPK', ipk.toStringAsFixed(2)),
                              const SizedBox(width: 8),
                              _infoChip('Total SKS', totalSks.toString()),
                              const SizedBox(width: 8),
                              _infoChip(
                                'Mata Kuliah',
                                nilaiList.length.toString(),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                  const SizedBox(height: 16),

                  Text(
                    'Daftar Nilai',
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF5C1033),
                      fontSize: 15,
                    ),
                  ),

                  const SizedBox(height: 8),

                  // ================================
                  // LIST NILAI
                  // ================================
                  nilaiList.isEmpty
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(32),
                            child: Column(
                              children: [
                                const Icon(
                                  Icons.inbox_outlined,
                                  size: 60,
                                  color: Colors.grey,
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  'Belum ada nilai',
                                  style: GoogleFonts.poppins(
                                    color: Colors.grey,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      : Container(
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.grey.withValues(alpha: 0.1),
                                blurRadius: 8,
                              ),
                            ],
                          ),
                          child: Column(
                            children: nilaiList.asMap().entries.map((entry) {
                              final i = entry.key;
                              final n = entry.value;

                              return Container(
                                decoration: BoxDecoration(
                                  border: i < nilaiList.length - 1
                                      ? const Border(
                                          bottom: BorderSide(
                                            color: Color(0xFFFDE8F2),
                                          ),
                                        )
                                      : null,
                                ),
                                padding: const EdgeInsets.all(14),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            n.mataKuliah,
                                            style: GoogleFonts.poppins(
                                              fontWeight: FontWeight.bold,
                                              color: const Color(0xFF5C1033),
                                              fontSize: 13,
                                            ),
                                          ),

                                          Text(
                                            '${n.kodeMatKul} · ${n.semester}',
                                            style: GoogleFonts.poppins(
                                              color: Colors.grey,
                                              fontSize: 11,
                                            ),
                                          ),

                                          const SizedBox(height: 4),

                                          Row(
                                            children: [
                                              _nilaiChip(
                                                'T',
                                                n.nilaiTugas.toStringAsFixed(0),
                                              ),
                                              const SizedBox(width: 4),
                                              _nilaiChip(
                                                'UTS',
                                                n.nilaiUts.toStringAsFixed(0),
                                              ),
                                              const SizedBox(width: 4),
                                              _nilaiChip(
                                                'UAS',
                                                n.nilaiUas.toStringAsFixed(0),
                                              ),
                                            ],
                                          ),

                                          const SizedBox(height: 4),

                                          if (n.totalPertemuan > 0)
                                            Text(
                                              'Kehadiran: ${n.hadir}/${n.totalPertemuan} '
                                              '(${(n.hadir / n.totalPertemuan * 100).toStringAsFixed(0)}%)',
                                              style: GoogleFonts.poppins(
                                                fontSize: 10,
                                                color:
                                                    n.hadir /
                                                            n.totalPertemuan >=
                                                        0.75
                                                    ? Colors.green
                                                    : Colors.red,
                                              ),
                                            ),
                                        ],
                                      ),
                                    ),

                                    Column(
                                      children: [
                                        Container(
                                          width: 44,
                                          height: 44,
                                          decoration: BoxDecoration(
                                            color: _gradeColor(
                                              n.gradeLetter,
                                            ).withValues(alpha: 0.15),
                                            shape: BoxShape.circle,
                                          ),
                                          child: Center(
                                            child: Text(
                                              n.gradeLetter,
                                              style: GoogleFonts.poppins(
                                                fontWeight: FontWeight.bold,
                                                color: _gradeColor(
                                                  n.gradeLetter,
                                                ),
                                                fontSize: 18,
                                              ),
                                            ),
                                          ),
                                        ),

                                        const SizedBox(height: 4),

                                        Text(
                                          n.nilaiAkhir.toStringAsFixed(1),
                                          style: GoogleFonts.poppins(
                                            fontSize: 11,
                                            color: Colors.grey,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ),
                ],
              ),
            ),
    );
  }

  Widget _infoChip(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        '$label: $value',
        style: GoogleFonts.poppins(
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _nilaiChip(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: const Color(0xFFFDE8F2),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        '$label: $value',
        style: GoogleFonts.poppins(
          fontSize: 10,
          color: const Color(0xFF5C1033),
        ),
      ),
    );
  }
}
