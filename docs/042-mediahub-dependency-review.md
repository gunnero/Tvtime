# MediaHub dependency review

Review date: 2026-07-13

## Frontend inventory

The root application contains React/Vite runtime and test tooling. The initial inventory resolved approximately 252 package-version nodes. Direct dependencies are React, React DOM, Phosphor icons, HLS.js, Vite, and the React Vite plugin; Vitest, Testing Library, jsdom, and Playwright support validation.

Initial `npm audit --json` result:

- Critical: 0
- High: 1
- Moderate: 0
- Low: 0

The high result affected direct development dependency Vite `6.4.2` through Windows development-server path handling advisories. The non-major patched version `6.4.3` was available and selected. It changes build/development tooling rather than browser runtime code. Final audit and build results are recorded in the professionalization review.

## Backend inventory

Composer reports ten direct packages across runtime and development requirements. Principal runtime packages are Laravel, Filament, Tinker, and the GD extension requirement; test and quality dependencies include PHPUnit, Pint, Mockery, Faker, Pail, Collision, and Pao.

Initial `composer audit --format=json` result:

- Security advisories: 0
- Abandoned packages: 0

## Exposure assessment

- Vite and frontend testing packages are development/build dependencies.
- React and HLS.js execute in the browser and receive higher runtime scrutiny.
- Laravel and Filament define the primary authenticated server attack surface.
- Optional metadata and provider integrations remain server-side trust boundaries and must not expose credentials to browser bundles.

## Policy

- Do not use `npm audit fix --force`.
- Prefer reviewed patch/minor updates with tests and production builds.
- Treat high or critical unresolved advisories as a blocker to main promotion.
- Keep Dependabot changes small and grouped by ecosystem.
- Re-run both audits before release candidates.
