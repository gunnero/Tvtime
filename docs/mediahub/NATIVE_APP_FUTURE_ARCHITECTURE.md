# Native App Future Architecture

Status: architecture boundary only; no native implementation exists

## Shared Account And Canonical Data

Future iOS, macOS, Android, and TV clients use the same versioned MediaHub API and account. Movies, shows, episodes, history, ratings, notes, lists, calendar state, and media events remain server-side canonical data.

An action completed on a native device must appear on Web through the normal API without a second import.

## Device-Owned Provider Boundary

Provider credentials should be stored in the platform's protected device storage, such as Keychain or Android Keystore. Raw credentials and playback locators should not be returned to the web client.

The native client is responsible for:

- provider connection and catalog ingestion where permitted
- local credential storage
- native playback and track selection
- local buffering and offline-safe progress capture
- sending normalized catalog metadata and progress events to MediaHub

The server remains responsible for:

- authentication and device-session authorization
- canonical identity and same-user media links
- permanent watch history
- ratings, notes, lists, alerts, and statistics
- accepting progress/completion updates with idempotency keys

## Playback And Sync

Preferred playback path is provider to native device. Video bytes should not transit the MediaHub web server by default. Native clients send only playback session state, progress, completion, and safe diagnostics.

Offline events use device-generated UUIDs and monotonic sequence numbers. On reconnect, the server accepts idempotent events, rejects another user's media IDs, and resolves progress by timestamp plus completion state. Completion must never be rolled back by stale progress.

## API Requirements

- stable `/api/v1` contracts until a versioned replacement exists
- scoped device tokens with revocation and rotation
- per-device session inventory
- idempotency keys for writes and offline replay
- cursor-based sync for large history and catalog changes
- server timestamps in UTC and user display timezones
- no secret fields in list, dashboard, event, or diagnostics payloads

## Platform Strategy

1. macOS companion proof of concept for local provider connectivity and catalog normalization
2. iOS client sharing account, library, and progress contracts
3. Android client with the same API contracts
4. TV clients after remote-control, focus, subtitle, and long-session behavior is proven

Platform playback implementations may differ, but canonical history contracts must not.

## Security

- device registration requires an authenticated account
- lost devices can be revoked without deleting history
- credentials remain encrypted at rest on the device
- sensitive values are never placed in analytics or media events
- native logs use safe error codes, never URLs or credentials
- transport uses HTTPS and certificate validation
- app backups must exclude credentials unless protected by platform secure backup
