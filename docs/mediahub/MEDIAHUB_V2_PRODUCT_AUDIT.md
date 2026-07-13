# MediaHub V2 Product Audit

**Audit date:** 2026-07-12
**Product state:** MediaHub Web V1, local current build
**Audit posture:** Review only. No features, code changes, commits, or deployment.
**Disciplines applied:** Product design, UX design, product management, Laravel architecture, QA, accessibility, and performance.

## Executive Verdict

MediaHub V1 is structurally feature-complete, visually coherent, and unusually respectful of private media data. Its strongest moments already feel like a real product: the Shows library, cinematic show detail, dense History browser, honest poster fallbacks, and privacy controls.

The product is not yet coherent enough for dependable daily use. The central issue is **trust**, not missing scope. Several screens disagree with the data beneath them: watched episode counts exceed aired totals, Continue Watching can be empty while unfinished shows exist, episode names change between list and detail, Calendar is empty without a convincing explanation, and Import & Export says no import was recorded while the library visibly contains TV Time data. These contradictions make a complete product feel unfinished.

The next quality bar should be reached by correcting data contracts, simplifying existing flows, and reducing technical leakage. It should not be reached by adding more surface area.

**Overall experience score: 6.1 / 10**

| Dimension | Score | Verdict |
|---|---:|---|
| Visual coherence | 7.0 | A consistent, cinematic foundation with a few visibly empty or metadata-poor surfaces. |
| Usability | 5.9 | Core tasks are present, but navigation state, duplicated settings, and unclear empty states add friction. |
| Performance | 6.2 | Local responses are acceptable in places, but query amplification and repeated requests will not scale gracefully. |
| Emotion | 5.1 | The product records a personal history but too often speaks like an import tool or operations console. |
| Navigation | 6.8 | Desktop navigation is clear; authenticated deep links, browser history, and mobile labels are weak. |
| Completeness | 6.6 | Most required surfaces exist, but data trust and finish quality vary sharply by screen. |

## Scope And Method

The audit used the current repository and a fresh local browser walkthrough at desktop and 390 px mobile widths. It covered login, invitation entry, Home, Discover, Movies, Shows, movie/show/episode details, History, Calendar, Alerts, Stats, Lists, Profile, Friends, Settings, and Import & Export.

Evidence included:

- 25 fresh screenshots from the current application.
- Browser console and request observations.
- Frontend interaction and accessibility inspection.
- Laravel route, service, and query inspection.
- Current local data behavior, including 533 movies, 92 shows, and 7,804 watch records shown by the application.

This was not a production load test, legal review, penetration test, or full screen-reader conformance audit. Local Vite development mode can duplicate requests through React Strict Mode, so timing evidence is directional rather than a production SLA.

## Current Strengths

1. **A distinct visual identity.** The dark cinema palette, yellow accent, typography, borders, and poster-led composition are consistent across the product.
2. **The Shows experience is the visual benchmark.** Rich artwork, progress, season navigation, and cinematic detail create the clearest sense of ownership and memory.
3. **History is dense and useful.** Search, type filters, newest-first ordering, and pagination make a large imported archive navigable.
4. **Neutral fallbacks are honest.** Missing posters are represented without fake artwork pretending to be canonical media.
5. **Privacy is treated as product behavior.** Profiles default to private, public fields are selective, and provider credentials remain outside normal payloads.
6. **Manual ownership remains intact.** Ratings, notes, watch actions, lists, exports, and provider-independent history are visible parts of the product.
7. **Account controls are keyboard-aware.** The account menu supports outside click, Escape, arrow keys, Home, End, and focus return.
8. **The detail model is consistent.** Movies, shows, and episodes use a recognizable modal structure with overview, activity, notes, rating, and history.
9. **The product handles large libraries.** Pagination and filters prevent Movies, Shows, and History from rendering the entire archive at once.
10. **The architecture has clear domain boundaries.** Canonical media, watch activity, profiles, friendships, metadata, and provider records are separate concerns.

## Current Weaknesses

