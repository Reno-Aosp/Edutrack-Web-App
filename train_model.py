"""
EduTrack Lifestyle - Decision Tree Model Training Script
Generates synthetic dataset, trains DT, evaluates & saves model.
"""

import numpy as np
import pandas as pd
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
from sklearn.preprocessing import LabelEncoder
import joblib
import random

random.seed(42)
np.random.seed(42)

# ─────────────────────────────────────────────────────────────────────────────
# 1. GENERATE SYNTHETIC DATASET
#    Features match the form: sleep_hours, study_hours, gaming_hours,
#    social_media_hours, mental_health_score, burnout_level,
#    exercise_hours, caffeine_intake, screen_time
# ─────────────────────────────────────────────────────────────────────────────

def label_lifestyle(row):
    """Deterministic labeling function based on domain knowledge."""
    risk = 0

    sleep         = row['sleep_hours']
    study         = row['study_hours']
    gaming        = row['gaming_hours']
    social_media  = row['social_media_hours']
    mental_health = row['mental_health_score']
    burnout       = row['burnout_level']
    exercise      = row['exercise_hours']
    caffeine      = row['caffeine_intake']
    screen_time   = row['screen_time']

    if sleep < 5: risk += 3
    elif sleep < 6: risk += 2
    elif sleep < 7: risk += 1
    elif sleep > 10: risk += 1

    if study > 10: risk += 2
    elif study > 8: risk += 1

    if gaming > 5: risk += 2
    elif gaming > 3: risk += 1

    if social_media > 5: risk += 2
    elif social_media > 3: risk += 1

    if mental_health < 30: risk += 4
    elif mental_health < 50: risk += 2
    elif mental_health < 65: risk += 1

    if burnout > 75: risk += 4
    elif burnout > 60: risk += 2
    elif burnout > 45: risk += 1

    if exercise < 1: risk += 2
    elif exercise < 2.5: risk += 1

    if caffeine > 400: risk += 2
    elif caffeine > 200: risk += 1

    if screen_time > 10: risk += 2
    elif screen_time > 7: risk += 1

    if risk <= 3:   return "Gaya Hidup Sehat"
    elif risk <= 6: return "Gaya Hidup Cukup Baik"
    elif risk <= 10: return "Gaya Hidup Perlu Perhatian"
    elif risk <= 15: return "Gaya Hidup Berisiko"
    else:            return "Gaya Hidup Sangat Berisiko"


N = 5000  # number of samples — bigger = more coverage

# Generate a more balanced dataset by sampling from targeted ranges
def generate_balanced(n_per_class=1000):
    rows = []

    # Class: Gaya Hidup Sehat (risk <= 3)
    for _ in range(n_per_class):
        rows.append({
            'sleep_hours':         np.random.uniform(7, 10),
            'study_hours':         np.random.uniform(2, 8),
            'gaming_hours':        np.random.uniform(0, 2),
            'social_media_hours':  np.random.uniform(0, 2),
            'mental_health_score': np.random.uniform(65, 100),
            'burnout_level':       np.random.uniform(0, 44),
            'exercise_hours':      np.random.uniform(3, 14),
            'caffeine_intake':     np.random.uniform(0, 150),
            'screen_time':         np.random.uniform(0, 6),
        })

    # Class: Gaya Hidup Cukup Baik (risk 4-6)
    for _ in range(n_per_class):
        rows.append({
            'sleep_hours':         np.random.uniform(6, 8),
            'study_hours':         np.random.uniform(3, 9),
            'gaming_hours':        np.random.uniform(1, 4),
            'social_media_hours':  np.random.uniform(1, 4),
            'mental_health_score': np.random.uniform(50, 75),
            'burnout_level':       np.random.uniform(30, 55),
            'exercise_hours':      np.random.uniform(1.5, 5),
            'caffeine_intake':     np.random.uniform(50, 250),
            'screen_time':         np.random.uniform(3, 8),
        })

    # Class: Gaya Hidup Perlu Perhatian (risk 7-10)
    for _ in range(n_per_class):
        rows.append({
            'sleep_hours':         np.random.uniform(5.5, 7.5),
            'study_hours':         np.random.uniform(5, 11),
            'gaming_hours':        np.random.uniform(2, 6),
            'social_media_hours':  np.random.uniform(2, 6),
            'mental_health_score': np.random.uniform(35, 65),
            'burnout_level':       np.random.uniform(40, 70),
            'exercise_hours':      np.random.uniform(0.5, 3),
            'caffeine_intake':     np.random.uniform(100, 350),
            'screen_time':         np.random.uniform(5, 11),
        })

    # Class: Gaya Hidup Berisiko (risk 11-15)
    for _ in range(n_per_class):
        rows.append({
            'sleep_hours':         np.random.uniform(4, 6.5),
            'study_hours':         np.random.uniform(7, 13),
            'gaming_hours':        np.random.uniform(3, 7),
            'social_media_hours':  np.random.uniform(3, 8),
            'mental_health_score': np.random.uniform(20, 50),
            'burnout_level':       np.random.uniform(55, 80),
            'exercise_hours':      np.random.uniform(0, 2),
            'caffeine_intake':     np.random.uniform(200, 500),
            'screen_time':         np.random.uniform(7, 14),
        })

    # Class: Gaya Hidup Sangat Berisiko (risk > 15)
    for _ in range(n_per_class):
        rows.append({
            'sleep_hours':         np.random.uniform(3, 5.5),
            'study_hours':         np.random.uniform(9, 14),
            'gaming_hours':        np.random.uniform(5, 10),
            'social_media_hours':  np.random.uniform(5, 10),
            'mental_health_score': np.random.uniform(0, 35),
            'burnout_level':       np.random.uniform(70, 100),
            'exercise_hours':      np.random.uniform(0, 1),
            'caffeine_intake':     np.random.uniform(350, 600),
            'screen_time':         np.random.uniform(9, 16),
        })

    return pd.DataFrame(rows)

