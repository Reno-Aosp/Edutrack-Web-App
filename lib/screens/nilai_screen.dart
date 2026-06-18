import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';
import '../models/nilai_model.dart';

class NilaiScreen extends StatefulWidget {
  const NilaiScreen({super.key});

  @override
  State<NilaiScreen> createState() => _NilaiScreenState();
}

class _NilaiScreenState extends State<NilaiScreen> {
  List<NilaiModel> _nilaiList = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNilai();
  }

  Future<void> _loadNilai() async {
    final data = await ApiService.getNilai();
    setState(() {
      _nilaiList = data;
      _isLoading = false;
    });
  }

  Color _getGradeColor(double nilai) {
    if (nilai >= 85) return Colors.green;
    if (nilai >= 75) return Colors.blue;
    if (nilai >= 60) return Colors.orange;
    return Colors.red;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF0F7),
      appBar: AppBar(
        backgroundColor: const Color(0xFFE91E8C),
        foregroundColor: Colors.white,
        title: Text(
          'Nilai Saya',
          style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
        ),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFE91E8C)),
            )
          : _nilaiList.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.grade_outlined,
                    size: 80,
                    color: Colors.pink.shade200,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Belum ada data nilai',
                    style: GoogleFonts.poppins(color: Colors.grey),
                  ),
                ],
              ),
            )
          : Column(
              children: [
                // Summary card
                Container(
                  margin: const EdgeInsets.all(16),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFE91E8C), Color(0xFF5C1033)],
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _summaryItem(
                        'Total MK',
                        _nilaiList.length.toString(),
                        Icons.book,
                      ),
                      _summaryItem(
                        'Rata-rata',
                        (_nilaiList.fold(0.0, (sum, n) => sum + n.nilaiAkhir) /
                                _nilaiList.length)
                            .toStringAsFixed(1),
                        Icons.bar_chart,
                      ),
                      _summaryItem(
                        'Tertinggi',
                        _nilaiList
                            .map((n) => n.nilaiAkhir)
                            .reduce((a, b) => a > b ? a : b)
                            .toStringAsFixed(0),
                        Icons.emoji_events,
                      ),
                    ],
                  ),
                ),

                // List nilai
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _nilaiList.length,
                    itemBuilder: (context, index) {
                      final n = _nilaiList[index];
                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.pink.shade50,
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Row(
                            children: [
                              // Grade
                              Container(
                                width: 56,
                                height: 56,
                                decoration: BoxDecoration(
                                  color: _getGradeColor(
                                    n.nilaiAkhir,
                                  ).withOpacity(0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: Center(
                                  child: Text(
                                    n.gradeLetter,
                                    style: TextStyle(
                                      fontSize: 22,
                                      fontWeight: FontWeight.bold,
                                      color: _getGradeColor(n.nilaiAkhir),
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      n.mataKuliah,
                                      style: GoogleFonts.poppins(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 14,
                                        color: const Color(0xFF5C1033),
                                      ),
                                    ),
                                    Text(
                                      '${n.kodeMatKul} · Semester ${n.semester}',
                                      style: GoogleFonts.poppins(
                                        color: Colors.grey,
                                        fontSize: 11,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        _chip(
                                          'Tugas',
                                          n.nilaiTugas.toStringAsFixed(0),
                                        ),
                                        const SizedBox(width: 6),
                                        _chip(
                                          'UTS',
                                          n.nilaiUts.toStringAsFixed(0),
                                        ),
                                        const SizedBox(width: 6),
                                        _chip(
                                          'UAS',
                                          n.nilaiUas.toStringAsFixed(0),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              Column(
                                children: [
                                  Text(
                                    n.nilaiAkhir.toStringAsFixed(1),
                                    style: TextStyle(
                                      fontSize: 24,
                                      fontWeight: FontWeight.bold,
                                      color: _getGradeColor(n.nilaiAkhir),
                                    ),
                                  ),
                                  Text(
                                    'Akhir',
                                    style: GoogleFonts.poppins(
                                      color: Colors.grey,
                                      fontSize: 11,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
    );
  }

  Widget _summaryItem(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 20),
        const SizedBox(height: 4),
        Text(
          value,
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: GoogleFonts.poppins(color: Colors.white70, fontSize: 11),
        ),
      ],
    );
  }

  Widget _chip(String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFFF0A8D0).withOpacity(0.3),
        borderRadius: BorderRadius.circular(8),
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