1. **Data contradictions damage trust.** Watched episode counts exceed aired totals, import status conflicts with visible import provenance, and item naming differs across screens.
2. **The emotional promise and event feed disagree.** “Entertainment Diary” contains provider refresh, playback infrastructure, and source labels rather than a human memory narrative.
3. **Authenticated navigation is state-only.** Most screens and details have no durable URL, so refresh, browser back, sharing, and multi-tab use are unreliable.
4. **Empty states often explain absence without resolving uncertainty.** Calendar, Lists, Friends, Profile, and Continue Watching can look unfinished even when useful data exists elsewhere.
5. **Technical implementation details leak into normal UI.** `TVTIME IMPORT`, `local`, `TMDB`, `UTC`, provider refresh events, and version-specific copy appear too frequently.
6. **Profile editing does not scale to a real library.** Hundreds of movies and shows are rendered as one checkbox list without search or virtualization.
7. **Mobile navigation is compact but cryptic.** Ten icon-only destinations consume two rows and require users to memorize symbols.
8. **Frontend requests are not deduplicated or cached.** Re-entering screens repeats data work, and large components own too much state.
9. **Library card serialization is query-heavy.** Per-card watches, ratings, notes, and provider checks create avoidable query amplification.
10. **Statistics are calculated from full watch collections in PHP.** This is acceptable for one archive today but scales with every future watch event.

## First-Time User Walkthrough: “I Expected Something Else”

| Step | What happened | What I expected instead | Health |
|---|---|---|---|
| Register / invitation | Login is polished, but there is no visible invite-only explanation or registration path. An invalid invite exposes a raw Laravel model error. | A calm explanation that MediaHub is invite-only, with friendly valid, expired, revoked, and invalid invitation states. | **Broken** |
| Discover | Attractive media cards provide search and category tabs, but every card repeats three actions and long descriptions. | One clear primary action per item, progressive secondary actions, and a simpler distinction between discovering and browsing my library. | **Needs work** |
| Movies | Filters and pagination work, but a large share of the current library is a wall of identical neutral initials and repeated `local` badges. | Honest fallbacks with stronger typographic variation and metadata status kept out of the primary card hierarchy. | **Needs work** |
| Shows | This is the strongest library, but some progress values exceed 100% because watched totals exceed aired totals. | Progress that is arithmetically trustworthy, with excess imported watches represented as rewatches rather than completion. | **Broken data contract** |
| Movie Detail | The modal is clear, but some movies have no poster, overview, year, or genres despite having runtime data. | A stable summary assembled from available canonical metadata, with missing fields omitted rather than announced as product incompleteness. | **Needs work** |
| Show Detail | Rich cinematic presentation, but “Continue watching” and “View details” lead to the same destination, and “Watched 20 times” is unclear at show level. | One clear next-episode action and a human summary such as episodes watched, completed seasons, or rewatches. | **Needs work** |
| Episode Detail | The list says “Episode 1” while detail says “Untitled episode,” and the hero does not clearly anchor the show and S/E code. | A consistent fallback title such as “Show Name · S01E01” everywhere. | **Broken** |
| History | The archive is usable, but repeated `TVTIME IMPORT` badges and “Unknown episode” rows make personal history feel like a database migration log. | Date-grouped memories with provenance de-emphasized and stable show/episode fallback names. | **Needs work** |
| Calendar | The month view loads but is empty, while copy says “Dates use UTC.” | Releases derived from the existing followed/watchlist metadata, shown in the user’s timezone, or a precise explanation of which metadata is missing. | **Broken expectation** |
| Alerts | The screen has content, but many entries repeat “Continue your show” with no date or urgency. | A small actionable inbox where each alert explains what changed, when, and what action is available. | **Needs redesign** |
| Stats | Totals are useful, but monthly labels repeat month numbers across years, Genres can be blank, and `5,636.2 hours` feels falsely precise. | Humanized time, contextual periods, meaningful chart labels, and explicit empty states for missing dimensions. | **Needs work** |
| Lists | The screen is almost empty and the empty-state message is not itself actionable. | A direct Create List action and a clear first-list flow using existing list capability. | **Unfinished** |
| Profile | Own profile is sparse; edit mode loads hundreds of checkbox options and splits overlapping controls between Profile and Settings. | One profile editing home with searchable favorite selection and a single understandable visibility model. | **Needs redesign** |
| Friends | Search, requests, sent requests, and friends are all present but empty; the page lacks the Invite Friends action visible elsewhere. | One first-use path that explains public search versus invite links and offers the relevant action in place. | **Unfinished** |
| Settings | Settings is clean, but Profile duplicates account information and mobile tabs are clipped without a scroll cue. | A single account/profile ownership model and responsive tab navigation that visibly indicates more sections. | **Needs work** |
| Export | Export exists, but the page says no TV Time import was recorded, presents technical import wording, and renders CSV choices as a long inline list. | A trustworthy data-ownership summary with accurate import state, scannable datasets, and clear export status. | **Broken data contract** |

