# UI_STYLE_GUIDE.md

# ScentMatch Local — Visual Design & UI Style Guide

## 1. Locked Visual Direction

Public-facing UI uses:

> **Soft Neo-Brutalism + Bento Grid + Clean Editorial Readability**

This means:
- bold and memorable,
- youthful and playful,
- visually distinctive for a portfolio project,
- still clean enough for perfume product information.

---

## 2. What the UI Should Feel Like

The UI should feel:
- friendly,
- fun,
- structured,
- local-brand oriented,
- modern,
- not generic.

It should **not** feel like:
- a corporate dashboard,
- a luxury dark fragrance boutique,
- a glassmorphism portfolio template,
- a default SaaS landing page.

---

## 3. Core Visual Rules

### 3.1. Borders
- Use noticeable borders, commonly `2px–3px`.
- Use dark/near-black outlines.
- Cards, buttons, filter chips, and form boxes should feel part of the same system.

### 3.2. Shadows
- Use offset shadows typical of neo-brutalism.
- Shadows should create a physical “sticker/card” feeling.
- Hover interactions may slightly shift the element and alter shadow offset.

### 3.3. Corners
- Use small-to-medium radius.
- Avoid ultra-round pill shapes everywhere.
- Keep the layout playful but structurally clear.

---

## 4. Color Direction

### 4.1. Base Colors
- Background: off-white, cream, warm neutral.
- Text/borders: black or near-black.

### 4.2. Accent Colors
Use a restrained playful set, such as:
- warm yellow,
- coral / peach,
- mint green,
- lavender,
- sky blue.

### 4.3. Color Usage
Use accent colors for:
- primary CTAs,
- category cards,
- badges,
- selected quiz options,
- caution or match emphasis.

Do not make every section use every color.

---

## 5. Typography

### 5.1. Heading Style
- Bold and expressive.
- Strong hierarchy.
- Should be easy to scan.

### 5.2. Body Style
- Clean sans-serif.
- High readability.
- Avoid dense paragraphs in UI components.

### 5.3. Hierarchy
Maintain clear hierarchy between:
- hero title,
- section title,
- card title,
- supporting text,
- labels/meta information.

---

## 6. Component Styling

### 6.1. Buttons
- Bold label.
- Thick border.
- Solid background.
- Offset shadow.
- Visible hover/focus/disabled states.

Primary CTA examples:
- “Temukan Parfum Untukmu”
- “Mulai Quiz”

### 6.2. Cards
Use card treatment for:
- perfume cards,
- feature cards,
- result cards,
- educational cards,
- quiz answer options.

Rules:
- content first,
- decoration second,
- recommendation cards may be more expressive than catalog cards.

### 6.3. Badges
Use badges for:
- aroma category,
- occasion,
- blind-buy caution,
- local brand,
- match label.

Badges should be:
- legible,
- concise,
- high contrast.

### 6.4. Inputs and Filters
- Use strong border style.
- Filter groups should remain usable on mobile.
- Quiz options can be large selectable cards instead of tiny radios.

---

## 7. Page-Specific Guidance

### 7.1. Homepage
Use bento-grid layout for sections such as:
- how it works,
- aroma categories,
- blind-buy guidance,
- perfume education,
- local perfume discovery.

Hero must have:
- clear headline,
- primary CTA to quiz,
- secondary CTA to catalog.

### 7.2. Quiz Page
This page should express the neo-brutalist identity most strongly.

Use:
- visible stepper/progress,
- card-based choices,
- generous spacing,
- strong selected-state feedback.

### 7.3. Recommendation Results
Must be information-first.

Each result card should clearly show:
- match percentage,
- reasons,
- caution label,
- CTA to detail page.

### 7.4. Catalog
Keep it clean and easy to scan.

Use style on:
- filter section,
- cards,
- chips,
- pagination,
- empty state.

### 7.5. Detail Page
Blend editorial cleanliness with neo-brutalist framing.

Suggested sections:
- beginner summary,
- blind-buy caution,
- occasion fit,
- technical details,
- notes pyramid.

---

## 8. Motion and Interaction

Use only light, purposeful motion:
- hover lift/shift,
- accordion expand,
- smooth quiz step transition,
- selected-state feedback.

Avoid:
- excessive parallax,
- complex animated backgrounds,
- motion that distracts from reading.

---

## 9. Accessibility Requirements

- Maintain high contrast.
- Do not rely on color alone to communicate caution or selected state.
- Use visible focus states.
- Ensure clickable areas are large enough.
- Use semantic HTML where possible.
- Caution badges must contain text labels.

---

## 10. Summary Rule for AI

When building frontend UI:
1. Read this file first.
2. Keep Soft Neo-Brutalism as the design identity.
3. Keep information readable.
4. Do not drift into generic SaaS or dark luxury aesthetics.
