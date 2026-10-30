# Marketplace

A classifieds marketplace (think local "buy and sell" ads) built as a showcase Laravel application.
Users post listings with multiple photos, browse and filter other people's listings, and manage their own ads from a dashboard.

> Screenshots: _placeholder_ - add `docs/screenshots/home.png`, `listings.png`, `listing.png`, `form.png` after first run.

## Stack

- PHP 8.4, Laravel 13
- Livewire 4 (class-based components, form objects)
- Tailwind CSS 4 + daisyUI 5 (light/dark themes), Alpine.js (bundled with Livewire), Vite
- Laravel Fortify (login, registration, password reset, email verification), Laravel Socialite (optional Google / Facebook login)
- spatie/laravel-medialibrary (image uploads, webp thumbnails and large conversions)
- MySQL 8.4 in Docker (SQLite in-memory for tests), Mailpit for outgoing mail in development
- Pest 4, Laravel Pint, Larastan

## Features

- Browse listings with URL-bound filters: multi-word keyword search, category, offer/wanted type, condition, price range (0 is a valid bound), sort (newest, price asc/desc), grid or list view, pagination that keeps the query string
- Listing detail page: Alpine.js gallery with lightbox, seller card, contact form that relays a message to the seller by email (the seller's address is never exposed), more listings from the same seller, similar listings, SEO meta tags, canonical URL, Open Graph, JSON-LD `Product`
- Public seller page with avatar, bio and active listings
- Post / edit listings: offer or wanted, drag and drop multi-image upload with progress and preview, drag to reorder, choose cover, remove; phone and city come from the profile (phone is shown only if you opt in per listing); image type/size/count validation; draft or publish
- My listings dashboard: status transitions (draft / active / sold), soft delete
- Accounts: register, login, logout, password reset, email verification (required to post), profile (name, email, phone, city, bio, avatar), password change, account deletion with password confirmation, login and auth-endpoint rate limiting
- Flat categories, decimal prices, foreign keys, indexes, soft deletes, slugs in URLs, real 404 pages
- Light and dark mode: follows the OS preference, toggle is persisted in `localStorage`, no flash on load
- All UI text goes through `__()`; English strings are in `lang/en.json` so another language can be added by dropping in `lang/xx.json`

## Run with Docker

Requires Docker with Compose. No local PHP, Composer or Node needed.

```bash
git clone <repo> marketplace && cd marketplace
docker compose up -d --build
```

The first start of the `app` container installs Composer dependencies, copies `.env.example` to `.env`, generates `APP_KEY`, waits for MySQL, runs the migrations and creates the `public/storage` link (follow progress with `docker compose logs -f app`; the first run takes a couple of minutes). The `node` container installs npm packages and starts the Vite dev server.

Load demo data once the app is up:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Then open:

| URL                   | What                          |
|-----------------------|-------------------------------|
| http://localhost:8000 | The application               |
| http://localhost:5173 | Vite dev server (HMR)         |
| http://localhost:8025 | Mailpit (verification / reset emails) |

Demo account (created by the seeder, email already verified): `demo@example.com` / `password`.
The seeder creates 12 categories, 9 users and ~50 listings with generated placeholder images (made locally with GD, nothing is downloaded).

Set `APP_PORT`, `VITE_PORT` or `MAILPIT_PORT` in your shell or in a `.env` next to `docker-compose.yml` if the default ports are taken. If you change `APP_PORT`, also change `APP_URL` in `.env` so generated image URLs match.

### Useful commands

```bash
make up        # docker compose up -d --build
make fresh     # migrate:fresh --seed
make test      # run the Pest suite
make pint      # format code
make build     # production asset build (npm run build)
make shell     # shell in the app container
docker compose exec app composer analyse   # Larastan
```

Image conversions run through the queue. The default `QUEUE_CONNECTION=sync` processes them inline; to use a worker set `QUEUE_CONNECTION=database` and run `docker compose exec app php artisan queue:work`.

## Tests

```bash
docker compose exec app php artisan test
docker compose exec app vendor/bin/pint --test
docker compose exec app composer analyse
```

Tests use SQLite in memory and `Storage::fake`. They cover authentication, profile management, browsing/search/filter/sort, listing CRUD, image upload rules and ownership checks, status transitions and policies.

## Project structure

```
app/
  Actions/Listings/   CreateListing, UpdateListing, SyncListingImages, ChangeListingStatus, DeleteListing
  Actions/Users/      UpdateProfile, UpdatePassword, DeleteAccount
  Actions/Fortify/    registration and password reset
  Enums/              ListingStatus (draft/active/sold), ListingCondition
  Livewire/           Home, Listings\{Index,Show,Form,Dashboard}, Profile\*, Forms\{ListingForm,ProfileForm}
  Models/             User, Category, Listing
  Policies/           ListingPolicy
  Support/            PlaceholderImage (demo data)
config/marketplace.php  currency and image limits
database/             migrations, factories, seeders (CategorySeeder, DemoSeeder)
docker/               PHP ini and container entrypoint
lang/                 en.json and framework strings
resources/views/
  components/         daisyUI-based Blade components (button, input, select, card, modal, ...)
  livewire/           Livewire component views
  auth/, errors/      Fortify views and error pages
tests/                Pest feature and unit tests
```

## Notes and design decisions

- The cover image is the first image of a listing; "make cover" moves an image to the front.
- Listings are only publicly visible when `active`; `sold` listings stay reachable by URL, drafts are visible to their owner only (everyone else gets a 404).
- Listing slugs are generated once from the title and stay stable when the title changes.
- Contact details come from the profile: the phone number is shown only on listings where "Show my phone number" is ticked; everything else goes through the on-site message form.
- Optional social login: set `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` (and/or `FACEBOOK_*`) and the buttons appear. An existing account is linked by email only if that account's email is verified.
- Design: one accent colour on neutral greys, flat bordered surfaces, small radii, no gradients or shadows; tokens live in `resources/css/app.css` as daisyUI themes (light and dark).
- Larastan runs at level 5 with no baseline. Enum casts are declared with `@property` annotations on `Listing` because the analyser does not resolve them from `casts()`.

## License

MIT
