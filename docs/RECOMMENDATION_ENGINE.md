# RECOMMENDATION_ENGINE.md

# Nuanscent — Recommendation Engine Specification

## 1. Core Decision

MVP recommendation engine uses:

> **Rule-based weighted matching with explainable output.**

It must not be converted to machine learning or LLM ranking unless the user explicitly asks for it.

---

## 2. Inputs from Quiz

The quiz should collect these inputs:

### 2.1. Required or primary inputs
- `occasion`
- `aroma_preferences[]` (1-3 desired aroma category slugs)
- `budget_range`
- `intensity_preference`
- `blind_buy_comfort`

### 2.2. Optional inputs
- `avoided_tags[]`
- `marketed_gender_preference`

---

## 3. Recommended Quiz Choices

### 3.1. Occasion
- campus/school
- office/work
- daily
- casual hangout
- date
- formal event
- evening

### 3.2. Aroma preference
Users can choose 1-3 desired aroma categories. Map beginner-friendly wording to stored single aroma categories:
- Fresh -> `fresh`
- Clean -> `clean`
- Sweet -> `sweet`
- Gourmand -> `gourmand`
- Floral -> `floral`
- Woody -> `woody`
- Earthy -> `earthy`
- Warm -> `warm`
- Amber -> `amber`
- Spicy -> `spicy`
- Musky -> `musky`
- Powdery -> `powdery`
- Soft -> `soft`

Legacy grouped slugs such as `fresh-clean`, `sweet-gourmand`, `woody-earthy`,
`warm-amber-spicy`, and `musky-powdery-soft` can remain backward-compatible
aliases for the legacy `aroma_preference` field, but new frontend choices should
send `aroma_preferences`.

### 3.3. Budget range
Example buckets:
- under 100k
- 100k–200k
- 200k–350k
- 350k–500k
- above 500k

### 3.4. Intensity preference
- soft
- medium
- strong
- no preference

### 3.5. Avoided aroma tags
Multi-select examples:
- too sweet
- smoky
- citrus-heavy
- floral-heavy
- spicy-heavy

### 3.6. Blind-buy comfort
- “Saya ingin yang cenderung aman”
- “Sedikit unik tidak masalah”
- “Saya tidak masalah dengan aroma lebih berani”

---

## 4. Candidate Selection

Initial candidates:
- only perfumes with `data_status = published`

Optional filtering before scoring:
- If perfume price data is available and clearly far outside selected budget, it may receive lower score rather than being immediately excluded.
- If avoided tags strongly conflict, either:
  - heavily penalize score, or
  - exclude if the user preference is treated as a hard rule.

The implementation should be explicit and testable.

---

## 5. Weighted Scoring Model

Recommended total weight: **100 points**.

| Criterion | Weight |
|---|---:|
| Aroma category/tag match | 40 |
| Occasion match | 25 |
| Budget match | 20 |
| Intensity match | 10 |
| Blind-buy comfort alignment | 5 |

---

## 6. Scoring Details

### 6.1. Aroma match — 40 points
Recommended structure:
- Main aroma category matches any selected category: `+25`
- Secondary tags support any selected preference: up to `+15`

Multiple selected aroma preferences must not increase aroma scoring beyond the
same 40-point maximum.

Do not create subjective arbitrary aroma scores.

### 6.2. Occasion match — 25 points
- Occasion selected by user exists in perfume occasions: `+25`

### 6.3. Budget match — 20 points
- Product price range fits selected budget: `+20`
- If price unknown, avoid false penalty. Use normalization or mark data limitation.
- If price only partially overlaps a budget bucket, optional partial scoring may be defined, but it must remain explicit.

### 6.4. Intensity match — 10 points
- Same intensity: `+10`
- If user selects “no preference”, intensity should not become a penalty.
- If intensity data is unavailable, normalize score or exclude this criterion from denominator.