## Page Scorecard

Scores are 1–10. The average is unweighted.

| Page | Visual | UX | Performance | Emotion | Navigation | Completeness | Average |
|---|---:|---:|---:|---:|---:|---:|---:|
| Home | 7 | 6 | 5 | 6 | 7 | 7 | **6.3** |
| Discover | 8 | 7 | 7 | 7 | 8 | 8 | **7.5** |
| Movies | 6 | 7 | 5 | 5 | 8 | 7 | **6.3** |
| Shows | 8 | 7 | 5 | 8 | 8 | 7 | **7.2** |
| Movie Detail | 7 | 7 | 6 | 6 | 6 | 7 | **6.5** |
| Show Detail | 9 | 8 | 6 | 8 | 7 | 8 | **7.7** |
| Episode Detail | 6 | 6 | 6 | 4 | 6 | 6 | **5.7** |
| History | 7 | 7 | 8 | 5 | 8 | 7 | **7.0** |
| Calendar | 7 | 5 | 6 | 3 | 7 | 4 | **5.3** |
| Alerts | 6 | 5 | 7 | 3 | 7 | 5 | **5.5** |
| Stats | 7 | 6 | 4 | 6 | 7 | 6 | **6.0** |
| Lists | 5 | 4 | 7 | 2 | 6 | 4 | **4.7** |
| Profile | 6 | 4 | 3 | 4 | 6 | 6 | **4.8** |
| Friends | 5 | 4 | 7 | 2 | 5 | 4 | **4.5** |
| Settings | 6 | 5 | 7 | 3 | 6 | 6 | **5.5** |

## Screen Finish Inventory

| Screen | Unfinished | Confusing | Empty | Too technical | Too many clicks |
|---|:---:|:---:|:---:|:---:|:---:|
| Login / invite | ✓ | ✓ |  | ✓ |  |
| Home |  | ✓ | ✓ | ✓ |  |
| Discover |  | ✓ |  | ✓ | ✓ |
| Movies | ✓ |  |  | ✓ |  |
| Shows |  | ✓ |  |  |  |
| Movie Detail | ✓ |  | ✓ | ✓ |  |
| Show Detail |  | ✓ |  |  |  |
| Episode Detail | ✓ | ✓ |  |  |  |
| History |  |  |  | ✓ | ✓ |
| Calendar | ✓ | ✓ | ✓ | ✓ |  |
| Alerts | ✓ | ✓ |  | ✓ |  |
| Stats | ✓ | ✓ | ✓ |  |  |
| Lists | ✓ |  | ✓ |  | ✓ |
| Profile | ✓ | ✓ | ✓ |  | ✓ |
| Friends | ✓ | ✓ | ✓ |  | ✓ |
| Settings |  | ✓ |  | ✓ | ✓ |
| Export | ✓ | ✓ |  | ✓ |  |

## Missing Delight

- The product rarely acknowledges milestones already present in the data: first watch, rewatch, completed season, long-running relationship with a show, or an archive anniversary.
- Continue Watching does not reliably surface the next meaningful action, so Home can open with a large absence instead of recognition.
- Lists, Profile, and Friends do not reward first setup with an immediate visible result.
- Detail views do not connect the item to the user’s broader story: when it was first watched, last watched, rated, noted, or revisited.
- Calendar and Alerts describe inventory states more than anticipation.
- The neutral poster system is honest but visually repetitive at library scale; typography and metadata could carry more identity without inventing artwork.

## Missing Emotion

MediaHub calls itself an entertainment memory, but much of the language still comes from systems administration: import sources, metadata providers, local/enriched states, provider refresh events, and raw status labels. The emotional layer should come from the existing facts, not new intelligence: “first watched,” “watched again,” “finished this season,” “added to your library,” “coming tomorrow,” and “your note from 2023.”

The product is most emotional when artwork, progress, and personal action meet in Show Detail. It is least emotional when a page presents an empty administrative shell or an imported-record label.

## Most Confusing Flows

