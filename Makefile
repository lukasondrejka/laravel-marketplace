.PHONY: up down setup seed fresh test coverage pint shell build

up:
	docker compose up -d --build

down:
	docker compose down

setup: up
	docker compose exec app php artisan migrate --seed --force

seed:
	docker compose exec app php artisan db:seed --force

fresh:
	docker compose exec app php artisan migrate:fresh --seed --force

test:
	docker compose exec app php artisan test

coverage:
	docker compose exec app composer test:coverage

pint:
	docker compose exec app vendor/bin/pint

build:
	docker compose run --rm node sh -c "npm install && npm run build"

shell:
	docker compose exec app bash