### 6.5. Blind-buy comfort alignment — 5 points
This should align the user’s risk tolerance with system caution:
- user wants safe + perfume caution low -> good fit
- user is adventurous + perfume caution medium/high -> not necessarily penalized
- user wants safe + perfume caution high -> lower fit

---

## 7. Avoided Tags Handling

Avoided tags should influence recommendations clearly.

Example options:
1. **Penalty approach**
   - subtract fixed points when a perfume contains avoided tags.
2. **Hard exclude approach**
   - remove perfumes containing a strongly avoided tag.

Recommended MVP approach:
- use a **penalty approach** first,
- optionally exclude only if user preference wording is strongly definitive.

Avoided tags should also produce an explanation when relevant, e.g.:
- “Kami menurunkan kecocokan karena kamu memilih untuk menghindari aroma smoky.”

---

## 8. Score Normalization for Missing Data

Perfume data will not always be complete. Missing data must not make the system fabricate values.

Recommended approach:
- Score only against criteria that can be evaluated.
- Normalize the final match percentage relative to the available denominator.

Example:
- if intensity is missing, do not automatically score it as 0.
- denominator becomes 90 instead of 100, then convert to percentage.

The UI may optionally display:
> “Sebagian atribut produk masih terbatas, sehingga kecocokan dihitung dari data yang tersedia.”

---

## 9. Blind-Buy Caution Heuristic

### 9.1. Purpose
Blind-buy caution estimates how carefully users should approach the perfume. It is not a certainty and not a quality rating.

### 9.2. Output labels
- `Cenderung Aman`
- `Perlu Pertimbangan`
- `Sebaiknya Coba Sample Dulu`
- `Data Belum Cukup`

### 9.3. Risk indicators
Risk may increase if the perfume has clearly polarizing tags such as:
- oud,
- smoky,
- tobacco,
- leathery,
- incense,
- animalic if introduced later,
- strong intensity if verified.

### 9.4. Simple heuristic
Possible MVP implementation:
- no risk flags -> `Cenderung Aman`
- 1 risk flag -> `Perlu Pertimbangan`
- 2+ risk flags -> `Sebaiknya Coba Sample Dulu`
- too little data -> `Data Belum Cukup`

### 9.5. Required explanation
Every caution label must include reasons, for example:
- “Memiliki profil smoky/leathery yang bisa terasa lebih spesifik bagi sebagian pengguna.”
- “Data karakter produk masih terbatas.”

---

## 10. Recommendation Result Payload — Suggested Shape

```json
{
  "recommendations": [
    {
      "perfume_id": 1,
      "slug": "example-perfume",
      "name": "Example Perfume",
      "brand": "Example Brand",
      "image_url": null,
      "price_min": 150000,
      "price_max": 200000,
      "match_percentage": 87,
      "matched_reasons": [
        "Sesuai dengan preferensi aroma Fresh.",
        "Cocok untuk pemakaian daily.",
        "Masuk dalam rentang budget yang dipilih."
      ],
      "blind_buy_caution": {
        "label": "Perlu Pertimbangan",
        "reasons": [
          "Memiliki satu karakter aroma yang cenderung lebih spesifik."
        ]
      },
      "data_limitations": []
    }
  ]
}
```

This is a contract example. Do not treat the perfume names as real production data.

---

## 11. Output Rules

Recommendation results must:
- show 3–5 top candidates when available,
- provide match reasons,
- avoid overclaiming,
- not hide data uncertainty,
- remain understandable for beginners.

Recommended language:
- “lebih mendekati preferensimu”,
- “berpotensi sesuai”,
- “perlu dipertimbangkan”.

Avoid:
- “pasti cocok”,
- “jaminan aman blind buy”.

---

## 12. Testing Checklist

Create tests for:
1. exact strong match should rank higher,
2. mismatched aroma category should rank lower,
3. avoided tag should reduce score,
4. missing intensity should normalize instead of falsely penalize,
5. caution heuristic should map risk flags correctly,
6. recommendations should include reasons.