1. **Invitation to account:** invalid links expose implementation text, while login gives no invite-only context or recovery path.
2. **Continue Watching:** the largest Home region can say there is nothing to continue despite unfinished shows and reminder alerts.
3. **Show completion:** watched counts can exceed aired counts, while the progress bar silently clamps at 100%.
4. **Episode identity:** one item can be “Episode 1,” “Untitled episode,” or “Unknown episode” depending on the screen.
5. **Profile ownership:** Account Menu > Edit Profile is the real editor, while Settings > Profile is a read-only summary.
6. **Profile publication:** `Profile visibility`, `Public profile`, and avatar visibility overlap without explaining precedence.
7. **Friends onboarding:** public profile search and private invite links live in different places without a shared explanation.
8. **Discover versus My Library:** the Discover mode switch overlaps conceptually with global search and dedicated Movies/Shows navigation.
9. **Import status:** History shows imported provenance while Settings says no import was recorded.
10. **Calendar timezone:** UI says UTC even though the backend calculates using the user or application timezone.

## Most Beautiful Screens

1. **Show Detail** — the best balance of backdrop, poster, hierarchy, progress, and personal controls.
2. **Shows** — rich artwork and progress make the library feel owned rather than indexed.
3. **Discover** — poster-led browsing, clear category rhythm, and confident visual density.
4. **Movie Detail** — a strong reusable shell, especially when canonical metadata is complete.
5. **Home header and Tonight** — calm and personal, with clear potential once the data contract is reliable.

## Screens Needing Redesign

“Redesign” here means reorganizing existing capability, not adding scope.

1. **Profile editor:** replace hundreds of checkboxes with searchable, staged selection and unify publication controls.
2. **Friends:** combine search, invitation, request state, and first-use explanation into one clear sequence.
3. **Lists:** make creation the actual empty-state action and establish hierarchy before search/filter controls.
4. **Alerts:** separate time-sensitive alerts from reminders and imported backlog, with dates and action relevance.
5. **Calendar empty state:** make timezone, eligibility, and missing release metadata understandable.
6. **Settings information architecture:** eliminate Profile duplication and reduce technical metadata/account copy.

## Best Opportunities

1. Restore trust by reconciling episode, import, calendar, and Continue Watching data contracts.
2. Turn existing events into a human Entertainment Diary rather than an operational log.
3. Give every authenticated surface a durable URL and predictable browser history behavior.
4. Make empty states actionable using capabilities that already exist.
5. Remove normal-user exposure to source, provider, metadata, and import implementation terms.
6. Optimize library card and statistics queries before more users or watch events arrive.
7. Unify Profile, Privacy, and Settings ownership.
8. Improve mobile navigation recognition and reduce pre-content vertical cost.

## Daily-Use Friction

- Home requires a long scroll and can spend its largest area on an incorrect empty state.
- Re-entering sections refetches data because there is no shared client cache or request deduplication.
- Browser Back does not represent section or detail navigation.
- Movie shelves with repeated neutral posters require reading every title rather than scanning artwork.
- Alerts are repetitive and do not explain timing.
- History provenance badges compete with titles on every row.
- Mobile users must memorize icons for ten destinations.
- Settings tabs are horizontally clipped at 390 px without a visible “more” affordance.
- Profile favorite selection creates hundreds of keyboard and scrolling targets.
- Calendar offers no useful release answer despite requiring a full page visit.

## Technical Debt

### Frontend architecture

- `src/App.jsx` is approximately 2,100 lines and coordinates authentication, section navigation, details, search, profile modes, and most product orchestration.
- `src/styles.css` is approximately 5,600 lines, increasing regression risk and making page ownership unclear.
- Authenticated navigation is driven by `activeSection` state. Only public profile and invitation paths are parsed from the URL (`src/App.jsx:43`).
- `apiRequest` is a bare fetch wrapper without shared caching, deduplication, cancellation, or stale-response policy (`src/App.jsx:327`).
- Detail modals move focus to the close button and support Escape, but they do not trap Tab focus or restore focus to the exact invoking element (`src/App.jsx:1206`).
- Reduced-motion styles and visible focus styles exist, which is a good base (`src/styles.css:894`, `src/styles.css:5053`).

### Backend architecture

