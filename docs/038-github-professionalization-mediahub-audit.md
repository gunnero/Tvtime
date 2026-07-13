# GitHub professionalization audit: MediaHub

Date: 2026-07-13

Audited revision: `1108c937200a59994c941f756398946bae37a37b`

Repository at audit time: `gunnero/Tvtime` (public)

Default branch: `main`

## Executive assessment

MediaHub is a substantive Laravel and React product with strong user-data boundaries and unusually broad automated coverage. Its public repository presentation did not match that engineering quality. The principal risk was not a committed secret: it was an accumulation of public operational evidence and internal runbooks that disclosed real staging hosts, SSH setup, server aliases, absolute paths, web-server behavior, deployment topology, backup locations, and rollback mechanics.

## Current public state before changes

- One local and remote branch: `main`; no stale remote branches.
- Local `main` matched `origin/main` with zero divergence and a clean worktree.
- No unpushed commits.
- Tags: `v1.0.0-rc1` and `v1.0.0-rc1.1`; GitHub reported no formal releases.
- GitHub repository was public, unarchived, with no description, homepage, or topics configured.
- README branded the application as MediaHub but was more than 400 lines and mixed product positioning with live operations.
- No CI workflow, security policy, contribution guide, canonical changelog, ownership rules, issue forms, PR template, or Dependabot configuration.
- Package metadata still used starter names and an unreviewed MIT declaration in Composer metadata despite no root license grant.

## Local and remote divergence

`main...origin/main` was `0 0`. The required professionalization branch was created directly from the synchronized default branch. No stronger application lineage or unpublished feature branch existed locally or on the remote.

## Public-evidence risks

High-priority cleanup targets included:

- Real staging and proposed production hostnames
- SSH aliases, key setup, usernames, and server access commands
- Absolute application, public-root, import, backup, and virtual-host paths
- Web-server and process-manager topology
- Detailed access-control changes and staging protection behavior
- Executable deploy and rollback scripts tied to a real environment
- Internal handover and stabilization notes containing operational state
- A machine-specific personal import path in the compatibility importer

The tree contained no committed database dump, GDPR export, backup archive, private key, or recognized service token. Gitleaks reported zero current-tree and zero full-history findings across 36 commits.

## Branding inconsistencies

- GitHub repository name remained `Tvtime` while application and README branding used MediaHub.
- Root npm package was named `dashboard`; backend Composer metadata retained Laravel starter identity.
- A live canonical hostname was hard-coded in `index.html`.
- Backend dashboard copy described itself as a “TV Time Laravel backend.”
- Factual TV Time import identifiers and compatibility wording are valid and should remain narrowly scoped to imports.

## Asset-rights findings

Eight PNG poster files were tracked under `public/assets/generated/`, totaling about 20 MB. They were 1024×1536, contained no EXIF/IPTC/XMP metadata, and were named as generated assets, but the repository contained no source prompts, generator manifest, license, attribution, or reproducibility script. Application code already rejected these paths and rendered neutral artwork instead, so they were not required by the product. The safe current-tree decision is removal pending documented provenance.

## Branch recommendations

- Keep `main` as the base and strongest complete lineage.
- Review this work through `program/github-professionalization-004-mediahub`.
- Do not archive, delete, or rewrite existing refs.
- Rename the GitHub repository only after this branch is reviewed and merged, CI is green, and external integrations are inventoried.

## Recruiter-readiness score before changes

**43/100**

| Area | Score | Reason |
| --- | ---: | --- |
| Product substance | 17/20 | Strong product and tests, but positioning was buried. |
| Security/public evidence | 6/20 | No leaked token, but extensive operational disclosure. |
| Documentation | 8/15 | Deep internal notes, weak public information architecture. |
| CI and repository hygiene | 3/20 | Core community and validation files were missing. |
| Brand and presentation | 5/15 | MediaHub UI, obsolete GitHub repository identity. |
| Evidence and asset rights | 4/10 | No reviewed screenshots; poster provenance undocumented. |

## Audit conclusion

The repository was technically credible but publicly overexposed and poorly packaged. A focused cleanup can make it recruiter-ready without changing product scope, history, visibility, deployment state, or GitHub settings.
