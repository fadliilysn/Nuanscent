# TECHNICAL_SPEC.md

# Nuanscent — Technical Specification

## 1. Architecture Overview

The application uses a separated frontend and backend inside one monorepo.

```text
scentmatch-local/
├── backend/                  # Laravel 12.x REST API + Filament 5.x Admin
├── frontend/                 # React + TypeScript + Vite + Tailwind
├── docs/
└── AGENTS.md
```

### Runtime Architecture
```text
React Frontend  -> Laravel 12.x REST API -> PostgreSQL
                         |
                         └-> Filament 5.x Admin Panel
```

---

## 2. Locked Tech Stack

- Backend: **Laravel 12.x REST API**
- Database: **PostgreSQL**
- Frontend: **React + TypeScript + Vite**
- Styling: **Tailwind CSS**
- Admin Panel: **Filament 5.x**
- API Style: **REST JSON**
- Public user authentication: **not required for MVP**
- Admin authentication: handled by Laravel/Filament.

---

## 3. Local Development Model

### Expected local processes
- Backend Laravel server.
- Frontend Vite dev server.
- PostgreSQL database.

Example URLs:
- Frontend: `http://localhost:5173`
- Backend API: `http://127.0.0.1:8000/api`
- Admin: `http://127.0.0.1:8000/admin`

### Typical commands
Backend:
```bash
cd backend
php artisan serve
```

Frontend:
```bash
cd frontend
npm run dev
```

---

## 4. Backend Modules

### 4.1. Core Domains
1. Brand
2. Perfume
3. Perfume Variant
4. Aroma Category
5. Aroma Tag
6. Notes
7. Occasion / Usage
8. Guide / Glossary
9. Recommendation Engine
10. Admin Management

### 4.2. Backend Design Principles
- Keep controllers thin.
- Put recommendation logic in a dedicated service.
- Use Form Requests for validation.
- Use Eloquent relationships properly.
- Use API Resources when shaping output becomes non-trivial.
- Keep business logic out of frontend.

---

## 5. Recommended API Endpoints

### 5.1. Public endpoints
```text
GET    /api/brands
GET    /api/brands/{slug}
GET    /api/perfumes
GET    /api/perfumes/{slug}
GET    /api/aroma-categories
GET    /api/aroma-tags
GET    /api/notes
GET    /api/occasions
GET    /api/guides
GET    /api/guides/{slug}
POST   /api/recommendations
```

### 5.2. Admin endpoints
Use Filament Resources where possible instead of building unnecessary custom admin APIs.

---

## 6. Database Schema — Conceptual

### 6.1. `brands`
- `id`
- `name`
- `slug`
- `description` nullable
- `official_website` nullable
- `logo_url` nullable
- timestamps

### 6.2. `perfumes`
- `id`
- `brand_id`
- `name`
- `slug`
- `short_description` nullable
- `official_description` nullable
- `concentration` nullable
- `volume_ml` nullable
- `price_min` nullable
- `price_max` nullable
- `image_url` nullable
- `marketed_gender` nullable
- `intensity` nullable
- `main_aroma_category_id` nullable
- `source_url` nullable
- `source_name` nullable
- `last_verified_at` nullable
- `data_status` enum-like string: `draft`, `reviewed`, `published`
- timestamps

`price_min` and `price_max` are the perfume-level public price range used by catalog filtering and recommendation budget matching. When a perfume has records in `perfume_variants`, these fields are aggregate values recomputed from variant prices. When a perfume has no variants, they remain the legacy/fallback perfume-level price fields.

`volume_ml` remains available as a legacy/fallback single-volume field while variants are introduced.

### 6.2.1. `perfume_variants`
- `id`
- `perfume_id`
- `label` nullable
- `volume_ml` nullable
- `price` nullable
- timestamps

Perfume variants represent purchasable options under a single perfume record, such as different bottle sizes. Variants must not create duplicate perfume catalog records or separate detail pages.

### 6.3. `aroma_categories`
- `id`
- `name`
- `slug`
- `description` nullable

Initial locked categories:
1. Fresh / Clean
2. Sweet / Gourmand
3. Floral
4. Woody / Earthy
5. Warm / Amber / Spicy
6. Musky / Powdery / Soft

### 6.4. `aroma_tags`
- `id`
- `name`
- `slug`
- `description` nullable
- `is_polarizing` boolean default false

### 6.5. `perfume_aroma_tag`
- `perfume_id`
- `aroma_tag_id`

### 6.6. `notes`
- `id`
- `name`
- `slug`
- `description_simple` nullable
- `note_family` nullable

### 6.7. `perfume_notes`
- `perfume_id`
- `note_id`
- `position` string:
  - top
  - middle
  - base
  - unspecified

### 6.8. `occasions`
- `id`
- `name`
- `slug`
- `description` nullable

### 6.9. `perfume_occasion`
- `perfume_id`
- `occasion_id`

### 6.10. `guides`
- `id`
- `title`
- `slug`
- `excerpt` nullable
- `body`
- `status`: `draft` or `published`
- `published_at` nullable
- timestamps

---

## 7. Recommendation Service Placement

Recommended service:
```text
backend/app/Services/RecommendationService.php
```

Responsibilities:
- validate normalized recommendation inputs after Form Request,
- query published perfumes,
- score candidates,
- compute blind-buy caution,
- generate matched reasons,
- return sorted recommendations.

Do not place scoring logic directly in controller methods.

---

## 8. Frontend Route Structure

```text
/
/quiz
/recommendations
/perfumes
/perfumes/:slug
/brands
/brands/:slug
/guides
/guides/:slug
/glossary
```

---

## 9. Frontend Components — Suggested

- `Navbar`
- `Footer`
- `HeroSection`
- `BentoFeatureGrid`
- `QuizStepper`
- `QuizOptionCard`
- `RecommendationResultCard`
- `BlindBuyCautionBadge`
- `MatchReasonList`
- `PerfumeCard`
- `FilterSidebar`
- `FilterDrawerMobile`
- `PerfumeDetailHeader`
- `NotesPyramid`
- `GuideCard`
- `EmptyState`
- `LoadingState`

---

## 10. Filtering Requirements

Perfume catalog should support filtering by:
- brand,
- aroma category,
- tags where useful,
- occasion,
- price range,
- intensity if data exists,
- marketed gender if data exists.

Use pagination for catalog responses.

---

## 11. Deployment Direction for Future Reference

MVP-friendly public deployment can be separated:
- Frontend React: static deployment provider.
- Backend Laravel: PHP-capable hosting service/container.
- PostgreSQL: managed PostgreSQL.

Deployment is not part of the initial foundation unless requested.

---

## 12. Testing Expectations

### Backend minimum
- Recommendation service unit/integration tests.
- API validation tests for recommendation endpoint.
- Filtering endpoint tests if implemented.

### Key recommendation test cases
- exact aroma/occasion/budget match,
- avoided tag penalty/exclusion,
- missing optional attribute handling,
- blind-buy caution calculation.

---

## 13. Technical Non-Goals for MVP

- Microservices.
- Event-driven architecture.
- GraphQL.
- Real-time features.
- Multi-tenant architecture.
- Complex user auth system.
- Automated scraping pipeline connected to public request flow.