- Library cards perform per-item watch, rating, note, and provider-link checks. A 24-card page can trigger roughly 100 additional queries (`backend/app/Services/LibraryBrowserService.php:163`, `backend/app/Services/LibraryBrowserService.php:540`).
- Show provider status plucks episode IDs and checks links per show, multiplying work across a page (`backend/app/Services/LibraryBrowserService.php:557`).
- Statistics load all movie and episode watches and aggregate them in PHP (`backend/app/Services/StatisticsService.php:16`).
- Calendar’s next-episode hints check local episode existence once per followed show (`backend/app/Services/CalendarService.php:99`).
- Dashboard views write an analytics event during a read request and assemble multiple independent payload concerns (`backend/app/Services/DashboardPayloadService.php:35`).
- Dashboard copy still exposes the legacy “TV Time Laravel backend” source framing (`backend/app/Services/DashboardPayloadService.php:50`).
- Profile options load up to 1,000 movies and 500 shows in one response (`backend/app/Services/UserProfileService.php:143`).
- Invalid invitation lookup uses `firstOrFail`, and the frontend displays `error.message`, allowing raw model errors to reach users (`backend/app/Services/FriendInviteService.php:120`, `src/components/ProfileSurfaces.jsx:58`).

### Local performance observations

These figures were captured in local Vite development mode and should be treated as comparative evidence only:

| Request | Observation |
|---|---|
| `/api/v1/dashboard` | 1 request, about 1.46 s, 54.9 KB |
| `/api/v1/stats` | 4 requests, 669 ms average, 1.12 s maximum |
| `/api/v1/calendar` | 8 requests, 346 ms average, 644 ms maximum |
| `/api/v1/library/shows` | 19 requests, 286 ms average, 362.5 KB cumulative |
| `/api/v1/library/movies` | 12 requests, 200 ms average, 70.4 KB cumulative |
| `/api/v1/profile/options` | 2 requests, 49.5 KB cumulative |

React development behavior amplified repeated requests, but the current code has no shared query cache, and the backend query shape confirms avoidable repeated work.

## Top 100 Improvements By Impact

The list is ordered by user impact, not implementation difficulty. Classification is the primary discipline; several items naturally affect more than one.

