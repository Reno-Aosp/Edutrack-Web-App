<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Asesmen Gaya Hidup</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --pink-primary: #d81b60;
      --pink-dark:    #880e4f;
      --pink-mid:     #e91e63;
      --pink-light:   #fce4ec;
      --pink-soft:    #f8bbd0;
      --pink-bg:      #fdf0f5;
      --white:        #ffffff;
      --text-dark:    #1a1a2e;
      --text-mid:     #5a3a4a;
      --text-soft:    #a07080;
      --shadow:       0 4px 24px rgba(216,27,96,0.12);
      --radius:       20px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Space Grotesk', sans-serif;
      background: linear-gradient(160deg, #fce4ec 0%, #fdf0f5 45%, #f3e5f5 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 16px 60px;
      color: var(--text-dark);
    }

    /* ── HEADER ── */
    .header {
      width: 100%;
      max-width: 700px;
      background: linear-gradient(135deg, var(--pink-primary), var(--pink-dark));
      border-radius: var(--radius);
      padding: 32px 36px;
      margin-bottom: 28px;
      color: var(--white);
      position: relative;
      overflow: hidden;
      animation: fadeDown 0.6s ease both;
      box-shadow: 0 8px 32px rgba(136,14,79,0.3);
    }
    .header::after {
      content: "";
      position: absolute;
      top: -40px; right: -40px;
      width: 160px; height: 160px;
      background: rgba(255,255,255,0.08);
      border-radius: 50%;
    }
    .header::before {
      content: "";
      position: absolute;
      bottom: -30px; left: -20px;
      width: 100px; height: 100px;
      background: rgba(255,255,255,0.05);
      border-radius: 50%;
    }
    .header-badge {
      display: inline-block;
      background: rgba(255,255,255,0.2);
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.4px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 999px;
      margin-bottom: 12px;
    }
    .header h1 {
      font-family: 'Fraunces', serif;
      font-size: clamp(24px, 4vw, 36px);
      line-height: 1.2;
      margin-bottom: 8px;
      position: relative;
    }
    .header p {
      font-size: 14px;
      line-height: 1.6;
      opacity: 0.88;
      max-width: 480px;
      position: relative;
    }

    /* ── PROGRESS ── */
    .progress-wrap {
      width: 100%;
      max-width: 700px;
      margin-bottom: 24px;
      animation: fadeDown 0.6s 0.1s ease both;
    }
    .progress-labels {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: var(--text-soft);
      margin-bottom: 8px;
      font-weight: 600;
    }
    .progress-bar-track {
      height: 6px;
      background: var(--pink-soft);
      border-radius: 99px;
      overflow: hidden;
    }
    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--pink-primary), var(--pink-dark));
      border-radius: 99px;
      transition: width 0.5s cubic-bezier(0.4,0,0.2,1);
      width: 0%;
    }

    /* ── SECTION CARD ── */
    .section-card {
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 32px 36px;
      width: 100%;
      max-width: 700px;
      margin-bottom: 20px;
      animation: fadeUp 0.5s ease both;
      display: none;
    }
    .section-card.active { display: block; }

    .section-tag {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 99px;
      margin-bottom: 10px;
    }
    .tag-who    { background: #fce4ec; color: var(--pink-primary); }
    .tag-burnout { background: #fce4ec; color: var(--pink-dark); }

    .section-title {
      font-family: 'Fraunces', serif;
      font-size: 22px;
      margin-bottom: 6px;
      color: var(--text-dark);
    }
    .section-sub {
      font-size: 13.5px;
      color: var(--text-mid);
      margin-bottom: 8px;
      line-height: 1.55;
    }
    .section-ref {
      font-size: 11px;
      color: var(--text-soft);
      margin-bottom: 26px;
      font-style: italic;
      padding: 8px 12px;
      background: var(--pink-light);
      border-radius: 8px;
      border-left: 3px solid var(--pink-soft);
    }

    /* ── QUESTION ── */
    .question-block {
      margin-bottom: 28px;
      padding-bottom: 28px;
      border-bottom: 1px solid #fce4ec;
    }
    .question-block:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    .q-num  { font-size: 11px; color: var(--text-soft); font-weight: 600; letter-spacing: 1px; margin-bottom: 5px; }
    .q-text { font-size: 15px; font-weight: 500; color: var(--text-dark); line-height: 1.5; margin-bottom: 16px; }

    .likert { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; }
    @media (max-width: 540px) { .likert { grid-template-columns: repeat(3, 1fr); } }

    .likert-option input { display: none; }
    .likert-option label {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
      cursor: pointer;
      padding: 10px 4px;
      border-radius: 12px;
      border: 1.5px solid #f8bbd0;
      background: #fff9fc;
      transition: all 0.2s;
      font-size: 11px;
      color: var(--text-soft);
      font-weight: 500;
      text-align: center;
      line-height: 1.3;
    }
    .likert-option label:hover {
      border-color: var(--pink-primary);
      background: #fce4ec;
      color: var(--pink-primary);
    }
    .likert-option input:checked + label {
      border-color: var(--pink-primary);
      background: linear-gradient(135deg, #fce4ec, #f8bbd0);
      color: var(--pink-dark);
      font-weight: 700;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(216,27,96,0.2);
    }
    .likert-score { font-size: 20px; font-weight: 700; line-height: 1; }

    .burnout-q .likert { grid-template-columns: repeat(4, 1fr); }

    /* ── SCORE PREVIEW ── */
    .score-preview {
      margin-top: 24px;
      padding: 16px 20px;
      background: linear-gradient(135deg, #fce4ec, #f3e5f5);
      border-radius: 12px;
      display: flex;
      align-items: center;
      gap: 16px;
      border: 1px solid #f8bbd0;
    }
    .score-preview-label { font-size: 12px; color: var(--text-soft); font-weight: 500; }
    .score-preview-value { font-family: 'Fraunces', serif; font-size: 28px; color: var(--pink-primary); line-height: 1; }
    .score-preview-desc  { font-size: 12px; color: var(--text-mid); margin-top: 2px; }
    .score-bar-mini { flex: 1; height: 8px; background: #f8bbd0; border-radius: 99px; overflow: hidden; }
    .score-bar-mini-fill {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, #880e4f, #e91e63);
      transition: width 0.4s ease;
    }

    /* ── NAVIGATION ── */
    .nav-wrap { display: flex; justify-content: space-between; align-items: center; margin-top: 28px; gap: 12px; }
    .btn-back {
      padding: 12px 24px;
      border-radius: 12px;
      border: 1.5px solid #f8bbd0;
      background: transparent;
      color: var(--text-mid);
      font-family: 'Space Grotesk', sans-serif;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-back:hover { background: #fce4ec; border-color: var(--pink-primary); }
    .btn-next {
      flex: 1;
      max-width: 300px;
      padding: 14px 28px;
      border-radius: 12px;
      border: none;
      background: linear-gradient(135deg, var(--pink-primary), var(--pink-dark));
      color: #fff;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 16px rgba(216,27,96,0.3);
    }
    .btn-next:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(216,27,96,0.4); }

    /* ── RESULT CARD ── */
    #result-card {
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 36px;
      width: 100%;
      max-width: 700px;
      text-align: center;
      display: none;
      animation: fadeUp 0.5s ease both;
    }
    .result-title { font-family: 'Fraunces', serif; font-size: 26px; margin-bottom: 6px; color: var(--text-dark); }
    .result-sub   { font-size: 14px; color: var(--text-mid); margin-bottom: 28px; }

    .result-scores { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 28px; }
    @media (max-width: 480px) { .result-scores { grid-template-columns: 1fr; } }

    .result-score-box { padding: 20px; border-radius: 14px; text-align: left; }
    .rs-who  { background: linear-gradient(135deg, #fce4ec, #f8bbd0); }
    .rs-burn { background: linear-gradient(135deg, #fce4ec, #f3e5f5); }
    .rs-label { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--text-soft); margin-bottom: 4px; }
    .rs-value { font-family: 'Fraunces', serif; font-size: 42px; line-height: 1; margin-bottom: 4px; color: var(--pink-primary); }
    .rs-interp { font-size: 13px; font-weight: 600; margin-bottom: 2px; color: var(--pink-dark); }
    .rs-ref    { font-size: 11px; color: var(--text-soft); font-style: italic; }

    .score-range-table { background: #fff9fc; border-radius: 12px; padding: 16px 20px; text-align: left; margin-bottom: 28px; border: 1px solid #fce4ec; }
    .score-range-table h3 { font-size: 13px; font-weight: 700; margin-bottom: 12px; color: var(--text-mid); }
    .range-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 12.5px; }
    .range-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .dot-red    { background: #c62828; }
    .dot-orange { background: #e65100; }
    .dot-yellow { background: #f9a825; }
    .dot-green  { background: #2e7d32; }

    .btn-submit {
      width: 100%;
      padding: 16px;
      border-radius: 14px;
      border: none;
      background: linear-gradient(135deg, var(--pink-primary), var(--pink-dark));
      color: #fff;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(216,27,96,0.35);
      transition: all 0.2s;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(216,27,96,0.45); }

    .warning-msg {
      background: #fce4ec;
      border: 1px solid #f48fb1;
      color: var(--pink-dark);
      border-radius: 10px;
      padding: 10px 16px;
      font-size: 13px;
      margin-top: 12px;
      display: none;
    }

    @keyframes fadeDown { from { opacity:0; transform:translateY(-16px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fadeUp   { from { opacity:0; transform:translateY(20px);  } to { opacity:1; transform:translateY(0); } }
  </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
  <div class="header-badge">🌿 Asesmen Gaya Hidup</div>
  <h1>Kenali kondisimu<br/>sebelum kita mulai</h1>
  <p>Jawab 11 pertanyaan singkat ini. Skor kesehatan mental & burnout-mu akan dihitung otomatis — berbasis instrumen WHO & OLBI yang telah tervalidasi secara ilmiah.</p>
</div>

<!-- PROGRESS -->
<div class="progress-wrap">
  <div class="progress-labels">
    <span id="prog-label">Bagian 1 dari 2 — Kesehatan Mental</span>
    <span id="prog-pct">0%</span>
  </div>
  <div class="progress-bar-track">
    <div class="progress-bar-fill" id="prog-fill"></div>
  </div>
</div>

<!-- BAGIAN 1: WHO-5 -->
<div class="section-card active" id="sec-who">
  <div class="section-tag tag-who">🧠 Bagian 1</div>
  <div class="section-title">Kesehatan Mental (WHO-5)</div>
  <div class="section-sub">
    Pilih seberapa sering kamu merasakan hal berikut <strong>dalam 2 minggu terakhir.</strong>
  </div>
  <div class="section-ref">
    📚 Indeks Kesejahteraan WHO · Topp CW, dkk. Psychother Psychosom. 2015;84(3):167–176. · Skor mentah 0–25 × 4 = 0–100
  </div>

  <div class="question-block who-q">
    <div class="q-num">Pertanyaan 1 dari 5</div>
    <div class="q-text">Saya merasa <strong>ceria dan bersemangat.</strong></div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="who1" value="0" id="w1_0"><label for="w1_0"><span class="likert-score">0</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="who1" value="1" id="w1_1"><label for="w1_1"><span class="likert-score">1</span>Kadang-kadang</label></div>
      <div class="likert-option"><input type="radio" name="who1" value="2" id="w1_2"><label for="w1_2"><span class="likert-score">2</span>Kurang dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who1" value="3" id="w1_3"><label for="w1_3"><span class="likert-score">3</span>Lebih dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who1" value="4" id="w1_4"><label for="w1_4"><span class="likert-score">4</span>Hampir selalu</label></div>
      <div class="likert-option"><input type="radio" name="who1" value="5" id="w1_5"><label for="w1_5"><span class="likert-score">5</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block who-q">
    <div class="q-num">Pertanyaan 2 dari 5</div>
    <div class="q-text">Saya merasa <strong>tenang dan rileks.</strong></div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="who2" value="0" id="w2_0"><label for="w2_0"><span class="likert-score">0</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="who2" value="1" id="w2_1"><label for="w2_1"><span class="likert-score">1</span>Kadang-kadang</label></div>
      <div class="likert-option"><input type="radio" name="who2" value="2" id="w2_2"><label for="w2_2"><span class="likert-score">2</span>Kurang dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who2" value="3" id="w2_3"><label for="w2_3"><span class="likert-score">3</span>Lebih dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who2" value="4" id="w2_4"><label for="w2_4"><span class="likert-score">4</span>Hampir selalu</label></div>
      <div class="likert-option"><input type="radio" name="who2" value="5" id="w2_5"><label for="w2_5"><span class="likert-score">5</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block who-q">
    <div class="q-num">Pertanyaan 3 dari 5</div>
    <div class="q-text">Saya merasa <strong>aktif dan bertenaga.</strong></div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="who3" value="0" id="w3_0"><label for="w3_0"><span class="likert-score">0</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="who3" value="1" id="w3_1"><label for="w3_1"><span class="likert-score">1</span>Kadang-kadang</label></div>
      <div class="likert-option"><input type="radio" name="who3" value="2" id="w3_2"><label for="w3_2"><span class="likert-score">2</span>Kurang dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who3" value="3" id="w3_3"><label for="w3_3"><span class="likert-score">3</span>Lebih dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who3" value="4" id="w3_4"><label for="w3_4"><span class="likert-score">4</span>Hampir selalu</label></div>
      <div class="likert-option"><input type="radio" name="who3" value="5" id="w3_5"><label for="w3_5"><span class="likert-score">5</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block who-q">
    <div class="q-num">Pertanyaan 4 dari 5</div>
    <div class="q-text">Saya <strong>bangun tidur merasa segar</strong> dan sudah beristirahat dengan baik.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="who4" value="0" id="w4_0"><label for="w4_0"><span class="likert-score">0</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="who4" value="1" id="w4_1"><label for="w4_1"><span class="likert-score">1</span>Kadang-kadang</label></div>
      <div class="likert-option"><input type="radio" name="who4" value="2" id="w4_2"><label for="w4_2"><span class="likert-score">2</span>Kurang dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who4" value="3" id="w4_3"><label for="w4_3"><span class="likert-score">3</span>Lebih dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who4" value="4" id="w4_4"><label for="w4_4"><span class="likert-score">4</span>Hampir selalu</label></div>
      <div class="likert-option"><input type="radio" name="who4" value="5" id="w4_5"><label for="w4_5"><span class="likert-score">5</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block who-q">
    <div class="q-num">Pertanyaan 5 dari 5</div>
    <div class="q-text">Keseharian saya <strong>dipenuhi hal-hal yang menarik</strong> bagi saya.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="who5" value="0" id="w5_0"><label for="w5_0"><span class="likert-score">0</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="who5" value="1" id="w5_1"><label for="w5_1"><span class="likert-score">1</span>Kadang-kadang</label></div>
      <div class="likert-option"><input type="radio" name="who5" value="2" id="w5_2"><label for="w5_2"><span class="likert-score">2</span>Kurang dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who5" value="3" id="w5_3"><label for="w5_3"><span class="likert-score">3</span>Lebih dari setengah</label></div>
      <div class="likert-option"><input type="radio" name="who5" value="4" id="w5_4"><label for="w5_4"><span class="likert-score">4</span>Hampir selalu</label></div>
      <div class="likert-option"><input type="radio" name="who5" value="5" id="w5_5"><label for="w5_5"><span class="likert-score">5</span>Selalu</label></div>
    </div>
  </div>

  <div class="score-preview" id="who-preview" style="display:none">
    <div>
      <div class="score-preview-label">Skor WHO-5 sementara</div>
      <div class="score-preview-value" id="who-score-live">—</div>
      <div class="score-preview-desc" id="who-interp-live">—</div>
    </div>
    <div class="score-bar-mini">
      <div class="score-bar-mini-fill" id="who-bar-live" style="width:0%"></div>
    </div>
  </div>

  <div class="warning-msg" id="warn-who">⚠️ Harap jawab semua 5 pertanyaan sebelum melanjutkan.</div>
  <div class="nav-wrap">
    <div></div>
    <button class="btn-next" onclick="goToBurnout()">Lanjut ke Bagian 2 →</button>
  </div>
</div>


<!-- BAGIAN 2: BURNOUT (OLBI) -->
<div class="section-card" id="sec-burnout">
  <div class="section-tag tag-burnout">🔥 Bagian 2</div>
  <div class="section-title">Tingkat Burnout (OLBI)</div>
  <div class="section-sub">
    Pilih seberapa sering kamu merasakan hal berikut <strong>dalam 1 bulan terakhir.</strong>
    <br/>Skala: 1 = Tidak pernah · 2 = Jarang · 3 = Sering · 4 = Selalu
  </div>
  <div class="section-ref">
    📚 Oldenburg Burnout Inventory (OLBI) · Demerouti E, Bakker AB. (2008). · Adaptasi 6 item untuk pelajar/mahasiswa berdasarkan dimensi Kelelahan & Ketidakterlibatan
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 1 dari 6 · Kelelahan (Exhaustion)</div>
    <div class="q-text">Saya merasa <strong>kelelahan setelah belajar atau beraktivitas</strong> seharian.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b1" value="1" id="b1_1"><label for="b1_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b1" value="2" id="b1_2"><label for="b1_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b1" value="3" id="b1_3"><label for="b1_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b1" value="4" id="b1_4"><label for="b1_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 2 dari 6 · Kelelahan (Exhaustion)</div>
    <div class="q-text">Saya sering merasa <strong>mengantuk atau lelah bahkan sebelum mulai belajar</strong> atau beraktivitas.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b2" value="1" id="b2_1"><label for="b2_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b2" value="2" id="b2_2"><label for="b2_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b2" value="3" id="b2_3"><label for="b2_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b2" value="4" id="b2_4"><label for="b2_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 3 dari 6 · Kelelahan (Exhaustion)</div>
    <div class="q-text">Saya <strong>sulit pulih dari rasa lelah</strong>, bahkan setelah tidur atau istirahat.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b3" value="1" id="b3_1"><label for="b3_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b3" value="2" id="b3_2"><label for="b3_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b3" value="3" id="b3_3"><label for="b3_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b3" value="4" id="b3_4"><label for="b3_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 4 dari 6 · Ketidakterlibatan (Disengagement)</div>
    <div class="q-text">Saya merasa <strong>kehilangan motivasi</strong> untuk belajar atau melakukan aktivitas rutin.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b4" value="1" id="b4_1"><label for="b4_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b4" value="2" id="b4_2"><label for="b4_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b4" value="3" id="b4_3"><label for="b4_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b4" value="4" id="b4_4"><label for="b4_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 5 dari 6 · Ketidakterlibatan (Disengagement)</div>
    <div class="q-text">Saya merasa <strong>tidak peduli</strong> dengan tugas, pelajaran, atau tanggung jawab saya.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b5" value="1" id="b5_1"><label for="b5_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b5" value="2" id="b5_2"><label for="b5_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b5" value="3" id="b5_3"><label for="b5_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b5" value="4" id="b5_4"><label for="b5_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="question-block burnout-q">
    <div class="q-num">Pertanyaan 6 dari 6 · Ketidakterlibatan (Disengagement)</div>
    <div class="q-text">Kegiatan sehari-hari terasa <strong>membosankan dan tidak berarti</strong> bagi saya.</div>
    <div class="likert">
      <div class="likert-option"><input type="radio" name="b6" value="1" id="b6_1"><label for="b6_1"><span class="likert-score">1</span>Tidak pernah</label></div>
      <div class="likert-option"><input type="radio" name="b6" value="2" id="b6_2"><label for="b6_2"><span class="likert-score">2</span>Jarang</label></div>
      <div class="likert-option"><input type="radio" name="b6" value="3" id="b6_3"><label for="b6_3"><span class="likert-score">3</span>Sering</label></div>
      <div class="likert-option"><input type="radio" name="b6" value="4" id="b6_4"><label for="b6_4"><span class="likert-score">4</span>Selalu</label></div>
    </div>
  </div>

  <div class="score-preview" id="burn-preview" style="display:none">
    <div>
      <div class="score-preview-label">Skor Burnout sementara</div>
      <div class="score-preview-value" id="burn-score-live">—</div>
      <div class="score-preview-desc" id="burn-interp-live">—</div>
    </div>
    <div class="score-bar-mini">
      <div class="score-bar-mini-fill" id="burn-bar-live" style="width:0%"></div>
    </div>
  </div>

  <div class="warning-msg" id="warn-burn">⚠️ Harap jawab semua 6 pertanyaan sebelum melihat hasil.</div>
  <div class="nav-wrap">
    <button class="btn-back" onclick="goToWho()">← Kembali</button>
    <button class="btn-next" onclick="showResult()">Lihat Hasil Skor →</button>
  </div>
</div>


<!-- KARTU HASIL -->
<div id="result-card">
  <div class="result-title">✅ Skor kamu sudah siap!</div>
  <div class="result-sub">Hasil asesmen berdasarkan instrumen ilmiah yang telah tervalidasi.</div>

  <div class="result-scores">
    <div class="result-score-box rs-who">
      <div class="rs-label">🧠 Skor Kesehatan Mental</div>
      <div class="rs-value" id="final-who">—</div>
      <div class="rs-interp" id="final-who-interp">—</div>
      <div class="rs-ref">WHO-5 · Topp dkk. (2015) · Batas &lt;50 = risiko</div>
    </div>
    <div class="result-score-box rs-burn">
      <div class="rs-label">🔥 Tingkat Burnout</div>
      <div class="rs-value" id="final-burn">—</div>
      <div class="rs-interp" id="final-burn-interp">—</div>
      <div class="rs-ref">OLBI · Demerouti & Bakker (2008)</div>
    </div>
  </div>

  <div class="score-range-table">
    <h3>📊 Panduan Interpretasi Skor (berdasarkan jurnal ilmiah)</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;">
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--pink-primary);margin-bottom:6px">WHO-5 (Kesehatan Mental)</div>
        <div class="range-row"><div class="range-dot dot-red"></div><strong>0–28</strong> — Sangat rendah / krisis</div>
        <div class="range-row"><div class="range-dot dot-orange"></div><strong>29–51</strong> — Rendah / perlu perhatian</div>
        <div class="range-row"><div class="range-dot dot-yellow"></div><strong>52–67</strong> — Cukup / moderat</div>
        <div class="range-row"><div class="range-dot dot-green"></div><strong>68–100</strong> — Baik / sejahtera</div>
      </div>
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--pink-dark);margin-bottom:6px">Tingkat Burnout</div>
        <div class="range-row"><div class="range-dot dot-green"></div><strong>0–25</strong> — Rendah / tidak burnout</div>
        <div class="range-row"><div class="range-dot dot-yellow"></div><strong>26–58</strong> — Sedang / waspada</div>
        <div class="range-row"><div class="range-dot dot-orange"></div><strong>59–83</strong> — Tinggi / burnout</div>
        <div class="range-row"><div class="range-dot dot-red"></div><strong>84–100</strong> — Sangat tinggi / kritis</div>
      </div>
    </div>
  </div>

  <button class="btn-submit" onclick="goToMainForm()">
    Lanjut ke Form Saran Gaya Hidup →
  </button>
</div>


<script>
  function getRadioVal(name) {
    const el = document.querySelector(`input[name="${name}"]:checked`);
    return el ? parseInt(el.value) : null;
  }
  function updateProgress(pct, label) {
    document.getElementById('prog-fill').style.width = pct + '%';
    document.getElementById('prog-pct').textContent = pct + '%';
    document.getElementById('prog-label').textContent = label;
  }
  function whoInterp(s) {
    if (s < 28) return '😔 Sangat rendah — risiko tinggi';
    if (s < 52) return '😐 Rendah — perlu perhatian';
    if (s < 68) return '🙂 Cukup — moderat';
    return '😊 Baik — sejahtera';
  }
  function burnInterp(s) {
    if (s <= 25) return '✅ Rendah — tidak burnout';
    if (s <= 58) return '⚠️ Sedang — mulai waspada';
    if (s <= 83) return '🔴 Tinggi — burnout';
    return '🚨 Sangat tinggi — kritis';
  }
  function calcWho() {
    const vals = ['who1','who2','who3','who4','who5'].map(getRadioVal);
    const answered = vals.filter(v => v !== null).length;
    if (!answered) return;
    const score = vals.reduce((a,v) => a+(v??0), 0) * 4;
    document.getElementById('who-score-live').textContent = score;
    document.getElementById('who-interp-live').textContent = whoInterp(score);
    document.getElementById('who-bar-live').style.width = score + '%';
    document.getElementById('who-preview').style.display = answered >= 3 ? 'flex' : 'none';
  }
  function calcBurn() {
    const vals = ['b1','b2','b3','b4','b5','b6'].map(getRadioVal);
    const answered = vals.filter(v => v !== null).length;
    if (!answered) return;
    const raw = vals.reduce((a,v) => a+(v??1), 0);
    const score = Math.round(((raw-6)/18)*100);
    document.getElementById('burn-score-live').textContent = score;
    document.getElementById('burn-interp-live').textContent = burnInterp(score);
    document.getElementById('burn-bar-live').style.width = score + '%';
    document.getElementById('burn-preview').style.display = answered >= 3 ? 'flex' : 'none';
  }
  function goToBurnout() {
    if (['who1','who2','who3','who4','who5'].map(getRadioVal).some(v=>v===null)) {
      document.getElementById('warn-who').style.display = 'block'; return;
    }
    document.getElementById('warn-who').style.display = 'none';
    document.getElementById('sec-who').classList.remove('active');
    document.getElementById('sec-burnout').classList.add('active');
    updateProgress(55, 'Bagian 2 dari 2 — Tingkat Burnout');
    window.scrollTo({top:0,behavior:'smooth'});
  }
  function goToWho() {
    document.getElementById('sec-burnout').classList.remove('active');
    document.getElementById('sec-who').classList.add('active');
    updateProgress(10, 'Bagian 1 dari 2 — Kesehatan Mental');
    window.scrollTo({top:0,behavior:'smooth'});
  }
  function showResult() {
    const bvals = ['b1','b2','b3','b4','b5','b6'].map(getRadioVal);
    if (bvals.some(v=>v===null)) {
      document.getElementById('warn-burn').style.display = 'block'; return;
    }
    document.getElementById('warn-burn').style.display = 'none';
    const whoScore  = ['who1','who2','who3','who4','who5'].map(getRadioVal).reduce((a,v)=>a+v,0) * 4;
    const burnScore = Math.round(((bvals.reduce((a,v)=>a+v,0)-6)/18)*100);
    document.getElementById('final-who').textContent      = whoScore;
    document.getElementById('final-who-interp').textContent  = whoInterp(whoScore);
    document.getElementById('final-burn').textContent     = burnScore;
    document.getElementById('final-burn-interp').textContent = burnInterp(burnScore);
    try {
      localStorage.setItem('ls_mentalHealth', whoScore);
      localStorage.setItem('ls_burnout', burnScore);
    } catch(e) {}
    document.getElementById('sec-burnout').classList.remove('active');
    document.getElementById('result-card').style.display = 'block';
    updateProgress(100, 'Selesai! ✓');
    window.scrollTo({top:0,behavior:'smooth'});
  }
  function goToMainForm() {
    let who = "0", burn = "0";
    try {
      who  = localStorage.getItem('ls_mentalHealth') || document.getElementById('final-who').textContent;
      burn = localStorage.getItem('ls_burnout') || document.getElementById('final-burn').textContent;
    } catch(e) {
      who = document.getElementById('final-who').textContent;
      burn = document.getElementById('final-burn').textContent;
    }
    let url = `/lifestyle?mentalHealth=${who}&burnout=${burn}`;
    window.location.href = url;
  }
  document.querySelectorAll('input[name^="who"]').forEach(el => el.addEventListener('change', calcWho));
  document.querySelectorAll('input[name^="b"]').forEach(el => el.addEventListener('change', calcBurn));
  updateProgress(5, 'Bagian 1 dari 2 — Kesehatan Mental');
</script>
</body>
</html>