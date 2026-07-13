# MediaHub screenshot policy

## Rules

- Use synthetic demo identities and viewing data only.
- Never show real names, emails, account identifiers, private history, provider locators, internal URLs, browser tabs, or infrastructure.
- Capture from a reviewed local revision with network access constrained to local application resources.
- Remove EXIF, IPTC, XMP, comments, and filesystem metadata before commit.
- Use consistent framing and accessible README alt text.
- Re-review screenshots when UI, data contracts, or privacy behavior changes.

## Approved evidence set

Source revision: professionalization branch derived from `1108c937200a59994c941f756398946bae37a37b`.

| Asset | Viewport | Data | Approval |
| --- | --- | --- | --- |
| `mediahub-dashboard.png` | 1440×1000 | Synthetic | Approved for repository review |
| `mediahub-diary.png` | 1440×1000 | Synthetic | Approved for repository review |
| `mediahub-statistics.png` | 1440×1000 | Synthetic aggregates | Approved for repository review |
| `mediahub-mobile.png` | 390×844 | Synthetic | Approved for repository review |

The capture workflow uses a local demo fixture. It does not authenticate to or read from a live environment.

## Sanitization checklist

- [x] Synthetic profile and history
- [x] No email or account ID
- [x] No private host or browser chrome
- [x] No third-party poster artwork
- [x] Image metadata stripped
- [x] Dimensions recorded
- [x] Alt text reviewed

## Rights and attribution

Screenshots depict the MediaHub interface and synthetic text-only/neutral artwork owned by this project. No redistributed movie or television poster artwork is approved in this evidence set.
