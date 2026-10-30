# Test strategy and matrix

Stack: Pest 4 on PHPUnit 12, Livewire testing helpers, SQLite in memory (default), `Storage::fake`, `Mail::fake`. Browser tests: `pestphp/pest-plugin-browser` 4.x (5.x needs Pest 5) on Playwright/Chromium.
Run `composer test:coverage` for tests plus coverage (needs pcov, which the Docker image has). Line coverage is about 99% (gate: 95%).

Status legend: **existing** = was there before this review, **added** = written during it, **skipped** = deliberately not tested (reason given).

## Feature areas

| Area | What is verified | Status | Where |
|---|---|---|---|
| Registration, login, logout | Happy paths, validation, wrong password, login throttle, logout | existing | `Feature/Auth/AuthenticationTest` |
| Password reset, email verification | Reset link flow, signed verification link | existing | same |
| Auth endpoint throttling | `throttle:auth` returns 429 | added | `Feature/PlatformTest` |
| Social login | Create, link to verified, no takeover of unverified, reuse, 404 for disabled provider | existing | `Feature/Auth/SocialLoginTest` |
| Social login hardening | Provider-flagged unverified email ignored, no-email placeholder user, provider failure redirect, redirect route, guest-only, throttle, cascade on delete | added | same |
| Browse / search / filter / sort | AND search, wildcard escaping, category, condition, type, price bounds, sort, URL binding, pagination, grid/list | existing | `Feature/Listings/BrowseTest` |
| Browse robustness | Invalid filter values, pagination reset, clear filters, SQL/wildcard input, ASCII case folding | added | same |
| Slovak diacritics search | `zlty` finds `Žltý`, case folding of `Ž` | added, MySQL only | same (`mysql` group, skipped on SQLite) |
| Listing page | SEO tags, JSON-LD, draft/sold/trashed visibility, safe linkification, phone opt-in, related listings, no seller email | existing | `Feature/Listings/ShowTest` |
| Seller page | Active listings only, no private data, 404 for unknown seller | existing + added | `ShowTest`, `PlatformTest` |
| Create / edit listing | Draft vs publish, slug, validation, image upload/reorder/remove, owner-only edit | existing | `Feature/Listings/ManageTest` |
| Form hardening | Tampered status becomes draft, per-field validation matrix, creation rate limit, tampered image order capped, trashed listing edit 404, HTML in title is inert | added | `Feature/Listings/FormSecurityTest` |
| Dashboard | Counts, status tabs, pagination, transitions, invalid status, 404 for trashed/missing, delete confirmation, unverified owner cannot publish | added (basic transitions existing) | `Feature/Listings/DashboardTest`, `ManageTest` |
| Contact seller | Guest and user send, reply-to, validation, no self-contact, inactive listing, rate limit | existing | `Feature/Listings/ContactSellerTest` |
| Profile | Update, email change resets verification, uniqueness, phone format, avatar upload/replace/remove, size and type limits, blank fields stored as null, password change rules, account deletion | existing + added | `Feature/Profile/ProfileTest` |
| Error pages and health | 404 page, 403, `/up` | added | `Feature/PlatformTest` |
| Seeders | Categories idempotent, demo data complete and loginable | added | `Feature/SeederTest` |

## Browser (E2E) level

