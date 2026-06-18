# 📚 EduTrack — Web & Mobile Academic Platform

A full-stack academic management platform with a **built-in AI Lifestyle Suggestion engine** powered by a **Decision Tree classifier (85.20% accuracy)**. The system is actively deployed on **AWS Lightsail** and consumed by a **Flutter mobile application**.

---

## 🌐 Live Deployment

> ✅ This project is **live and running** on AWS Lightsail.

| Resource | URL |
|---|---|
| 🌍 Live Web App | [`https://edutrack.serveblog.net/login`](https://edutrack.serveblog.net/login) |
| 🤖 AI Health Check | [`https://edutrack.serveblog.net/health`](https://edutrack.serveblog.net/health) |
| 📡 Lifestyle AI Endpoint | `POST https://edutrack.serveblog.net/api/lifestyle-suggestion` |

**Hit the health check endpoint right now to verify the server and AI model are alive:**
```bash
curl https://edutrack.serveblog.net/health
# Expected response: {"status": "ok", "model": "Decision Tree", "accuracy": "85.20%"}
```

---

## 🤖 AI Feature — Lifestyle Suggestion (Decision Tree)

The platform includes a built-in AI model that classifies a student's lifestyle into one of **5 risk categories** based on 9 behavioural input features.

### Algorithm Details
| Property | Value |
|---|---|
| **Algorithm** | Decision Tree (CART) |
| **Criterion** | Gini Impurity |
| **Training Samples** | 5,000 (balanced synthetic dataset) |
| **Test Accuracy** | **85.20%** |
| **Validation** | 5-Fold Cross Validation |
| **Framework** | scikit-learn (Python) |
| **Serving** | Flask API → proxied by Laravel |

### Input Features
The model accepts 9 student behavioural features:

| Feature | Description |
|---|---|
| `sleep_hours` | Average hours of sleep per day |
| `study_hours` | Hours spent studying per day |
| `gaming_hours` | Hours spent gaming per day |
| `social_media_hours` | Hours on social media per day |
| `mental_health_score` | Self-rated mental health (0–100) |
| `burnout_level` | Self-rated burnout level (0–100) |
| `exercise_hours` | Hours of exercise per day |
| `caffeine_intake` | Daily caffeine intake (mg) |
| `screen_time` | Total screen time per day (hours) |

### Output Categories
| Label | Meaning |
|---|---|
| 🟢 `Gaya Hidup Sehat` | Healthy Lifestyle |
| 🟡 `Gaya Hidup Cukup Baik` | Fairly Good Lifestyle |
| 🟠 `Gaya Hidup Perlu Perhatian` | Lifestyle Needs Attention |
| 🔴 `Gaya Hidup Berisiko` | At-Risk Lifestyle |
| ⛔ `Gaya Hidup Sangat Berisiko` | Critically At-Risk Lifestyle |

### Try the API Live
```bash
curl -X POST https://edutrack.serveblog.net/api/lifestyle-suggestion \
  -H "Content-Type: application/json" \
  -d '{
    "sleep_hours": 5,
    "study_hours": 10,
    "gaming_hours": 4,
    "social_media_hours": 5,
    "mental_health_score": 40,
    "burnout_level": 70,
    "exercise_hours": 0.5,
    "caffeine_intake": 300,
    "screen_time": 12
  }'
```

**Expected Response:**
```json
{
  "suggestion_label": "Gaya Hidup Berisiko – Burnout dan kesehatan mental memerlukan perhatian serius.",
  "label_short": "Gaya Hidup Berisiko",
  "algorithm": "Decision Tree (Gini, depth=19)",
  "accuracy": "85.20%"
}
```

### Key Source Files (Proof)
| File | What It Proves |
|---|---|
| [`train_model.py`](./train_model.py) | Full Decision Tree training script using `sklearn.tree.DecisionTreeClassifier` |
| [`lifestyle_api.py`](./lifestyle_api.py) | Flask API that loads the `.pkl` model and serves predictions |
| [`routes/api.php`](./routes/api.php#L21) | Laravel route that proxies the AI endpoint to the frontend |

---

## 📱 Mobile Application

The Flutter mobile app lives in the **`Mobile-Version` branch** of this repository.

> Switch branches: `git checkout Mobile-Version`

The mobile app connects directly to the **live deployed backend** — including the Lifestyle AI feature:
- `lib/screens/lifestyle_screen_mobile.dart` → Loads `https://edutrack.serveblog.net/lifestyle-assessment`
- `assets/lifestyle/LifeStyleSuggestionPage.html` → Calls `POST https://edutrack.serveblog.net/api/lifestyle-suggestion`

---

## 🗂️ Repository Structure

```
main branch          ← Laravel PHP Backend + Python AI model
Mobile-Version       ← Flutter Mobile Application
```

### Backend (`main` branch)
```
edutrack-backend/
├── app/Http/Controllers/   ← Laravel API Controllers
├── routes/api.php          ← All API routes (including AI endpoint)
├── train_model.py          ← Decision Tree training script (scikit-learn)
├── lifestyle_api.py        ← Flask server that serves the trained .pkl model
├── lifestyle_model.pkl     ← Trained Decision Tree model
└── lifestyle_label_encoder.pkl  ← Label encoder for output classes
```

---

## ⚙️ Backend Setup (Laravel)

```bash
composer install
cp .env.example .env      # Configure your database
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

To also run the Python AI model locally:
```bash
pip install flask scikit-learn joblib numpy
python lifestyle_api.py   # Starts Flask on port 5003
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend API | Laravel (PHP) |
| AI Model | Python + scikit-learn (Decision Tree) |
| AI Server | Flask |
| Mobile App | Flutter (Dart) |
| Hosting | AWS Lightsail |
| Database | MySQL | Supabase
