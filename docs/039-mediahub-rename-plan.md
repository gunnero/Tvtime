# MediaHub repository rename plan

Recommended final repository: `gunnero/mediahub`

This is a preparation plan only. It does not authorize or perform a GitHub rename.

## Impact

GitHub normally redirects repository web and Git transport URLs after a rename, but redirects are not a substitute for updating canonical references. Fork networks, package consumers, local remotes, badges, webhooks, deployment automation, portfolio links, and cached clones require explicit review.

## Before approval

1. Merge the reviewed professionalization PR with all CI checks green.
2. Confirm no open workflow, webhook, deployment, package, or external integration depends on the old slug.
3. Record the current default branch, protection rules, tags, releases, environments, deploy keys, secrets, webhooks, and installed apps without publishing their values.
4. Confirm the target name `mediahub` is available under `gunnero`.
5. Prepare updates for portfolio and case-study links.

## Exact rename sequence after approval

1. In GitHub repository settings, rename `Tvtime` to `mediahub`.
2. Confirm `main` remains the default branch and visibility remains public.
3. Update the local remote:

   ```bash
   git remote set-url origin https://github.com/gunnero/mediahub.git
   git remote -v
   git fetch origin --prune
   git ls-remote origin
   ```

4. Update badges, documentation, portfolio links, package references, and any permitted deployment configuration.
5. Review GitHub Actions, environments, webhooks, installed apps, deploy keys, and repository-scoped secrets.
6. Verify clone, fetch, pull-request, issue, Actions, Dependabot, and release pages.
7. Test the old GitHub URL redirect without treating it as canonical.

## CI impact

Actions workflows that use relative repository context should continue to work. Any external status check, badge, reusable workflow, package publication rule, or hard-coded repository slug must be updated and verified.

## Deployment impact

No deployment change belongs in the public rename commit. Privately maintained deployment systems must update their Git remote and verify a read-only fetch before any later release.

## Documentation and portfolio impact

Update public GitHub links, clone commands, badges, case studies, pinned repositories, and portfolio evidence after the rename succeeds. Do not publish private operational links.

## Webhook and integration review

Inventory GitHub Apps, OAuth integrations, webhooks, dependency services, status badges, code scanners, and automation identities. Confirm each supports redirects or update it explicitly.

## Rollback plan

If a critical integration fails, rename the repository back to `Tvtime`, restore affected remote URLs, and verify default branch, Actions, pull requests, and webhooks. Avoid creating a new repository with either name during the rollback window because that can interfere with redirects.

## Verification checklist

- [ ] Repository is `gunnero/mediahub`
- [ ] Visibility remains public
- [ ] `main` remains default
- [ ] Branches, tags, issues, PRs, and releases remain present
- [ ] Fresh clone and existing clone fetch succeed
- [ ] CI and Dependabot run
- [ ] Badges and documentation resolve
- [ ] Portfolio/case-study link resolves
- [ ] Webhooks and integrations are healthy
- [ ] Old URL redirects without exposing private information