df = generate_balanced(n_per_class=1000)
df['label'] = df.apply(label_lifestyle, axis=1)

print("=== Dataset Distribution ===")
print(df['label'].value_counts())
print()

# ─────────────────────────────────────────────────────────────────────────────
# 2. PREPARE FEATURES & TARGET
# ─────────────────────────────────────────────────────────────────────────────

FEATURES = [
    'sleep_hours', 'study_hours', 'gaming_hours', 'social_media_hours',
    'mental_health_score', 'burnout_level', 'exercise_hours',
    'caffeine_intake', 'screen_time'
]

X = df[FEATURES]
y = df['label']

# Encode labels
le = LabelEncoder()
y_enc = le.fit_transform(y)

print("=== Label Classes ===")
for i, cls in enumerate(le.classes_):
    print(f"  {i}: {cls}")
print()

# ─────────────────────────────────────────────────────────────────────────────
# 3. TRAIN / TEST SPLIT
# ─────────────────────────────────────────────────────────────────────────────

X_train, X_test, y_train, y_test = train_test_split(
    X, y_enc, test_size=0.2, random_state=42, stratify=y_enc
)

# ─────────────────────────────────────────────────────────────────────────────
# 4. TRAIN DECISION TREE
# ─────────────────────────────────────────────────────────────────────────────

dt = DecisionTreeClassifier(
    criterion='gini',
    max_depth=None,          # let tree grow as needed
    min_samples_split=4,
    min_samples_leaf=2,
    class_weight='balanced', # handle any remaining imbalance
    random_state=42
)

dt.fit(X_train, y_train)

# ─────────────────────────────────────────────────────────────────────────────
# 5. EVALUATE
# ─────────────────────────────────────────────────────────────────────────────

y_pred = dt.predict(X_test)
acc = accuracy_score(y_test, y_pred)

print(f"=== Decision Tree Accuracy ===")
print(f"  Test Accuracy : {acc * 100:.2f}%")
print()

# Cross-validation
cv_scores = cross_val_score(dt, X, y_enc, cv=5, scoring='accuracy')
print(f"=== 5-Fold Cross Validation ===")
print(f"  Scores  : {[f'{s*100:.1f}%' for s in cv_scores]}")
print(f"  Mean    : {cv_scores.mean()*100:.2f}%")
print(f"  Std Dev : ±{cv_scores.std()*100:.2f}%")
print()

print("=== Classification Report ===")
print(classification_report(y_test, y_pred, target_names=le.classes_))

print("=== Confusion Matrix ===")
print(confusion_matrix(y_test, y_pred))
print()

print(f"=== Tree Info ===")
print(f"  Max depth used : {dt.get_depth()}")
print(f"  Num leaves     : {dt.get_n_leaves()}")
print()

# ─────────────────────────────────────────────────────────────────────────────
# 6. SAVE MODEL
# ─────────────────────────────────────────────────────────────────────────────

joblib.dump(dt, 'lifestyle_model.pkl')
joblib.dump(le, 'lifestyle_label_encoder.pkl')

print("=== Model Saved ===")
print("  lifestyle_model.pkl")
print("  lifestyle_label_encoder.pkl")
