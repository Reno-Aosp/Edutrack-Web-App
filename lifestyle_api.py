"""
EduTrack Lifestyle Suggestion API — powered by Decision Tree (85% accuracy)
Runs on port 5003, proxied by Laravel.
"""

from flask import Flask, request, jsonify
import joblib
import numpy as np
import os

app = Flask(__name__)

# Load trained model and label encoder
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
model = joblib.load(os.path.join(BASE_DIR, 'lifestyle_model.pkl'))
le    = joblib.load(os.path.join(BASE_DIR, 'lifestyle_label_encoder.pkl'))

FEATURES = [
    'sleep_hours', 'study_hours', 'gaming_hours', 'social_media_hours',
    'mental_health_score', 'burnout_level', 'exercise_hours',
    'caffeine_intake', 'screen_time'
]

DESCRIPTIONS = {
    "Gaya Hidup Sehat":             "Pertahankan kebiasaan baikmu! Tidur, olahraga, dan waktu layar sudah seimbang.",
    "Gaya Hidup Cukup Baik":        "Ada beberapa kebiasaan yang perlu diperbaiki, seperti waktu tidur atau aktivitas fisik.",
    "Gaya Hidup Perlu Perhatian":   "Kurangi waktu layar, tingkatkan tidur berkualitas, dan mulai rutinitas olahraga ringan.",
    "Gaya Hidup Berisiko":          "Burnout dan kesehatan mental memerlukan perhatian serius. Istirahat cukup dan cari dukungan.",
    "Gaya Hidup Sangat Berisiko":   "Segera ubah pola hidup. Konsultasikan kondisi mental dan fisik dengan profesional kesehatan.",
}


@app.route('/api/lifestyle-suggest', methods=['POST'])
def lifestyle_suggest():
    try:
        data = request.get_json(force=True)
        if not data:
            return jsonify({'error': 'Request body kosong atau bukan JSON.'}), 400

        # Validate required fields
        missing = [f for f in FEATURES if f not in data]
        if missing:
            return jsonify({'error': f'Field berikut wajib diisi: {", ".join(missing)}'}), 400

        # Build feature vector
        X = np.array([[float(data[f]) for f in FEATURES]])

        # Predict
        pred_enc   = model.predict(X)[0]
        pred_label = le.inverse_transform([pred_enc])[0]
        desc       = DESCRIPTIONS.get(pred_label, '')

        return jsonify({
            'suggestion_label': f"{pred_label} – {desc}",
            'label_short':      pred_label,
            'algorithm':        'Decision Tree (Gini, depth=19)',
            'accuracy':         '85.20%',
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model': 'Decision Tree', 'accuracy': '85.20%'}), 200


if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5003)