| # | Improvement | Classification | Why it matters |
|---:|---|---|---|
| 1 | Replace raw invalid-invite model errors with friendly invalid, expired, revoked, and used states. | Bug | The first public touchpoint currently exposes framework language and destroys trust. |
| 2 | Reconcile `seen_episodes` and `aired_episodes`; represent excess watches as rewatches instead of >100% completion. | Bug | Core progress data is visibly impossible on multiple shows. |
| 3 | Make Continue Watching derive from the real next unfinished movie or episode. | Bug | Home’s primary promise can contradict the user’s library. |
| 4 | Standardize episode fallback identity across lists, detail, History, Calendar, and search. | Bug | “Episode 1,” “Untitled episode,” and “Unknown episode” cannot all describe one record. |
| 5 | Repair import-status provenance so Settings agrees with imported History and library data. | Bug | Data ownership and backup trust depend on accurate origin status. |
| 6 | Use the backend-provided user timezone in Calendar copy and rendering; remove hardcoded UTC language. | Bug | Release timing is a core promise and currently describes the wrong contract. |
| 7 | Diagnose and correct the empty Calendar eligibility pipeline using existing followed-show and watchlist metadata. | Bug | A release calendar with no releases feels nonfunctional. |
| 8 | Remove provider refresh, connector, and transport events from the visible Entertainment Diary. | UX | The memory feed currently reads like an operations log. |
| 9 | Separate actionable alerts from passive backlog reminders using the existing alert types. | UX | Sixteen repetitive unread items create alert fatigue rather than confidence. |
| 10 | Explain invite-only access directly on Login. | Copywriting | First-time users currently cannot tell how access is obtained. |
| 11 | Give authenticated sections and media details durable URLs. | Architecture | Refresh, browser Back, sharing, bookmarks, and multi-tab use need stable navigation state. |
| 12 | Batch/eager-load card watch, rating, note, and provider-link state. | Performance | Per-card query amplification is the largest server-side browsing risk. |
| 13 | Aggregate statistics in SQL instead of loading all watch rows into PHP. | Performance | Stats cost grows with the user’s permanent history. |
| 14 | Add shared request caching, deduplication, cancellation, and stale-response protection. | Performance | Navigation currently repeats work and can race updates. |
| 15 | Split `App.jsx` into route/page orchestration boundaries. | Architecture | A 2,100-line state owner makes every UX fix riskier. |
| 16 | Split the global stylesheet by tokens, primitives, and page ownership. | Architecture | A 5,600-line stylesheet raises visual regression cost. |
| 17 | Replace the Profile favorite checkbox wall with searchable, incremental selection. | UX | Editing favorites is unusable at 533 movies and 92 shows. |
| 18 | Virtualize or paginate Profile favorite options. | Performance | The current editor creates a large DOM and keyboard sequence immediately. |
| 19 | Merge Profile editing and Settings Profile into one ownership path. | UX | Two profile surfaces provide different capabilities and expectations. |
| 20 | Collapse profile visibility controls into one clear model with dependent toggles explained. | UX | Public enablement, visibility, and avatar publication have unclear precedence. |
| 21 | Preserve show title and S/E code prominently in Episode Detail. | UX | Episode identity should survive missing metadata. |
| 22 | Convert all API failures to a product-safe error vocabulary before rendering. | Architecture | Raw backend messages should never be user copy. |
| 23 | Make invitation acceptance explain whether sign-in, account creation, or confirmation is required. | UX | The current path has no clear mental model for a first-time recipient. |
| 24 | Give Lists empty state a direct Create List action. | UX | The page tells users what to do without providing the action in context. |
| 25 | Put Invite Friends inside the empty Friends page. | UX | The most relevant recovery action is currently elsewhere. |
| 26 | Explain public search versus private invite links on Friends. | Copywriting | Users cannot infer why a person may not appear in search. |
| 27 | Remove “There is no messaging or public activity feed in V1” from the primary Friends experience. | Copywriting | Negative version-specific copy makes the product feel provisional. |
| 28 | Replace `TVTIME IMPORT` row badges with a quiet provenance detail available on demand. | Visual | Source labels overpower personal History. |
| 29 | Hide `local` / `enriched` metadata badges from primary library cards. | Visual | Internal data state should not compete with title and watch state. |
| 30 | Remove “TV Time Laravel backend” from dashboard source copy. | Copywriting | The product has outgrown the migration project name. |
| 31 | Humanize Diary events using watched, rated, noted, added, and completed language. | Copywriting | Existing events can already tell a personal story. |
| 32 | Group History by date while retaining pagination and search. | UX | A timeline should read temporally before it reads technically. |
| 33 | Keep S/E fallback labels instead of “Unknown episode” in History. | Bug | The record already contains enough identity to avoid “unknown.” |
| 34 | Clarify whether History search applies immediately or on Search button press. | UX | Filters currently mix instant and submitted interaction models. |
| 35 | Align Movies filter behavior so text, status, and sort apply consistently. | UX | Mixed submission behavior makes results feel unpredictable. |
| 36 | Make Discover’s primary card action singular and demote secondary actions. | UX | Three equal actions on every card create visual and decision overload. |
| 37 | Clarify the difference between Discover, global search, and Discover’s My Library mode. | UX | Three discovery mechanisms currently overlap. |
| 38 | Shorten card overviews and reveal full copy in detail. | Visual | Dense paragraphs weaken scanning and poster hierarchy. |
| 39 | Omit missing Movie Detail fields instead of announcing “No overview available yet.” | Copywriting | Missing metadata should not make the product sound unfinished. |
| 40 | Give honest neutral posters more visual identity through title-led composition, not fake art. | Visual | Repeated initial tiles make large movie libraries monotonous. |
| 41 | Make Show hero “Continue watching” target the next episode, not the same detail action. | Bug | The primary verb currently promises behavior it does not perform. |
| 42 | Replace show-level “Watched N times” with an unambiguous existing metric. | Copywriting | A series watched 20 times is semantically unclear. |
| 43 | Add confirmation or undo to season-wide watched/unwatched actions. | UX | Bulk history changes have high consequence. |
| 44 | Restore focus to the exact trigger after closing detail modals. | Accessibility | Keyboard users lose their place in long shelves and grids. |
| 45 | Trap Tab focus inside open detail and link modals. | Accessibility | `aria-modal` does not itself prevent background focus. |
| 46 | Give modal tabs correct tablist/tab/tabpanel semantics. | Accessibility | Current controls are visually tabs but need programmatic relationships. |
| 47 | Add a skip-to-content link. | Accessibility | Keyboard users currently traverse the full navigation on every page. |
| 48 | Show visible labels for mobile primary navigation, or provide a clearly labeled compact menu. | Accessibility | Ten icon-only destinations require memorization. |
| 49 | Reduce mobile header/nav/search vertical cost. | UX | Meaningful Home content begins too far below the viewport top. |
| 50 | Make Settings tab overflow visibly scrollable with edge cue and active-tab reveal. | UX | At 390 px, tabs are clipped without indicating hidden sections. |
| 51 | Replace empty Calendar silence with a precise eligibility summary. | Copywriting | Users need to know whether nothing is scheduled or metadata is incomplete. |
| 52 | Show Calendar in the user’s locale and week convention. | UX | A personal schedule should not feel server-centric. |
| 53 | Add date and release context to alert rows using fields already present. | UX | “When you are ready” does not explain urgency. |
| 54 | Deduplicate repetitive Continue Watching alerts. | Bug | Similar rows inflate unread count and erode trust. |
| 55 | Make “Mark all read” more visible and provide a short undo state. | UX | Bulk inbox changes are easy to miss and hard to reverse. |
| 56 | Define what “This month” measures in Home statistics. | Copywriting | A naked number has no meaning. |
| 57 | Humanize total watch time into hours and days without decimal false precision. | Copywriting | `5,636.2 hours` is technically precise but cognitively poor. |
| 58 | Include year context in monthly activity labels. | Bug | Repeated month numbers across years are ambiguous. |
| 59 | Add a true empty state to Genres rather than a blank panel. | UX | Blank charts appear broken. |
| 60 | Suppress meaningless Top Movies rankings when every item is tied at one watch. | UX | Ranking identical values adds noise, not insight. |
| 61 | Make chart values available as accessible text summaries. | Accessibility | Visual bars alone are not sufficient for all users. |
| 62 | Improve chart label contrast and target size. | Accessibility | Tiny period labels are difficult to read on laptop and mobile screens. |
| 63 | Reframe Collections empty columns so Lists and Favorites are not conflated. | Copywriting | Current copy sends users to Profile for a section titled Lists. |
| 64 | Hide empty Home collection columns until they can display an existing list/favorite. | UX | Three empty columns consume attention without utility. |
| 65 | Make Home’s first viewport denser when Continue Watching is empty. | UX | The current large absence makes the product feel sparse. |
| 66 | Tie Tonight suggestions to named existing items and clear duration logic. | Copywriting | Generic counts are less actionable than the item already chosen. |
| 67 | Ensure Tonight and Continue Watching never offer contradictory actions. | Bug | Home sections should share one watch-state source of truth. |
| 68 | Limit Home’s initial payload to above-the-fold essentials and defer lower data. | Performance | The dashboard payload is large and slow for the first screen. |
| 69 | Stop writing `dashboard.viewed` analytics synchronously on every dashboard read. | Performance | A read path should not add avoidable database contention. |
| 70 | Cache stable dashboard fragments with user-safe invalidation. | Performance | Stats and shelves are recomputed more often than their data changes. |
| 71 | Batch Calendar’s local-episode existence checks. | Performance | One query per followed show is unnecessary. |
| 72 | Add response-level query-count regression tests for library pages. | Architecture | Functional tests alone will not catch N+1 regressions. |
| 73 | Add request-duration budgets for dashboard, stats, calendar, and libraries. | Performance | Performance needs an explicit release gate. |
| 74 | Add frontend request-count regression coverage under React Strict Mode. | Performance | Duplicate development requests currently hide real refetch behavior. |
| 75 | Preserve section, filters, pagination, and scroll position in navigation state. | UX | Returning from detail should not reset browsing context. |
| 76 | Use browser Back to close detail before leaving the library. | Navigation | Modal navigation should match platform expectations. |
| 77 | Make media details shareable without exposing user-private activity. | Navigation | Canonical identity and private state need distinct URL behavior. |
| 78 | Add a clear loading skeleton for large library transitions. | Animation | Current waiting states do not preserve spatial context consistently. |
| 79 | Standardize motion duration/easing across menus, modals, rows, and page entry. | Animation | Existing motion is restrained but not always systematized. |
| 80 | Preserve all motion changes under the existing reduced-motion policy. | Accessibility | Polish must not remove a current strength. |
| 81 | Increase contrast of secondary labels and action text that fall below comfortable laptop readability. | Accessibility | Several metadata badges and quiet actions are visually fragile. |
| 82 | Ensure poster images use descriptive alt text when the image adds identity. | Accessibility | Empty alt is correct for decorative backdrops, not always for primary posters. |
| 83 | Give avatar images the profile display name as alt text where identity matters. | Accessibility | Current avatar component uses empty alt even in account/profile contexts. |
| 84 | Announce async save, upload, and filter results through live regions. | Accessibility | Visual status text may not be announced to assistive technology. |
| 85 | Make drag-and-drop avatar upload fully operable and explained by keyboard. | Accessibility | File selection works, but the dropzone interaction is visually dominant. |
| 86 | Add search and selected-count context to favorite pickers. | UX | Users need orientation before selecting from hundreds of records. |
| 87 | Prevent duplicate HTML `datalist` IDs if Profile and another country field mount together. | Architecture | Reusable form controls should own unique associations. |
| 88 | Clarify avatar privacy dependency when the profile itself is private. | Copywriting | The current toggle can imply an impossible publication state. |
| 89 | Make public-profile preview visibly distinct from owner view. | UX | Users need confidence about exactly what others see. |
| 90 | Keep email and internal account role out of any shared/profile preview path with explicit contract tests. | Architecture | Privacy correctness should remain enforceable as the profile evolves. |
| 91 | Turn export dataset names into scannable grouped controls. | Visual | A long inline CSV link string is difficult to parse. |
| 92 | Explain what the full JSON export includes using the existing export contract. | Copywriting | Data ownership requires informed expectations. |
| 93 | Show export generation state, completion timestamp, and safe failure message. | UX | A download action currently provides little feedback. |
| 94 | Replace “Private assisted import” with a human description of the current workflow. | Copywriting | The phrase describes an internal process, not a user action. |
| 95 | Remove version-specific wording such as “not enabled in V1” from permanent settings copy. | Copywriting | Product copy should describe current capability without sounding temporary. |
| 96 | Move metadata provider coverage into a secondary diagnostics disclosure. | UX | Enrichment ratios are useful operationally but not primary settings content. |
| 97 | Give Account deletion/export guidance a single ordered sequence. | UX | Current warning explains consequence but not the user’s next step. |
| 98 | Add empty, loading, error, and success contract tests for every primary page. | Architecture | Finish quality is inconsistent because state coverage varies by surface. |
| 99 | Establish one product vocabulary for library, watchlist, followed, watched, completed, and rewatched. | Copywriting | Several screens use overlapping state words without a canonical definition. |
| 100 | Add a V2 quality gate covering data trust, accessibility, request count, and empty-state actionability. | Architecture | The product needs a finish standard, not another feature checklist. |

