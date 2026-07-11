# MediaHub Discovery And Provider Player Specification

Status: Sprint 010 implementation contract

## Purpose

MediaHub has three separate user experiences:

1. **Discover** finds public movie and show metadata outside the user's current library.
2. **My Library** stores permanent, user-owned canonical media and entertainment history.
3. **Player** browses and plays a private catalog imported from a provider attached by that user.

These experiences may link to one another, but they do not share ownership or persistence rules.

## Non-Negotiable Product Rules

- MediaHub provides no streams and has no global stream catalog.
- Users may connect only sources they own or are authorized to use.
- Provider credentials, provider URLs, playlist URLs, artwork locators, and playback URLs are private per user.
- Provider settings and playback locators are encrypted at rest.
- List, dashboard, discovery, library, timeline, and admin payloads do not return raw provider or playback URLs.
- Only the owner-only play endpoint may return the active playback URL required by the video player.
- Canonical media, watch history, ratings, notes, and diary events survive provider disablement, refresh, or deletion.
- Deterministic local/TMDB identity matching runs before any optional Kalveri AI fallback.
- Sprint 010 adds no recommendation or Kalveri AI behavior.

## Discovery

Discovery is an authenticated TMDB-backed search mode, separate from canonical library search.

### Search API

`GET /api/v1/discover/search`

Parameters:

- `query`: required, 2-120 characters
- `type`: `movie`, `show`, or `all`
- `page`: positive integer
- `year`: optional four-digit year

Safe response fields:

- `media_type`
- `tmdb_id`
- `title`
- `original_title`
- `year`
- public TMDB `poster` and `backdrop` URLs
- `overview`
- `genres`
- `already_in_library`
- `existing_library_id`

The endpoint is rate-limited. When TMDB is disabled or unavailable it returns a safe status and an empty result list; normal library use remains available.

### Add API

- `POST /api/v1/discover/movies/{tmdb_id}/add`
- `POST /api/v1/discover/shows/{tmdb_id}/add`

Supported action values:

- `library`
- `watchlist`
- `watched` for movies

Adding a title creates or reuses one canonical record for the current user. TMDB identity and public metadata are stored additively. Duplicate requests do not create duplicate canonical media. Discovery actions record sanitized media events.

## Cinematic Details

Movie and show details are rendered as wide, independently scrolling overlays.

Movie detail includes:

- backdrop and proportionate poster
- title, year, runtime, genres, overview, watched state, rating, and provider state
- watch-history and watchlist actions
- Play only when a same-user linked provider item is available
- Overview, Your Activity, Notes & Rating, Watch History, and Provider / Playback tabs
- collapsed technical metadata

Show detail includes:

- the same cinematic header
- progress and next-unwatched episode
- Overview, Episodes, Activity, Notes & Rating, and Provider tabs
- one selected season at a time
- compact episode rows with code, air date, runtime, watched state, and playback availability
- mark season watched/unwatched actions

The mobile overlay places artwork above content, keeps close/actions reachable, and prevents horizontal overflow.

## Provider Settings

Provider configuration belongs under Settings, not Player.

Supported provider types:

- Xtream-compatible API
- M3U playlist
- XMLTV EPG
- Manual source

Plex, Jellyfin, and Emby remain future placeholders.

Provider configuration supports:

- display name
- type-specific server, username, password, or playlist fields
- optional XMLTV URL and EPG time shift
- refresh frequency
- enable/disable
- legal ownership confirmation
- safe connection test
- catalog refresh
- deletion

Editing never repopulates a saved password or raw URL. Summary responses expose booleans such as `credentialsConfigured`, `serverConfigured`, and `xmltvConfigured`, not the underlying values.

Manual providers may receive individually entered source items in Settings. Imported providers populate source items through catalog refresh instead.

## Provider Catalog

The catalog importer stores private provider inventory in user-scoped `playback_source_items`.

Supported catalog kinds:

- `live`
- `movie`
- `show`
- `episode`

Stored catalog data includes the provider item identity, title, kind, category, duration, year, match status, favorite state, safe public-like metadata, encrypted artwork locator, encrypted playback locator, timestamps, and active/unavailable status.

Refresh behavior:

- creates newly discovered items
- updates items seen again
- marks removed imported items unavailable instead of deleting them
- retains source-to-canonical links
- never deletes canonical media or user activity
- writes summary-only audit and media events
- records only safe error codes on failure