Run with `make e2e` / `composer test:e2e` (`tests/Browser`, group `e2e`). Outside the `phpunit.xml` suites, so the default run and the 95% coverage gate are unaffected. The plugin serves the app in-process on a random port, so the SQLite in-memory database, `Mail::fake` and `actingAs` work as in feature tests. Test-only glue is in `tests/Pest.php`: built assets are forced (no Vite hot file), media is written to `public/e2e-storage` and removed afterwards, a middleware rebuilds multipart bodies (the plugin's server does not parse them, Livewire uploads need it), uploads are injected from the page because the Playwright server is remote, and `e2eWait()` polls inside the browser because the plugin's assertions do not wait. Selectors are `data-testid` attributes or roles/names.

| Flow | What is verified | Where |
|---|---|---|
| Register, login, logout, wrong password | Real forms and redirects | `Browser/AuthTest` |
| Create listing with upload | File input upload, preview, publish, redirect, saved media | `Browser/ListingFormTest` |
| Drag and drop, reorder, make cover, remove | Drop-zone upload of 3 files, native drag reorder, cover button, order persisted | same |
| Publish from dashboard, delete confirmation | Draft becomes active; cancel keeps, confirm deletes | `Browser/DashboardTest` |
| Gallery lightbox | Open, next, close button, Escape | `Browser/ListingPageTest` |
| Contact seller | Success message, mail to seller with reply-to, validation | same |
| Theme | Toggle, persistence after reload and navigation, system preference fallback | `Browser/ThemeTest` |
| Search, filters, grid/list | Keyword and category, clear filters, empty state, layout in URL | `Browser/BrowseTest` |
| Health | No JS errors, console logs, broken images or failed asset requests on home, browse, listing, login and dashboard in light and dark | `Browser/SiteHealthTest` |
| 404 | Friendly page for unknown URL and listing | same |

Failure screenshots are saved to `tests/Browser/Screenshots`; Playwright traces and video are not produced by the plugin.

## Unit level

| Unit | What is verified | Status |
|---|---|---|
| `ListingPolicy` | view/create/update/delete | existing (`Unit/PolicyTest`) |
| `ListingPolicy::publish` | owner and verified required | added (via dashboard test) |
| `ListingStatus` | Transition matrix, labels, badges | added |
| `ListingCondition`, `ListingType` | Labels, schema.org URLs | added |
| `Listing` model | Slugs (diacritics, reserved, trashed collision, truncation), rich description escaping, teaser, price format, owner check, phone exposure, scopes (active, price, type, search caps, sort), media collection mime types, FK restrict | added (`Unit/Models/ListingTest`) |
| `CreateListing`, `UpdateListing`, `ChangeListingStatus`, `DeleteListing` | Status handling, ignored `user_id`/`status`, first publication date kept, soft delete | added |
| `SyncListingImages` | Order, delete unlisted, foreign ids ignored (IDOR), duplicates and cap, webp `thumb`/`large` conversions | added |
| `ListingInquiry` mail | Subject, reply-to, link, Markdown/HTML neutralised | added |
| `PlaceholderImage` | Deterministic JPEG of requested size | added |
| `LoginWithProvider` | Covered through the social login feature tests | added |

## Cross-cutting

| Concern | Status | Notes |
|---|---|---|
| Authorization on every mutating Livewire action | added | Dashboard (`setStatus`, `delete`), Form (`save`, edit mount), ContactSeller, Profile components |
| IDOR on images | existing + added | Foreign media ids ignored in `ManageTest` and `SyncListingImagesTest` |
| Mass assignment | added | `user_id`, `status`, `published_at` in action input are ignored |
| Rate limits | added | Inquiry (existing), listing creation, auth endpoints, social endpoints, login (existing) |
| Upload limits and mime checks | existing + added | Non-image, oversized, too many, svg/gif avatars |
| XSS | existing + added | Description linkification, HTML title, email Markdown injection |
| i18n key completeness | added | Every `__()` literal exists in `lang/en.json`, no unused keys, identity mapping (`Feature/I18nTest`) |
| N+1 / lazy loading | implicit | `Model::preventLazyLoading()` is on outside production, so any N+1 on a rendered page fails the tests |
| Console / seed | added | Seeders; `inspire` is framework boilerplate |
| Static quality | n/a | Pint (`composer lint`) and Larastan level 5 without baseline (`composer analyse`) |

## Deliberately skipped

- **Upload progress bar, touch gestures and other browsers**: the browser tests run Chromium only, at desktop size.
- **Real OAuth round trip with Google/Facebook**: depends on third parties; the provider is mocked at the Socialite factory boundary.
- **Real mail delivery / Mailpit**: `Mail::fake` plus rendering the mailable.
- **Queue workers**: conversions run on the `sync` queue in tests; worker behaviour is Laravel's.
- **Client-tampered `newImages` with a non-image Livewire temp file**: Livewire's test harness cannot forge a signed temp upload outside the upload path; the server re-validates in `Form::save` and the media collection also rejects wrong mime types.
- **Session invalidation on password change**: not implemented (needs `auth.session` middleware on Livewire requests); documented, not tested.
- **Slug race conditions** (two simultaneous creates with the same title): the unique index is the guard; reproducing it needs real concurrency.
- **Accent-insensitive search on SQLite**: not a supported behaviour of SQLite `LIKE`; covered on MySQL (CI job `tests-mysql`, or `DB_CONNECTION=mysql php artisan test`).
- **Blade presentation details and CSS**: asserted only where they carry behaviour (escaping, links, visibility).
- **`routes/console.php` `inspire` command**: framework boilerplate.

## Not covered by line coverage

`Support/PlaceholderImage` has a few GD error branches, `Models/Category` the unused `listings()` relation. Neither is worth a test.