## Recommended Sequence

### P0 — Trust blockers

1. Invitation error handling.
2. Show/episode count reconciliation.
3. Continue Watching correctness.
4. Episode identity consistency.
5. Import-status consistency.
6. Calendar timezone and eligibility correctness.

### P1 — Daily-use coherence

1. Humanize Diary and Alerts.
2. Durable authenticated routes and detail history.
3. Unify Profile/Privacy/Settings.
4. Make Lists and Friends empty states actionable.
5. Simplify Discover and hide technical labels.
6. Improve mobile navigation and Settings tabs.

### P2 — Scale and finish

1. Remove library N+1 queries.
2. Move Stats aggregation into the database.
3. Add frontend query caching/deduplication.
4. Split frontend ownership boundaries.
5. Complete modal, chart, image, and async accessibility work.

## Evidence Index

Screenshots are local audit artifacts under `output/playwright/mediahub-v2-audit/` and are intentionally outside product code.

| Evidence | File |
|---|---|
| Login | `01-login.png` |
| Home | `02-home.png`, `23-home-library.png`, `24-home-lower.png`, `25-home-bottom.png` |
| Discover | `03-discover.png` |
| Movies and detail | `04-movies.png`, `05-movie-detail.png` |
| Shows and detail | `06-shows.png`, `07-show-detail.png`, `08-episode-detail.png` |
| History | `09-history.png` |
| Calendar | `10-calendar.png` |
| Alerts | `11-alerts.png` |
| Stats | `12-stats.png` |
| Lists | `13-lists.png` |
| Settings and export | `14-settings.png`, `15-import-export.png` |
| Account and profile | `16-account-menu.png`, `17-profile.png`, `18-profile-edit.png` |
| Friends | `19-friends.png` |
| Mobile | `20-home-mobile.png`, `21-settings-mobile.png` |
| Invalid invite | `22-invite-entry.png` |

## Final Product Assessment

MediaHub should enter V2 as a **stabilization and coherence program**, not a feature program. The product already has enough capability to be valuable. Its next leap is to make every existing screen agree about the same person, the same media, the same watch state, and the same language.

The highest-value outcome is simple: a user should open MediaHub and trust every number, title, date, action, and empty state without needing to understand imports, metadata providers, Laravel, or provider infrastructure.
