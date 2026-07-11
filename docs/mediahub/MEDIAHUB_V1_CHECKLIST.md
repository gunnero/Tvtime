# MediaHub Web V1 Checklist

Last updated: 2026-07-11

Status: implementation and local QA complete; not deployed

## Product

- [x] Discovery with My Library and TMDB-backed Discover separation
- [x] Paginated movie library, filters, sorting, and detail
- [x] Paginated show library, filters, sorting, and detail
- [x] One-season-at-a-time season browser and season watch actions
- [x] Episode rows and episode detail
- [x] Paginated watch history
- [x] Ratings for movies, shows, and episodes
- [x] Private notes for movies, shows, and episodes
- [x] Human-readable entertainment diary
- [x] Database-derived monthly/yearly stats and distributions
- [x] Release calendar with day, week, month, and type filters
- [x] In-app alerts and preferences
- [x] Private manual lists with add, remove, reorder, rename, and delete
- [x] Existing private TV Time import retained
- [x] User-owned JSON and CSV export
- [x] Final Profile, Privacy, Notifications, Import & Export, Metadata, Account, and About settings

## Product Boundary

- [x] Web Player hidden by default
- [x] Web Provider Settings hidden by default
- [x] Provider/player backend and admin compatibility retained
- [x] No subscription, payment, or premium-gating code
- [x] No native playback implementation

## Quality

- [x] User-scoped API tests
- [x] Import repair dry-run and idempotency tests
- [x] Provider/player API route preservation tests
- [x] Frontend component tests for V1 surfaces
- [x] Complete backend suite
- [x] Complete frontend suite after final docs/code pass
- [x] Python importer suite
- [x] Production frontend build after final pass
- [x] Pint and PHP syntax checks
- [x] Sensitive-value and legacy-surface scans
- [x] Desktop screenshots
- [x] Mobile screenshots and horizontal-overflow review
- [x] Keyboard/focus review

## Operations

- [ ] Deploy Web V1
- [ ] Run staging smoke tests
- [ ] Confirm noindex policy for staging
- [ ] Run import relationship dry-run against the intended staging user

## Data Accuracy

- [x] Rewatch-aware movie watch time
- [x] Show completion derived from canonical counters
- [x] Specials remain season 0
- [x] Missing metadata does not suppress canonical episodes
- [x] Deterministic relationship repair command implemented
- [x] Local user 1 dry-run reviewed: 7,291 episodes, 13 counter-only repairs, no broken episode/watch links, no aggregate-only gaps
- [x] Local user 1 cross-user relationship scan: zero mismatched episode-show or watch-episode links
- [x] Valid positive-number episode identity scan: zero duplicate groups
- [ ] Review 15 invalid-number duplicate groups (46 extra rows, all distinct external IDs) without deleting history
- [ ] Apply repairs only after reviewing dry-run and backup

## Commits

- `94ca2e0` - preserve XMLTV query credentials
- `276ddb2` - expose provider catalog sync lifecycle
- Web V1 implementation: uncommitted during final QA

## Known Blockers And Bugs

- Deployment is intentionally blocked until explicit approval.
- User-facing import remains an assisted private workflow; there is no raw GDPR upload form.
- Email notification delivery and push notifications are not part of V1.
- Aggregate-only imported show totals cannot be converted into invented episode rows.
- The local archive contains 15 season/episode zero-number identity groups. They are retained as ambiguous imported records and are not eligible for automatic deduplication.
