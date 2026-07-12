# MediaHub Profiles And Friends Specification

Status: V1 foundation implemented locally on 2026-07-12; not committed or deployed.

## Purpose

Profiles and friendships let a member present a deliberately small public identity and connect with people they know. They do not turn MediaHub into a public activity network. MediaHub remains a private entertainment memory first.

The social contract is:

- profiles are private by default
- public sharing is opt-in and field-by-field
- friendship is mutual
- invitations require explicit acceptance
- email, notes, providers, playback data, exports, alerts, and raw history are never public
- MediaHub does not sell viewing history

Chat, messaging, public feeds, automatic activity publishing, ratings publishing, and full-history publishing are outside V1.

## Account Menu

The authenticated top-right account control opens one keyboard-accessible menu with:

1. View Profile
2. Edit Profile
3. Friends
4. Invite Friends
5. Privacy
6. Settings
7. Logout

The menu closes on outside click or Escape, restores focus to its trigger after Escape, supports arrow-key navigation, and exposes visible focus states. Logout exists only as an explicit menu item and shows a pending state while the request is running.

## Profile Model

Profile identity is stored on `users`:

| Field | Purpose | Public rule |
| --- | --- | --- |
| `username` | Unique public handle | Basic identity |
| `display_name` | Optional chosen display name | Basic identity |
| `bio` | Optional profile introduction | Visible only when profile content is allowed |
| `avatar_path` | Optional local avatar path | Basic identity when present |
| `public_profile_enabled` | Master publication switch | Defaults to `false` |
| `profile_visibility` | `private`, `friends`, or `public` | Defaults to `private` |
| `profile_slug` | Stable public route identifier | Unique; reserved values rejected |
| `country` | Optional profile country | Explicitly visible content only |
| `favorite_genres` | Up to 12 selected genres | Explicitly visible content only |
| favorite media/list IDs | User-selected featured content | Same-user IDs only |
| publication switches | Per-content opt-ins | All default to `false` |
| `joined_at` | Membership date | Visible content only |
| `last_active_at` | Internal activity timestamp | Never public |

Reserved profile slugs include application and security-sensitive routes such as `admin`, `api`, `assets`, `invite`, `login`, `profile`, `settings`, and `u`.

Profile updates are authenticated, self-only, validated, and rate-limited. Favorite movie/show/list IDs are intersected with the current user's records; private lists cannot be featured.

## Visibility Contract

`public_profile_enabled=false` always produces the private shell regardless of the selected visibility value.

| Visibility | Guest | Accepted friend | Owner |
| --- | --- | --- | --- |
| Private | Minimal identity shell | Minimal identity shell | Owner view |
| Friends | Minimal identity shell | Explicitly enabled content | Owner view |
| Public | Explicitly enabled content | Explicitly enabled content | Owner view |

The safe preview endpoint always applies guest/public rules even for the owner. The frontend uses this endpoint for “View profile as public”; it does not rely on the normal owner-aware profile response.

## Public Serialization

Public identity allowlist:

- profile slug
- username
- display name
- optional avatar
- effective visibility
- private/content-visible state

Content can be added only when visibility permits it and the matching switch is enabled:

- bio, country, favorite genres, member-since date
- selected aggregate statistics
- selected favorite movies
- selected favorite shows
- selected featured public lists

Never serialized publicly:

- email or real account identifiers
- internal user, media, friendship, or list IDs
- role, status, admin access, IP, or device data
- private notes or exports
- alerts or notification settings
- providers, credentials, catalogs, stream locators, playback sessions, or progress
- raw watch history or diary events
- ratings
- last-active timestamp

`show_recent_activity` is stored as a future preference but V1 intentionally emits no recent activity. Enabling the preference does not publish history.

## Privacy Controls

Settings > Privacy > Public Profile includes:

- Enable public profile
- Profile visibility
- Show statistics
- Show favorite movies
- Show favorite shows
- Show public lists
- Show recent activity
- Allow friend requests
- Allow profile sharing
- Allow search discovery

Every switch defaults to off. Profile sharing returns a stable `/u/{profile_slug}` URL with no token. Copy confirmation and the native Web Share API are available when supported.

## Friendships

`friendships` stores one unique unordered user pair through `pair_key`, plus requester, addressee, status, blocker, and lifecycle timestamps.

Statuses:

- `pending`
- `accepted`
- `declined`
- `blocked`

Rules:

- a user cannot friend themselves
- the addressee must explicitly allow requests
- duplicate pair requests are rejected
- only the addressee may accept or decline a pending request
- only a participant may remove an accepted friendship
- a blocked participant cannot delete the other member's block
- either member may block the other
- blocked pairs cannot create requests or accepted invite friendships
- friend and request responses contain safe public identity only, never email

## Friend Invitations

Friend invitations are separate from administrator account invitations.

- a random token is returned once in the share URL
- only its SHA-256 hash is stored
- links expire after seven days
- the inviter can revoke a non-accepted link
- opening records `opened_at`
- acceptance records the accepting user and `accepted_at`
- opening alone never creates a friendship
- the signed-in recipient must explicitly press Accept
- existing members return to the invitation after signing in; the token is preserved without auto-acceptance
- inviter email is never included

Public registration remains disabled. A person without a MediaHub account still needs the existing administrator invitation/onboarding path before accepting a friend invitation.

Invitation statuses are `pending`, `opened`, `accepted`, `expired`, and `revoked`.

## Notifications

Safe in-app social alerts are created for:

- friend request received
- friend request accepted
- invitation accepted

Alert payloads contain only a safe kind, profile slug, and where needed the friendship identifier used by the authenticated app. No social email delivery is enabled by default.

## API

Public or session-aware:

- `GET /api/v1/profiles/{profile_slug}`
- `GET /api/v1/friend-invites/{token}`

Authenticated:

- `GET /api/v1/profile`
- `GET /api/v1/profile/options`
- `PATCH /api/v1/profile`
- `PATCH /api/v1/profile/privacy`
- `GET /api/v1/profile/public-preview`
- `GET /api/v1/profiles/search?query=`
- `GET /api/v1/friends`
- `GET /api/v1/friends/requests`
- `POST /api/v1/friends/request/{profile_slug}`
- `POST /api/v1/friends/{friendship}/accept`
- `POST /api/v1/friends/{friendship}/decline`
- `DELETE /api/v1/friends/{friendship}`
- `POST /api/v1/friends/{profile_slug}/block`
- `GET /api/v1/friend-invites`
- `POST /api/v1/friend-invites`
- `POST /api/v1/friend-invites/{token}/accept`
- `DELETE /api/v1/friend-invites/{invite}`

Search returns only profiles that enabled both the public-profile master switch and search discovery.

## Frontend Screens

- Account dropdown
- Own profile view and editor
- Privacy controls and public preview
- Friends, incoming requests, sent requests, and profile search
- Invite creation, copy/share, status history, and revoke
- Public profile
- Private profile state
- Invitation landing and explicit acceptance

All screens include loading, safe error, empty, and confirmation states. Responsive rules prevent horizontal overflow and preserve the account menu on mobile.

## Testing Contract

Backend coverage proves private/public/friends-only visibility, email and internal-ID exclusion, request ownership, duplicate prevention, block behavior, safe alerts, hashed/expiring invites, explicit invite acceptance, slug uniqueness/reserved values, self-only updates, and same-user profile options.

Frontend coverage proves account-menu open/close/Escape/keyboard/logout behavior, private content suppression, public allowlisted content, friend request, privacy persistence, accept/decline, and invite copy.

Every future social feature must extend the public allowlist deliberately and add a regression test showing that private media data remains absent.
