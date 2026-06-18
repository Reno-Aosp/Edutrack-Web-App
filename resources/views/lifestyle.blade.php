<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Gaya Hidup</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Space+Grotesk:wght@400;600;700&display=swap');

        :root {
            --ink: #5C1033;
            --muted: #8a4b66;
            --accent: #E91E8C;
            --accent-2: #FF6EC7;
            --surface: #ffffff;
            --bg-1: #FDE8F2;
            --bg-2: #FFF0F7;
            --border: #F0A8D0;
            --shadow: 0 20px 45px rgba(233, 30, 140, 0.16);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 32px;
            font-family: 'Space Grotesk', sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 10% 15%, rgba(255, 110, 199, 0.18), transparent 55%),
                        radial-gradient(circle at 90% 85%, rgba(233, 30, 140, 0.18), transparent 55%),
                        linear-gradient(135deg, var(--bg-1), var(--bg-2));
            min-height: 100vh;
        }

        .shell {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            gap: 28px;
            align-items: start;
        }

        .hero {
            background: var(--surface);
            border-radius: 24px;
            padding: 32px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            animation: rise 0.6s ease-out;
        }

        .hero::after {
            content: "";
            position: absolute;
            top: -50px; right: -30px;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(233, 30, 140, 0.18), transparent 70%);
            border-radius: 50%;
        }

        .hero h1 {
            font-family: 'Fraunces', serif;
            font-size: 2.5rem;
            margin: 0 0 12px;
        }

        .hero p {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.6;
        }

        .hero ul { margin: 0; padding-left: 18px; color: var(--muted); }
        .hero li { margin-bottom: 8px; }

        /* ── BACK BUTTON ── */
        .btn-back-assessment {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 22px;
            padding: 9px 18px;
            border-radius: 999px;
            border: 1.5px solid var(--border);
            background: transparent;
            color: var(--muted);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-back-assessment:hover {
            background: var(--bg-1);
            border-color: var(--accent);
            color: var(--accent);
        }

        /* ── AUTOFILL BADGE ── */
        .autofill-badge {
            display: none;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding: 10px 14px;
            background: rgba(233, 30, 140, 0.07);
            border: 1px solid rgba(233, 30, 140, 0.2);
            border-radius: 12px;
            font-size: 0.85rem;
            color: var(--accent);
            font-weight: 600;
            animation: rise 0.4s ease-out;
        }
        .autofill-badge.show { display: flex; }
        .autofill-badge span { color: var(--muted); font-weight: 400; }

        /* ── AUTOFILLED INPUT HIGHLIGHT ── */
        input.autofilled {
            border-color: var(--accent) !important;
            background: rgba(233, 30, 140, 0.04);
        }

        .panel {
            background: var(--surface);
            border-radius: 24px;
            padding: 32px;
            box-shadow: var(--shadow);
            animation: rise 0.6s ease-out;
        }

        .panel h2 { margin: 0 0 20px; font-size: 1.5rem; }

        form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 6px;
            animation: slide 0.5s ease-out;
            animation-delay: var(--delay);
            animation-fill-mode: both;
        }

        .field.full { grid-column: 1 / -1; }

        label { font-weight: 600; }

        input[type="number"] {
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(233, 30, 140, 0.16);
        }

        .hint { font-size: 0.85rem; color: var(--muted); }

        .cta {
            border: none;
            border-radius: 999px;
            padding: 14px 22px;
            background: linear-gradient(120deg, var(--accent), #d81b60);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .cta:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(233, 30, 140, 0.2); }
        .cta[disabled] { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

        .spinner {
            width: 18px; height: 18px;
            border: 3px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        .result {
            margin-top: 18px;
            border-radius: 16px;
            padding: 18px;
            background: rgba(233, 30, 140, 0.08);
            border: 1px solid rgba(233, 30, 140, 0.18);
            display: none;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .result strong {
            font-size: 1.5rem;
            color: var(--accent);
            text-transform: capitalize;
        }

        .status { font-size: 0.95rem; color: var(--muted); }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slide { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 980px) {
            body { padding: 24px; }
            .shell { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .hero, .panel { padding: 24px; }
            .hero h1 { font-size: 2.1rem; }
            form { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <h1>Rekomendasi Gaya Hidup</h1>
            <p>Seimbangkan waktu tidur, belajar, dan waktu layar dengan kebiasaan sehat. Masukkan rutinitas harianmu untuk mendapatkan rekomendasi gaya hidup yang akurat.</p>
            <ul>
                <li>Waktu tidur, belajar, dan layar</li>
                <li>Waktu bermain game dan media sosial</li>
                <li>Skor kesehatan mental dan tingkat burnout</li>
                <li>Olahraga dan asupan kafein</li>
            </ul>
            <a class="btn-back-assessment" href="LifestyleAssessment.html">
                ← Isi kuesioner dulu
            </a>
        </section>

        <section class="panel">
            <h2>Masukkan rutinitas harianmu</h2>

            <!-- Badge muncul kalau skor dari kuesioner -->
            <div class="autofill-badge" id="autofillBadge">
                ✅ Skor kesehatan mental &amp; burnout
                <span>sudah diisi otomatis dari kuesioner</span>
            </div>

            <form id="lifestyleForm">
                <div class="field" style="--delay: 0s;">
                    <label for="sleep_hours">Jam tidur (per malam)</label>
                    <input type="number" id="sleep_hours" name="sleep_hours" step="0.1" min="0" max="24" required>
                    <div class="hint">Rata-rata jam tidur di malam hari.</div>
                </div>
                <div class="field" style="--delay: 0.05s;">
                    <label for="study_hours">Jam belajar (per hari)</label>
                    <input type="number" id="study_hours" name="study_hours" step="0.1" min="0" max="24" required>
                    <div class="hint">Rata-rata waktu belajar yang fokus.</div>
                </div>
                <div class="field" style="--delay: 0.1s;">
                    <label for="gaming_hours">Jam bermain game (per hari)</label>
                    <input type="number" id="gaming_hours" name="gaming_hours" step="0.1" min="0" max="24" required>
                    <div class="hint">Total waktu bermain game dalam sehari.</div>
                </div>
                <div class="field" style="--delay: 0.15s;">
                    <label for="social_media_hours">Jam media sosial (per hari)</label>
                    <input type="number" id="social_media_hours" name="social_media_hours" step="0.1" min="0" max="24" required>
                    <div class="hint">Waktu yang dihabiskan untuk aplikasi sosial.</div>
                </div>
                <div class="field" style="--delay: 0.2s;">
                    <label for="mental_health_score">Skor kesehatan mental (0-100)</label>
                    <input type="number" id="mental_health_score" name="mental_health_score" min="0" max="100" required>
                    <div class="hint">Dihitung otomatis dari kuesioner WHO-5.</div>
                </div>
                <div class="field" style="--delay: 0.25s;">
                    <label for="burnout_level">Tingkat burnout (0-100)</label>
                    <input type="number" id="burnout_level" name="burnout_level" min="0" max="100" required>
                    <div class="hint">Dihitung otomatis dari kuesioner OLBI.</div>
                </div>
                <div class="field" style="--delay: 0.3s;">
                    <label for="exercise_hours">Jam olahraga (per minggu)</label>
                    <input type="number" id="exercise_hours" name="exercise_hours" step="0.1" min="0" max="168" required>
                    <div class="hint">Total waktu aktivitas fisik setiap minggu.</div>
                </div>
                <div class="field" style="--delay: 0.35s;">
                    <label for="caffeine_intake">Asupan kafein (mg per hari)</label>
                    <input type="number" id="caffeine_intake" name="caffeine_intake" min="0" required>
                    <div class="hint">Kopi, teh, atau minuman berenergi.</div>
                </div>
                <div class="field" style="--delay: 0.4s;">
                    <label for="screen_time">Waktu layar (jam per hari)</label>
                    <input type="number" id="screen_time" name="screen_time" step="0.1" min="0" max="24" required>
                    <div class="hint">Total waktu menatap layar dalam sehari.</div>
                </div>
                <div class="field full" style="--delay: 0.45s;">
                    <button class="cta" type="submit" id="submitBtn">
                        <span id="submitBtnText">Dapatkan rekomendasi</span>
                        <span class="spinner" id="spinner"></span>
                    </button>
                </div>
            </form>

            <div class="result" id="result" aria-live="polite">
                <div>
                    <div class="status">Hasil Rekomendasi</div>
                    <strong id="resultValue">--</strong>
                </div>
                <div class="status" id="resultNote"></div>
            </div>
        </section>
    </div>

    <script>
        // ── AUTO-FILL dari kuesioner ──────────────────────────
        // Baca URL params dulu, fallback ke localStorage
        const params       = new URLSearchParams(window.location.search);
        const mhParam      = params.get('mentalHealth');
        const burnParam    = params.get('burnout');
        const mhScore      = mhParam   ?? localStorage.getItem('ls_mentalHealth');
        const burnScore    = burnParam ?? localStorage.getItem('ls_burnout');

        window.addEventListener('DOMContentLoaded', () => {
            let autoFilled = false;

            if (mhScore !== null && mhScore !== '') {
                const f = document.getElementById('mental_health_score');
                f.value = mhScore;
                f.classList.add('autofilled');
                autoFilled = true;
            }

            if (burnScore !== null && burnScore !== '') {
                const f = document.getElementById('burnout_level');
                f.value = burnScore;
                f.classList.add('autofilled');
                autoFilled = true;
            }

            if (autoFilled) {
                document.getElementById('autofillBadge').classList.add('show');
            }
        });

        // ── FORM SUBMIT ───────────────────────────────────────
        const form          = document.getElementById('lifestyleForm');
        const submitBtn     = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const spinner       = document.getElementById('spinner');
        const result        = document.getElementById('result');
        const resultValue   = document.getElementById('resultValue');
        const resultNote    = document.getElementById('resultNote');
        const endpoint      = '/api/lifestyle-suggestion';

        const setLoading = (isLoading) => {
            submitBtn.disabled = isLoading;
            spinner.style.display = isLoading ? 'inline-block' : 'none';
            submitBtnText.textContent = isLoading ? 'Memproses...' : 'Dapatkan rekomendasi';
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setLoading(true);
            result.style.display = 'none';
            resultNote.textContent = '';

            const formData = new FormData(form);
            const data = Object.fromEntries(
                Array.from(formData.entries()).map(([key, value]) => [key, Number(value)])
            );

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.error || `API error: ${response.status}`);
                }

                const suggestion = payload.suggestion_label;
                if (!suggestion) throw new Error('Tidak ada rekomendasi yang dikembalikan.');

                resultValue.textContent = String(suggestion);
                resultNote.textContent  = 'Perubahan kecil yang konsisten akan berdampak besar dari waktu ke waktu.';
                result.style.display    = 'flex';
            } catch (error) {
                resultValue.textContent = 'Error';
                resultNote.textContent  = error.message;
                result.style.display    = 'flex';
            } finally {
                setLoading(false);
            }
        });
    </script>
</body>
</html>