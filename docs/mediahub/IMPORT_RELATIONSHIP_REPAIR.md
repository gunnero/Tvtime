# Import Relationship Repair

## Purpose

Imported archives can contain valid watch totals whose episode, watch, or show relationships are incomplete. The repair command restores only relationships that can be proven from same-user canonical rows.

It never invents episodes, deletes data, or uses aggregate counts as episode identities.

## Commands

Inspect first:

```bash
cd backend
php artisan mediahub:repair-import-relationships {user_id} --dry-run
```

Apply after reviewing the summary:

```bash
php artisan mediahub:repair-import-relationships {user_id} --apply
```

Apply mode creates a private user backup before changing relationships. Command output contains counts only.

## Deterministic Repairs

The service may:

- connect an unlinked episode to one same-user show when all same-user watches for that episode identify exactly that show
- copy a same-user episode's show relationship to a same-user watch that lacks it
- correct a same-user watch whose show conflicts with its same-user episode
- raise show watched and aired counters to the number of canonical same-user watches and episodes
- advance `latest_seen_at` to the latest same-user episode watch

## Never Repaired Automatically

- episodes with zero or multiple plausible shows
- watches without a canonical episode identity
- cross-user relationships
- aggregate-only show totals with no canonical episode rows
- metadata numbering conflicts
- missing TMDB matches

These remain visible in summary counts for manual review.

## Safety Invariants

- dry-run performs no writes
- apply is idempotent
- user scoping is applied to every query and relationship check
- watch history, ratings, notes, media events, and external IDs are never deleted
- no provider settings, credentials, or locators are read or printed
- Specials remain season `0`; they are not renumbered
- missing metadata never controls whether an imported episode is returned

## Verification

After apply, compare:

- each show's canonical episode count with `aired_episodes`
- each show's same-user episode-watch count with `seen_episodes`
- episodes with a show relationship but missing metadata
- duplicate episode IDs/cards
- ambiguous and aggregate-only summary counts

The counters may be greater than the canonical rows when an archive contains aggregate-only history. The command reports that case and leaves the original aggregate intact.

## Local Archive Review 2026-07-11

The dry run for local user `1` reported:

- 7,291 episodes scanned
- zero repairable episode links
- zero repairable watch-show links or mismatches
- 13 show counters that could be raised from canonical rows
- zero ambiguous episodes or watches
- zero aggregate-only show gaps
- zero cross-user episode-show or watch-episode links

A separate identity scan found zero duplicate groups with positive season and episode numbers. It found 15 groups where season or episode numbering is zero, representing 46 extra rows with distinct external IDs. Those are ambiguous imported records, not safe deduplication targets. They remain for manual review and must not be deleted by this command.
