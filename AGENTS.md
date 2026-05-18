# AGENTS.md

# ScentMatch Local — Compact Project Context for Agents Code

> **Status:** Final baseline context for AI coding assistant  
> **Purpose:** This file is intentionally concise. It gives Agents the project direction, locked decisions, and the correct docs to open only when needed.  
> **Rule:** Do not expand scope, invent perfume data, or rewrite agreed decisions without explicit instruction from the user.

---

# 1. What This Project Is

**ScentMatch Local** is a web application that helps users discover **local Indonesian perfumes** based on:
- aroma preference,
- occasion/use case,
- budget,
- preferred intensity,
- comfort level for blind buy.

It is designed for:
1. **Beginners / general users** who do not understand perfume terms.
2. **Casual perfume users** who want more structured exploration.
3. **Beginner–intermediate enthusiasts** who want filters, notes, and detail pages.

The system must **reduce confusion and blind-buy risk**, not promise that a perfume will definitely match the user.

---

# 2. Read Docs Selectively to Save Tokens

Do **not** load every document for every task. Open only the relevant docs.

| Task Type | Read These Files |
|---|---|
| Understand product scope/features | `docs/PRD.md` |
| Backend/frontend/database/API work | `docs/TECHNICAL_SPEC.md` |
| Quiz/recommendation logic | `docs/RECOMMENDATION_ENGINE.md` |
| UI/landing page/components styling | `docs/UI_STYLE_GUIDE.md` |
| Data ingestion/scraping/seeding | `docs/DATA_STRATEGY.md` |
| Project phases and token-efficient work style | `docs/IMPLEMENTATION_PLAN.md` |

When the user asks to work on one specific phase, read only:
1. this `AGENTS.md`, and
2. the one or two most relevant docs.

---

# 3. Locked Product Decisions

These decisions are **final** unless the user explicitly changes them.

- [x] Focus on **local Indonesian perfumes** for MVP.
- [x] Product must be useful for **beginners** but still informative for users who already understand perfume basics.
- [x] Main differentiator: **recommendation quiz + explainable result + blind-buy guidance**.
- [x] The system is **not** an e-commerce platform.
- [x] The system does **not** guarantee that a perfume will be liked.
- [x] Initial dataset target: around **40–60 perfumes** from a limited set of known local brands.
- [x] Recommended initial brand set:
  - HMNS
  - SAFF & Co.
  - Mykonos
  - Carl & Claire
  - Kahf
- [x] MVP recommendation method: **rule-based weighted matching**, not ML/LLM ranking.
- [x] Do not create subjective manual numeric aroma scores such as `freshness_score = 4/5` based on personal guesswork.
- [x] Use **structured tags, categories, product attributes, and explainable rules**.

---

# 4. Locked Tech Stack

Do not change this stack without explicit user instruction.

- [x] **Backend:** Laravel REST API
- [x] **Database:** PostgreSQL
- [x] **Frontend:** React + TypeScript + Vite
- [x] **Styling:** Tailwind CSS
- [x] **Admin panel:** Filament
- [x] **Repo style:** simple monorepo

Recommended repository layout:

```text
scentmatch-local/
├── backend/                  # Laravel REST API + Filament
├── frontend/                 # React + TypeScript + Vite + Tailwind
├── docs/                     # Detailed specifications
└── AGENTS.md                 # Compact AI context file
```

---

# 5. MVP Scope — Build These

- [x] Landing page / homepage
- [x] Quiz recommendation flow
- [x] Recommendation result page with:
  - match percentage,
  - explainable reasons,
  - blind-buy caution label,
  - link to perfume detail.
- [x] Perfume catalog with search/filter
- [x] Perfume detail page
- [x] Beginner-friendly perfume glossary / guide content
- [x] Admin panel using Filament for managing:
  - brands,
  - perfumes,
  - notes,
  - aroma categories/tags,
  - occasions,
  - guide content.

---

# 6. Post-MVP — Do Not Build Unless Asked

- [ ] User login/public account
- [ ] Wishlist
- [ ] Saved recommendation history
- [ ] User review/rating
- [ ] Compare perfumes
- [ ] AI natural-language perfume advisor
- [ ] Machine learning recommender
- [ ] Marketplace / checkout / payment
- [ ] Automated live scraping in user-facing requests

---

# 7. Visual Direction — Locked

Public-facing UI uses:
- [x] **Soft Neo-Brutalism / Editorial Neo-Brutalism**
- [x] **Bento-grid inspired homepage layout**
- [x] **Clean editorial readability** for catalog and detail pages

Do not create:
- glassmorphism as primary style,
- generic SaaS dashboard visual,
- dark luxury fragrance aesthetic,
- over-animated gimmicky UI.

For UI tasks, read `docs/UI_STYLE_GUIDE.md` before coding.

---

# 8. Data Integrity Rules

AI must **not** invent:
- perfume names,
- notes,
- prices,
- brand claims,
- longevity/projection claims,
- product descriptions presented as factual.

Allowed when real data is not yet available:
- clear placeholder structures,
- seed files marked `DUMMY` or `EXAMPLE ONLY`,
- TODO markers,
- schema-ready implementations.

Each real perfume record should support data traceability, e.g.:
- source URL,
- source name,
- last verified date,
- draft/reviewed/published status.

For data work, read `docs/DATA_STRATEGY.md`.

---

# 9. Recommendation Logic — Core Principles

- [x] Recommendation is **rule-based weighted matching**.
- [x] Match percentage is computed dynamically from quiz input vs perfume attributes.
- [x] Output must explain *why* a perfume is recommended.
- [x] Blind-buy caution is a **heuristic**, not a certainty.
- [x] Missing data must not be fabricated; scoring should normalize or mark limitations.

For implementation details, read `docs/RECOMMENDATION_ENGINE.md`.

---

# 10. AI Workflow Rules to Save Tokens

When the user asks to implement something:
1. Work only on the requested phase/module.
2. Do not scan the entire repo unless necessary.
3. Do not rewrite unrelated files.
4. Before coding, state what files/modules will be touched.
5. After coding, summarize:
   - what changed,
   - how to run/test it,
   - what remains next.
6. Stop after the requested scope. Wait for the next instruction.

For detailed phased execution, read `docs/IMPLEMENTATION_PLAN.md`.

---

# 11. Preferred Work Style with the User

Use phased delivery:
- plan,
- implement small scope,
- review,
- continue.

Do not respond by attempting to build the entire application in one shot.

---

# 12. If Instructions Conflict

Priority order:
1. Latest explicit user instruction
2. This `AGENTS.md`
3. Relevant file in `docs/`
4. Existing codebase conventions

If changing a locked decision, explicitly say what changed and why.
