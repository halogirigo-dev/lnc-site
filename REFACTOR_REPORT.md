# Lombok Nature Culture — Refactor Report
**Date:** June 2026

---

## Summary

The codebase had one major code quality issue: **78 inline style attributes in `booking.php`** — the most important conversion page. This has been fully refactored into a CSS-driven system.

---

## Changes Applied

### 1. `booking.php` — Inline Styles → CSS Classes

**Before:** Every element in booking.php used inline `style=""` attributes:
```html
<h2 style="font-family:'Raleway',sans-serif;font-weight:900;font-size:34px;
           color:#1a2118;margin-bottom:8px;">Choose Your Experience</h2>
<p style="font-family:'Lato',sans-serif;font-size:15px;color:#8a7d6e;
          margin-bottom:32px;line-height:1.7;">...</p>
```

**After:** Semantic CSS classes:
```html
<h2 class="bk-step__title">Choose Your Experience</h2>
<p class="bk-step__sub">...</p>
```

**Impact:**
- ~8KB reduction in HTML payload per booking page load
- All styles now cacheable by the browser
- Responsive breakpoints can now target booking elements properly
- Single-line CSS edits propagate to all instances

### 2. New CSS Classes Added to `style.css`

| Class | Purpose |
|---|---|
| `.booking-topbar` | Booking page header bar |
| `.booking-topbar__brand` | Logo + brand name link |
| `.bk-step__title` | Step heading (replaces inline font/size/color) |
| `.bk-step__sub` | Step subtitle paragraph |
| `.bk-label` | Form field labels |
| `.bk-input` | Light-background form inputs (overrides dark CTA defaults) |
| `.bk-radio-grid` | 3-column accommodation radio grid |
| `.bk-radio-label` | Radio option label with `:has(input:checked)` state |
| `.bk-field-group` | Spacing wrapper for field groups |
| `.bk-source-pills` | "How did you hear" pill buttons |
| `.bk-source-pill` | Individual source pill |
| `.bk-vision-stack` | Column stack for vision step |
| `.bk-next-steps` | "What Happens Next" info box |
| `.pkg-option` | Package selection card (promoted from inline) |
| `.pkg-option__icon` | Package icon cell |
| `.pkg-option__id` | Package ID / duration line |
| `.pkg-option__title` | Package title |
| `.pkg-option__sub` | Package subtitle |
| `.pkg-option__pricing` | Price column |
| `.pkg-radio-dot` | Selection indicator dot |
| `.pkg-radio-dot--active` | Selected state |
| `.bk-review-table` | Review step summary table |
| `.bk-review-row` | Individual review row |
| `.bk-review-key` | Review row label |
| `.bk-review-val` | Review row value |
| `.bk-terms-note` | Terms agreement notice |
| `.bk-sidebar__*` | Sidebar summary panel classes |
| `.booking-error-banner` | Validation error display |
| `.sr-only` | Screen-reader only utility |

### 3. Fonts Fixed — Raleway/Lato References Removed

`booking.php` previously referenced `font-family:'Raleway'` and `font-family:'Lato'` in inline styles — neither font was loaded. These have been replaced by `var(--font-head)` (MuseoModerno) and `var(--font-body)` (MuseoModerno) via the CSS class system.

### 4. Accessibility Improvements in `booking.php`

- All form inputs now have associated `<label for="field-...">` / `id` pairs
- Package radio inputs use `.sr-only` instead of `display:none` (screen reader accessible)
- ARIA labelling inherited from existing form-input structure

### 5. `config.php` — Centralised Session Management

`session_start()` was called inconsistently across `booking.php`, `process-booking.php`, and `thank-you.php`. It is now centralised in `config.php` with secure cookie parameters.

---

## What Was Preserved

- All multi-step booking logic in `main.js` (unchanged)
- All `data.php` package data (unchanged)
- All visual design intent — only implementation method changed
- All JavaScript selectors and data attributes remain compatible
