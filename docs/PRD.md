# PRD.md

# Nuanscent — Product Requirements Document

## 1. Product Summary

Nuanscent is a web application for recommending **local Indonesian perfumes** to users based on preferences that are easy to understand, especially for beginners.

The product solves a common problem:
- Many users want to buy perfume online.
- Perfume terminology is confusing.
- Notes alone are not easy to imagine.
- Users want to reduce the risk of wrong blind buy.

The product is positioned as:

> **A beginner-friendly assistant for discovering local perfumes, while still offering structured exploration for users who already understand perfume basics.**

---

## 2. Problem Statement

### 2.1. User Pain Points
1. Users do not know which local perfume to choose.
2. Users do not understand perfume terms such as:
   - top notes,
   - middle notes,
   - base notes,
   - projection,
   - sillage,
   - gourmand,
   - woody,
   - amber.
3. Users see many recommendations on social media, but information is scattered.
4. Users often ask repetitive questions:
   - “Budget segini cocok apa?”
   - “Buat kuliah yang fresh apa?”
   - “Mau yang manis tapi tidak bikin eneg.”
5. Users want to blind buy but fear choosing a perfume that feels too bold, too sweet, too smoky, or simply does not fit their use case.

### 2.2. Product Opportunity
The product can help users by:
- translating fragrance concepts into simple everyday language,
- giving guided recommendations,
- explaining recommendation reasons,
- warning when a perfume may be less safe to blind buy,
- organizing local perfume options into a clear discovery experience.

---

## 3. Product Goals

### 3.1. Primary Goals
- Help users find perfumes that better match their preferences.
- Make perfume discovery easier for beginners.
- Provide transparent recommendation explanations.
- Offer a structured catalog of local perfumes.
- Build a credible portfolio project with real product thinking.

### 3.2. Non-Goals
- Not an online store.
- Not a payment system.
- Not a marketplace.
- Not a guarantee that the user will like the perfume.
- Not an ML-heavy recommender in MVP.
- Not a global fragrance database in MVP.

---

## 4. Target Users

### 4.1. Persona A — Beginner / Awam
**Profile:**
- Interested in perfume but not knowledgeable.
- Uses simple wording like “fresh”, “wangi bersih”, “manis lembut”, “buat kuliah”.
- Often considers buying online.

**Needs:**
- Guided quiz.
- Simple explanations.
- Safer blind-buy guidance.
- Recommendations with reasons.

### 4.2. Persona B — Casual Perfume Buyer
**Profile:**
- Has tried several perfumes.
- Understands broad fragrance styles.
- Wants to explore new local options.

**Needs:**
- Catalog filters.
- Occasion and aroma filters.
- Detail page with notes.

### 4.3. Persona C — Beginner–Intermediate Enthusiast
**Profile:**
- Already knows notes/categories.
- Wants more structured discovery.

**Needs:**
- Search by brand.
- Filter by notes/tags.
- Technical product details.

### 4.4. Persona D — Admin / Content Manager
**Profile:**
- Maintains the perfume data.
- Reviews imported or manually entered data.

**Needs:**
- Efficient CRUD.
- Data publishing status.
- Source metadata.

---

## 5. Core User Journeys

### 5.1. Journey 1 — Beginner Uses Quiz
1. User opens homepage.
2. Clicks “Temukan Parfum Untukmu”.
3. Completes multi-step quiz.
4. Gets 3–5 recommendations.
5. Reads why each perfume matches.
6. Checks blind-buy caution.
7. Opens perfume detail page.

### 5.2. Journey 2 — User Browses Catalog
1. User opens catalog.
2. Applies filters:
   - budget,
   - brand,
   - aroma category,
   - occasion.
3. Opens a perfume card.
4. Reads beginner summary and technical notes.

### 5.3. Journey 3 — User Learns Terminology
1. User opens glossary/guide.
2. Reads terms such as “gourmand” or “base notes”.
3. Returns to quiz/catalog with better understanding.

---

## 6. MVP Features

### 6.1. Homepage
- Hero section.
- CTA to quiz.
- CTA to catalog.
- Simple “how it works” section.
- Aroma category overview.
- Blind-buy guidance teaser.
- Educational teaser.

### 6.2. Quiz Recommendation
Collect:
- usage/occasion,
- desired aroma impression,
- budget,
- intensity preference,
- disliked aroma types,
- blind-buy comfort level,
- optional marketed gender preference.

### 6.3. Recommendation Result Page
Each result must show:
- perfume name,
- brand,
- image if available,
- price/range if available,
- match percentage,
- recommendation reasons,
- blind-buy caution label,
- link to detail.

### 6.4. Catalog
- Search.
- Filter.
- Sorting.
- Responsive cards.

### 6.5. Perfume Detail
Two layers:
1. Beginner-friendly overview.
2. Technical details:
   - notes,
   - concentration,
   - volume,
   - tags,
   - occasion.

### 6.6. Guide / Glossary
Minimum content:
- Notes structure.
- EDT/EDP/Extrait overview if used.
- Projection, sillage, longevity.
- Basic aroma terms.
- Blind-buy explanation.
- Tips selecting a first local perfume.

### 6.7. Admin Panel
Using Filament:
- Brand CRUD.
- Perfume CRUD.
- Notes CRUD.
- Aroma category/tag CRUD.
- Occasion CRUD.
- Guide CRUD.

---

## 7. Post-MVP Features

Do not include unless explicitly requested:
- compare perfumes,
- public account/login,
- wishlist,
- saved quiz history,
- user reviews,
- AI natural language search,
- recommendation model based on machine learning,
- e-commerce functions.

---

## 8. Information Architecture

```text
Public Website
├── Home
├── Quiz Recommendation
├── Recommendation Results
├── Perfume Catalog
│   └── Perfume Detail
├── Brands
│   └── Brand Detail
├── Guides / Glossary
│   └── Guide Detail
└── Admin Panel
```

---

## 9. Product Language Principles

Use wording that is:
- clear,
- friendly,
- humble,
- explanatory.

Avoid:
- “guaranteed suitable”,
- “the best perfume for everyone”,
- exaggerated certainty.

Preferred phrasing:
- “lebih mendekati preferensimu”,
- “berpotensi sesuai”,
- “perlu dipertimbangkan”,
- “sebaiknya coba sample bila ragu”.

---

## 10. Success Criteria for MVP

The MVP is successful when:
- users can complete a quiz and get explainable recommendations,
- users can browse and filter a perfume catalog,
- perfume detail pages are understandable for beginners,
- admins can manage the data without direct DB edits,
- blind-buy caution is visible and reasoned,
- system scope remains focused and implementable.
