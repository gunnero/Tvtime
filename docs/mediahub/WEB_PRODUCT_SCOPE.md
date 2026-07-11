# MediaHub Web Product Scope

Status: Web V1 implementation baseline

## Product Promise

MediaHub Web is a free, private entertainment memory for discovering, tracking, and understanding movies and television. It is provider-independent: canonical library data, watch history, ratings, notes, lists, and events remain useful when a playback source changes or disappears.

## Free Web V1

Web V1 includes:

- session-authenticated personal accounts
- Home dashboard and entertainment diary
- backend-only TMDB discovery with My Library and Discover separation
- paginated movie, show, episode, and watch-history browsing
- manual watched and unwatched actions
- watchlists, ratings, and private notes
- show progress, seasons, episodes, and Specials
- release calendar for relevant movies and episodes
- in-app alerts and notification preferences
- database-derived statistics
- private manual lists with ordering
- private TV Time import workflow
- user-owned JSON and CSV exports
- metadata status, privacy, account, and version information

No Web V1 capability is subscription-gated.

## Web Navigation

The stable navigation order is:

1. Home
2. Discover
3. Movies
4. Shows
5. History
6. Calendar
7. Alerts
8. Stats
9. Lists
10. Settings

## Deferred Player Surface

The provider and player backend remains intact for compatibility and future native clients. It is not part of the normal Web V1 product flow.

The default runtime flags are:

```dotenv
MEDIAHUB_WEB_PLAYER_ENABLED=false
MEDIAHUB_WEB_PROVIDERS_ENABLED=false
```

When disabled, the web client hides Player navigation, provider setup, Play actions, and provider status. This is presentation gating only. It must never delete provider data, migrations, admin support, APIs, or ownership tests.

## Identity Model

- TMDB is the primary source for public canonical metadata.
- IMDb IDs are secondary external references.
- Imported IDs and manually corrected fields are preserved.
- User-owned history and annotations are canonical MediaHub data, not TMDB data.
- Missing metadata must never hide imported canonical records.

## Explicitly Deferred

- native provider configuration and native playback
- subscriptions, payments, premium gates, and entitlements
- Kalveri AI recommendations or smart collections
- push notifications
- family profiles
- streaming-service availability aggregation

Web V1 quality and data trust come before monetization or expanded intelligence.
