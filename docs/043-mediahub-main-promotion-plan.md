# MediaHub main-promotion plan

## Lineage assessment

At the audit baseline, local `main` and `origin/main` both pointed to `1108c937200a59994c941f756398946bae37a37b`. The remote exposed no additional application branch, and the local repository contained no stronger unpublished lineage. The release-candidate tags are ancestors of current `main`.

## Recommendation

Use `main` as the merge base and promote this professionalization work through a normal pull request from `program/github-professionalization-004-mediahub`. Do not cherry-pick documentation onto an older tag or create a replacement repository.

## Required gates

1. Gitleaks current-tree and full-history scans pass.
2. Private-topology, credential, export, dump, and backup scans pass.
3. Frontend and backend tests pass from clean installs.
4. Production frontend build, Laravel caches, and dependency audits pass.
5. Markdown links, GitHub YAML, architecture SVG, and diff checks pass.
6. Screenshot evidence is synthetic and metadata-free.
7. Review confirms no live operational behavior or infrastructure was changed.

## Promotion method

Open a PR, require green hosted CI, review the public diff, and merge according to the repository's normal reviewed strategy. Repository rename and metadata changes are separate post-merge approvals.

## Blockers

Any live secret, high/critical unresolved advisory, personal-data artifact, private topology reference, broken CI check, or uncertain screenshot/asset right blocks promotion.
