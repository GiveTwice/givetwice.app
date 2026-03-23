# TODOs

## Gift Exchange — Phase 2

### Exclusion UI for gift exchanges
**What:** Let organizers set exclusion pairs (e.g., couples who shouldn't draw each other) before running the draw.
**Why:** Real-world Secret Santa groups always have couples/siblings who shouldn't draw each other. This is the #1 feature request on competing apps.
**Effort:** ~2 hours with CC. Requires constraint-satisfaction retry loop + organizer UX for managing pairs.
**Depends on:** Core lootjes trekken MVP shipped.
**Added:** 2026-03-22 via /plan-eng-review

### Actual per-claim affiliate commission tracking
**What:** Replace the hardcoded "~€2-5 donated" estimate with real per-claim affiliate data. Track which affiliate partner, click-through, and commission amount for each gift URL.
**Why:** Real numbers are more impactful than estimates. "Your gift donated €3.47" hits harder than "~€2-5." Feeds into the transparency page.
**Effort:** Ocean — requires partner API integration (varies by affiliate network), per-click attribution logic, and a new data model.
**Depends on:** Affiliate partnerships established, partner APIs available.
**Added:** 2026-03-22 via /plan-eng-review

## Brand & Growth

### Voice rebrand of existing pages
**What:** Rewrite ~50 translation strings across homepage, about page, auth pages, and existing emails to match the Reynolds/Williams/Adams voice DNA defined in the lootjes trekken design doc.
**Why:** Participants arriving via lootjes trekken experience the new voice. If they visit the homepage and see generic SaaS copy, the inconsistency erodes trust.
**Effort:** S (human: ~3 hours / CC: ~45 min). Requires translation into 3 languages (en/nl/fr).
**Priority:** P2 — do after lootjes trekken ships, before Sinterklaas campaign.
**Depends on:** Lootjes trekken shipped (so the voice DNA is proven in production first).
**Added:** 2026-03-22 via /plan-ceo-review

### Browser extension / bookmarklet
**What:** Chrome extension that adds "Add to GiveTwice" button on any product page. Extracts title, image, price, URL. Bookmarklet fallback for Firefox/Safari.
**Why:** Primary engagement driver — reduces adding a gift from 4 steps to 1 click. 12+ competitors have this. See ROADMAP.md Phase 1.2 for full spec.
**Effort:** M (human: ~2 weeks / CC: ~2 hours). Separate codebase + Chrome Web Store review.
**Priority:** P1 — second highest-leverage feature after lootjes trekken.
**Depends on:** API endpoint for quick-add (`POST /api/gifts/quick-add`).
**Added:** 2026-03-22 via /plan-ceo-review