The command equivalent is:

```bash
php artisan mediahub:refresh-provider {provider_id}
```

## Catalog Linking

Catalog items have one of these match states:

- `linked`
- `suggested`
- `needs_review`
- `ignored`

Exact TMDB identity is the strongest deterministic suggestion. Exact normalized title may suggest a movie or show. Episode title matching additionally requires valid season and episode numbers. Suggestions never create a link automatically; the user confirms the canonical target.

A provider item can link only to canonical media owned by the same user. Unlinking or removing a provider does not delete canonical history.

## Player

Without an active provider, Player shows a private-playback empty state and sends the user to Provider Settings. Dashboard, discovery, manual library actions, ratings, notes, and history remain usable.

With an active provider, Player provides:

- Home
- Movies
- Shows
- Live TV
- TV Guide
- Search

Home shelves include Continue Watching, Recently Added Movies, Recently Added Shows, Recently Watched, Linked Library Items, Needs Matching, and Categories.

Catalog APIs return only safe item summaries. Artwork availability and playability are booleans; private locators are not returned.

## Playback And Progress

`POST /api/v1/player/items/{item}/play` is the only endpoint that returns `playbackUrl`. Before returning it, the service validates the source item, provider source, media link, and authenticated user ownership graph.

Playback uses native HTML5 video with HLS.js fallback. Sessions record resume progress and completion:

- linked movie/episode completion updates canonical watch history
- unlinked movie/episode completion stores source-only progress
- live viewing records a playback session but never creates movie or episode history
- repeated completion updates for one session do not duplicate canonical watches

Browser errors use user-safe messages and never log or render a provider URL.

## API Surface

Discovery:

- `GET /api/v1/discover/search`
- `POST /api/v1/discover/movies/{tmdbId}/add`
- `POST /api/v1/discover/shows/{tmdbId}/add`

Provider management:

- `GET /api/v1/providers`
- `POST /api/v1/providers/test`
- `POST /api/v1/providers`
- `PATCH /api/v1/providers/{provider}`
- `POST /api/v1/providers/{provider}/refresh`
- `DELETE /api/v1/providers/{provider}`

Player catalog:

- `GET /api/v1/player/catalog`
- `GET /api/v1/player/items`
- `POST /api/v1/player/sources/{source}/items`
- `PATCH /api/v1/player/items/{item}/favorite`
- `POST /api/v1/player/items/{item}/link`
- `DELETE /api/v1/player/items/{item}/link`
- `POST /api/v1/player/items/{item}/play`
- `PATCH /api/v1/player/sessions/{session}`

Library actions added for cinematic details:

- `POST|DELETE /api/v1/library/movies/{movie}/watchlist`
- `POST|DELETE /api/v1/library/shows/{show}/watchlist`
- `POST|DELETE /api/v1/library/shows/{show}/seasons/{season}/watch`

## Security Boundaries

- Every query starts from the authenticated user's scope.
- Route-model-bound provider records are revalidated by services.
- Source item ownership includes checking the parent source owner.
- Media links can target only same-user canonical records.
- Provider test and refresh responses contain only booleans, counts, sync state, and safe error codes.
- Event and audit metadata pass through recursive sensitive-key sanitization.
- Provider test, provider refresh, and discovery search are rate-limited.
- Provider responses are rejected before JSON/XML parsing when they exceed the configured byte ceiling.
- Direct private, loopback, local, or reserved IP provider URLs are rejected.
- Filament shows user, source name, status, sync metadata, item type, link state, and optional hashes; it does not show raw settings or locators.

## Acceptance Invariants

- Discovery works without a provider.
- Duplicate discovery additions reuse the same canonical record.
- TMDB failure never blocks My Library.
- One user cannot discover another user's library state or access another user's provider catalog.
- Provider credentials are encrypted and absent from serialized responses.
- Catalog refresh deactivates missing items without deleting links.
- Provider deletion preserves watches, ratings, notes, and canonical media.
- Live completion creates no movie or episode watch.
- Linked VOD completion creates one canonical watch per session.
- Dashboard, library, timeline, catalog, and provider list payloads contain no raw provider or playback URL.

## Deferred Work

- queued/scheduled refresh for very large catalogs
- image proxy/cache with explicit privacy controls
- richer series drill-down inside provider catalog
- full EPG time-grid visualization
- Plex/Jellyfin/Emby adapters
- recommendations
- additional Kalveri AI matching work
