# GitHub professionalization review: MediaHub

Date: 2026-07-13

Branch: `program/github-professionalization-004-mediahub`

## Executive assessment

MediaHub is ready for focused pull-request review as a substantially safer and more recruiter-readable public repository. The current tree no longer contains live operational runbooks or unproven poster binaries, and product identity, architecture, security posture, validation, and rename impact are explicit.

## Security findings

- No recognized secret was found in the current tracked tree or full Git history.
- No private key, GitHub/AWS/OpenAI/Slack token pattern, tracked database dump, backup archive, or user export was found.
- Public operational evidence was the material finding and has been removed or generalized.
- Full-history operational references remain in immutable public commits; they are not credentials and do not justify history rewriting. Private infrastructure owners should still treat previously published topology as known information.

## Public-evidence cleanup

Removed executable deploy/rollback tooling, SSH documentation, environment-specific virtual-host templates, domain migration instructions, internal handover notes, stabilization logs, machine-specific import defaults, hard-coded canonical staging metadata, and real server topology from current-tree documentation.

## Branding and rename readiness

Repository-facing identity is MediaHub. TV Time remains only where it accurately describes import compatibility, provenance values, command names, or tests. The repository is ready for the separately approved rename sequence in `039-mediahub-rename-plan.md`; no rename or GitHub metadata change was performed.

## README review

The README is now a concise recruiter-oriented product document with status, boundaries, architecture, privacy, evidence, setup, testing, documentation, security, roadmap, license posture, and trademark disclaimer. It makes no launch, adoption, customer, or revenue claim.

## Screenshot review

The approved screenshot set uses a local synthetic fixture, contains no real identity or watch history, avoids third-party poster artwork, and has stripped metadata. Four high-signal views cover dashboard, diary, statistics, and mobile responsiveness.

## Asset strategy

Eight undocumented generated poster PNGs were removed. They were not used by supported frontend rendering and had no sufficient provenance record. Product-owned SVG identity assets and governed synthetic screenshots remain.

## Architecture review

`docs/architecture.md` and its reviewed SVG describe frontend/backend, authentication, privacy, imports, diary/history, discovery, social, jobs, provider boundaries, storage, notifications, and generic release principles without operational topology.

## CI status

CI now separates frontend, backend, repository-quality, and full-history secret scanning with read-only permissions. Final local results must be copied into the PR description and confirmed by hosted checks before merge.

## Dependency status

Composer reported no advisories or abandoned packages. The initial npm audit found a high-severity Vite development-server advisory; Vite was patched from `6.4.2` to `6.4.3` without a major upgrade. No high or critical advisory may remain at promotion time.

## License recommendation

**Proprietary source-visible pending approved legal wording.** The unreviewed Laravel starter MIT declaration was replaced with Composer's `proprietary` package marker. No root `LICENSE` file was added, and the README does not call MediaHub open source.

## Branch and main recommendation

Current `main` is the strongest complete lineage. Promote this branch through one reviewed PR after hosted CI is green. Do not cherry-pick the docs onto an older release tag.

## Recruiter-readiness score

- Before: **43/100**
- After local validation: **88/100**

The remaining gap is primarily external: hosted CI evidence, legal license wording, repository metadata, approved GitHub rename, and ongoing public case-study maintenance.

## Remaining blockers

1. Hosted CI must pass on the PR.
2. License wording requires explicit legal/product approval.
3. GitHub description, homepage, topics, and rename require separate approval.
4. Historical operational details remain visible in prior commits; no history rewrite is recommended because no live secret was found.
5. Repository rename dependencies and external integrations must be inventoried privately.

## Exact next steps

1. Review this branch diff for public-evidence and product accuracy.
2. Push the branch and open a PR using the recommended title/body in the completion report.
3. Require green hosted CI and resolve review comments.
4. Merge without rewriting history.
5. Approve legal wording and repository metadata separately.
6. Execute the documented rename checklist only after those approvals.
