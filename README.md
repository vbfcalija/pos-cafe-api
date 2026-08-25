# pos-cafe-api

Laravel backend for Brothrrs Cafe's POS — a single-cafe point-of-sale system. Paired with `pos-cafe-client` (Nuxt) in this repo.

See [.claude/skills/brothrrs-cafe-backend/SKILL.md](../.claude/skills/brothrrs-cafe-backend/SKILL.md) for the conventions this codebase follows: layering, the data model, auth, and what's deliberately *not* included (no fiscal/tax-authority complexity — see the sibling project's much larger POS if that's ever needed).

## Setup

Requires a running MySQL/MariaDB server. Create the database, then:

```bash
composer install
cp .env.example .env
php artisan key:generate
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS pos_cafe_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

`.env.example` ships with `DB_DATABASE=pos_cafe_db`/`DB_USERNAME=root`/no password — adjust to match your local MySQL setup. The test suite runs against an in-memory SQLite database instead (see `phpunit.xml`), so `php artisan test` doesn't touch `pos_cafe_db` at all.

## Development

```bash
php artisan serve
```

## Testing

```bash
php artisan test
./vendor/bin/pint --test
```

## Auth

`POST /api/auth/register` and `POST /api/auth/login` issue a Sanctum bearer token straight off the `User` model — no roles, no device/terminal concept. Matches what `pos-cafe-client`'s `AuthService` already expects.
