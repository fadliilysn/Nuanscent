# DATA_STRATEGY.md

# ScentMatch Local — Data Collection & Curation Strategy

## 1. Core Principle

The system should use a **semi-automatic data workflow**:

- factual product data may be collected through manual entry, structured import, or limited scraping,
- recommendation-facing labels/tags must be traceable and not invented,
- the project must avoid pretending to have expert sensory certainty.

---

## 2. Initial Dataset Scope

### 2.1. Target Size
- Around **40–60 local perfumes** for MVP.

### 2.2. Recommended Initial Brands
- HMNS
- SAFF & Co.
- Mykonos
- Carl & Claire
- Kahf

These are the initial candidates because they are relatively recognizable among local perfume discussions and provide enough variety for categories and use cases.

---

## 3. Data Types

### 3.1. Factual Product Data
These may be collected from official or verifiable sources:
- perfume name,
- brand,
- concentration,
- volume,
- price/range,
- official description,
- top/middle/base notes,
- product URL,
- image URL if allowed,
- marketed gender if explicitly stated.

### 3.2. Derived Structured Data
These are used for filtering/recommendation, but must remain rule-based and traceable:
- main aroma category,
- secondary aroma tags,
- occasion tags,
- blind-buy caution inputs,
- beginner-facing summary.

These must not be guessed randomly.

---

## 4. Source Metadata Requirements

Every real perfume record should support:
- `source_url`
- `source_name`
- `last_verified_at`
- `data_status`:
  - draft,
  - reviewed,
  - published.

Optional future fields:
- reviewer note,
- data import batch.

---

## 5. Data Entry Workflow

Recommended process:
1. Choose a limited list of target perfumes.
2. Collect product facts from official sources where possible.
3. Store raw findings in CSV/JSON or manually input in admin.
4. Review and normalize text fields.
5. Map notes to tags/categories if appropriate.
6. Assign occasion tags only when supported by product descriptions or curated reasoning.
7. Mark as `reviewed` or `published`.

---

## 6. Scraping Guidelines

### 6.1. Scraping is Allowed as Internal Tooling
Scraping can help collect:
- names,
- prices,
- notes,
- product descriptions,
- simple metadata.

### 6.2. Scraping is Not a Public Runtime Feature
Do **not** scrape websites live when users open the app.

### 6.3. Avoid Overengineering
For MVP:
- prefer small source-specific parsers/import scripts,
- do not build a large universal crawler.

### 6.4. Manual Entry Is Acceptable
If a website is inconsistent or difficult to scrape reliably, structured manual entry is better than fragile automation.

---

## 7. Tagging Strategy

### 7.1. Locked Main Aroma Categories
1. Fresh / Clean
2. Sweet / Gourmand
3. Floral
4. Woody / Earthy
5. Warm / Amber / Spicy
6. Musky / Powdery / Soft

### 7.2. Example Secondary Tags
- citrus
- aquatic
- clean
- soapy
- fruity
- vanilla
- caramel
- coffee
- tea
- creamy
- rose
- jasmine
- white-floral
- cedar
- sandalwood
- vetiver
- patchouli
- amber
- spicy
- saffron
- musky
- powdery
- smoky
- leathery
- tobacco
- oud

---

## 8. Mapping Notes to Tags

Mapping helps generate initial structured signals. It is not absolute truth.

Example mapping:

| Notes | Possible Tags |
|---|---|
| lemon, bergamot, mandarin | citrus, fresh |
| aquatic, marine, ozonic | aquatic, fresh |
| vanilla, caramel, praline, tonka | sweet, gourmand |
| coffee, cacao, chocolate | gourmand |
| rose, jasmine, tuberose | floral |
| cedar, sandalwood, vetiver | woody |
| patchouli, oakmoss | earthy |
| amber, benzoin, resin | warm, amber |
| cinnamon, pepper, saffron | spicy |
| musk | musky, soft |
| powdery notes | powdery, soft |
| leather | leathery, potentially polarizing |
| tobacco | tobacco, potentially polarizing |
| oud | oud, potentially polarizing |
| smoke, incense | smoky, potentially polarizing |

---

## 9. Avoid These Mistakes

Do not:
- invent perfume facts,
- produce fake pricing,
- assign strong claims without support,
- create subjective score numbers based only on personal feeling,
- use the dataset as if it is complete or expert-reviewed when it is not.

---

## 10. Data Fields That May Be Unknown

Unknown values are acceptable.

Examples:
- intensity missing,
- price unavailable,
- image unavailable,
- notes incomplete.

The system should handle missing data gracefully instead of filling it with false information.

---

## 11. Beginner Summary Guidance

Beginner-facing descriptions should be based on available evidence and written conservatively.

Good style:
- “Profilnya mengarah ke kesan fresh dan clean.”
- “Terlihat cocok untuk pengguna yang mencari aroma manis yang lebih nyaman dipakai harian.”

Avoid:
- “Pasti paling aman buat semua orang.”
- “Dijamin tidak bikin eneg.”

---

## 12. Recommended Seed Strategy

Create seeders for:
- aroma categories,
- common aroma tags,
- occasions,
- sample/test records clearly marked as dummy if needed for development.

Real perfume seed data should only be added after verification.
