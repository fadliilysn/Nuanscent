# IMPLEMENTATION_PLAN.md

# Nuanscent — Phased Implementation & Token-Efficient AI Workflow

## 1. Purpose

This document defines how the project should be built **step by step** to:
- avoid uncontrolled scope growth,
- keep AI token usage lower,
- make review easier,
- prevent half-finished massive changes.

---

## 2. Core Working Principle

Always work in this order:

> **Understand → Plan → Implement one small scope → Review → Continue**

Do not ask the AI to build the entire product in one prompt.

---

## 3. Recommended Project Phases

### Phase 0 — Project Understanding
Goal:
- Read project context.
- Confirm scope.
- Do not code.

Suggested Agents prompt:
```text
Read AGENTS.md only. Do not write code yet.
Summarize your understanding of the project, final decisions, MVP scope, and stack.
Then suggest a phased implementation plan. Wait for my instruction before coding.
```

---

### Phase 1 — Monorepo Foundation
Goal:
- Create folder structure.
- Initialize backend and frontend.
- Setup basic environment files/examples.
- Add README setup notes.

Read:
- `AGENTS.md`
- `docs/TECHNICAL_SPEC.md`

Do not build:
- database schema,
- API endpoints,
- admin resources,
- frontend pages.

Suggested prompt:
```text
Read AGENTS.md and docs/TECHNICAL_SPEC.md.
Implement only Phase 1: monorepo foundation.
Create backend/frontend structure and minimal run instructions.
Do not create migrations, recommendation logic, Filament resources, or UI pages yet.
After finishing, list changed files and how to run locally.
```

---

### Phase 2 — Backend Data Model
Goal:
- Create migrations/models/relationships.
- Seed basic lookup tables.

Read:
- `AGENTS.md`
- `docs/TECHNICAL_SPEC.md`
- `docs/DATA_STRATEGY.md`

Do not build:
- recommendation service,
- public frontend,
- full guide content.

Suggested prompt:
```text
Read AGENTS.md, docs/TECHNICAL_SPEC.md, and docs/DATA_STRATEGY.md.
Implement only database models, migrations, and relationships for the MVP core entities.
Create seeders only for stable lookup data such as aroma categories, tags, and occasions.
Do not add fake real perfume records.
```

---

### Phase 3 — Filament Admin
Goal:
- Admin CRUD for core data.

Read:
- `AGENTS.md`
- `docs/TECHNICAL_SPEC.md`
- `docs/DATA_STRATEGY.md`

Build:
- Brand resource.
- Perfume resource.
- Note resource.
- Aroma category/tag resources.
- Occasion resource.
- Guide resource.

Suggested prompt:
```text
Read AGENTS.md, docs/TECHNICAL_SPEC.md, and docs/DATA_STRATEGY.md.
Implement only the Filament admin resources for the MVP data entities.
Do not build recommendation endpoints or frontend pages in this step.
```

---

### Phase 4 — Public Catalog API + Frontend Catalog
Goal:
- Expose public read APIs.
- Build catalog and detail pages.

Read:
- `AGENTS.md`
- `docs/TECHNICAL_SPEC.md`
- `docs/UI_STYLE_GUIDE.md`

Suggested prompt:
```text
Read AGENTS.md, docs/TECHNICAL_SPEC.md, and docs/UI_STYLE_GUIDE.md.
Implement public perfume catalog APIs and the corresponding frontend catalog/detail pages.
Keep the UI in Soft Neo-Brutalism style.
Do not implement quiz or recommendation engine yet.
```

---

### Phase 5 — Recommendation Engine Backend
Goal:
- Build recommendation endpoint and scoring service.

Read:
- `AGENTS.md`
- `docs/RECOMMENDATION_ENGINE.md`
- `docs/TECHNICAL_SPEC.md`

Build:
- Form Request.
- RecommendationService.
- API endpoint.
- tests.

Suggested prompt:
```text
Read AGENTS.md, docs/RECOMMENDATION_ENGINE.md, and docs/TECHNICAL_SPEC.md.
Implement only the backend recommendation engine, request validation, endpoint, and tests.
Do not build the frontend quiz UI in this step.
```

---

### Phase 6 — Quiz UI + Recommendation Results UI
Goal:
- Build frontend quiz flow.
- Call API.
- Render explainable recommendation results.

Read:
- `AGENTS.md`
- `docs/RECOMMENDATION_ENGINE.md`
- `docs/UI_STYLE_GUIDE.md`

Suggested prompt:
```text
Read AGENTS.md, docs/RECOMMENDATION_ENGINE.md, and docs/UI_STYLE_GUIDE.md.
Implement only the frontend quiz flow and recommendation result page.
Reuse the existing API contract.
Do not change backend scoring logic unless absolutely required; if needed, explain first.
```

---

### Phase 7 — Homepage + Guide/Glossary Polish
Goal:
- Build landing page.
- Add public guide/glossary pages.
- Refine empty/loading states.

Read:
- `AGENTS.md`
- `docs/PRD.md`
- `docs/UI_STYLE_GUIDE.md`

Suggested prompt:
```text
Read AGENTS.md, docs/PRD.md, and docs/UI_STYLE_GUIDE.md.
Implement only the public homepage and guide/glossary presentation.
Add polished empty/loading states where necessary.
Do not create new product features outside MVP.
```

---

## 4. Token-Saving Rules for Agents

When working with Agents Code:
- Do not ask it to “understand the entire repo” unless necessary.
- Ask it to inspect only relevant folders/files.
- Use narrow prompts.
- Ask for a plan first on large steps.
- Limit work to one phase or one module per turn.
- Ask it to stop after implementation and summarize.

---

## 5. Review Checklist After Each Phase

Ask:
1. Did it stay within requested scope?
2. Did it read only necessary documents?
3. Did it avoid unrelated refactors?
4. Did it update or create only relevant files?
5. Is there anything that should be tested before continuing?

---

## 6. Prompt Template for Small Fixes

```text
Read AGENTS.md and only the most relevant supporting doc if needed.
Fix only this specific issue: [describe issue].
Do not refactor unrelated files.
Before changing code, identify likely affected files.
After the change, explain what changed and how to verify it.
```

---

## 7. Prompt Template for UI Work

```text
Read AGENTS.md and docs/UI_STYLE_GUIDE.md.
Work only on [specific component/page].
Keep the Soft Neo-Brutalism direction.
Do not redesign unrelated pages.
Return a short summary of changed files and visual decisions.
```

---

## 8. Prompt Template for Backend Work

```text
Read AGENTS.md and docs/TECHNICAL_SPEC.md.
Work only on [specific backend module].
Keep controller logic thin and use dedicated services where appropriate.
Do not change frontend files.
Return changed files, migration/API details if relevant, and test instructions.
```

---

## 9. Prompt Template for Recommendation Logic

```text
Read AGENTS.md and docs/RECOMMENDATION_ENGINE.md.
Work only on the recommendation-related task: [specific task].
Preserve rule-based weighted matching.
Do not introduce ML or LLM ranking.
Explain any scoring adjustment before implementing it.
```

---

## 10. Final Advice

The best workflow is not “build everything quickly”, but:
- make one stable layer,
- test it,
- then continue.

This will usually save more time and tokens than large one-shot generation.
