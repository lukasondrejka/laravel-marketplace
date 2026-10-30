# Marketplace

Classifieds marketplace built with Laravel 13 and Livewire 4.

## Run with Docker

Requires Docker with Compose. No local PHP, Composer or Node needed.

```bash
docker compose up -d --build      # first start installs dependencies, creates .env,
                                  # generates APP_KEY, runs migrations, links storage
docker compose exec app php artisan migrate --seed   # load demo data
```

Then open:

- App: http://localhost:8000
- Vite dev server: http://localhost:5173
- Mailpit (outgoing mail): http://localhost:8025

Useful commands: `make up`, `make setup`, `make fresh`, `make test`, `make pint`, `make shell`.
