# Ransom Notes (web game)

A web version of the [Ransom Notes](https://www.geekyhobbies.com/ransom-notes-board-game-rules-and-instructions-for-how-to-play/) board game. Players answer prompt cards by arranging word tiles on a plate; when the round ends, everyone votes (1 = best), and the winner gains a point. First to 5 points wins.

## Stack

- **Backend:** Laravel 12, MySQL, Laravel Sanctum (API auth)
- **Frontend:** Vue 3, Vue Router, Tailwind CSS, Vite

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8 (or use SQLite by keeping `DB_CONNECTION=sqlite` in `.env` and creating `database/database.sqlite`)

## Setup

1. **Install PHP dependencies**
   ```bash
   composer install
   ```

2. **Environment**
   - Copy `.env.example` to `.env` and run `php artisan key:generate`
   - For MySQL: set `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`
   - Or use SQLite: leave `DB_CONNECTION=sqlite` and ensure `database/database.sqlite` exists

3. **Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   This creates:
   - **Admin:** `admin@example.com` (password from `UserFactory` / default)
   - **User:** `test@example.com`
   - A set of word tiles for the game

4. **Frontend**
   ```bash
   npm install
   npm run build
   ```
   For development: `npm run dev` (and keep it running).

5. **Run the app**
   ```bash
   php artisan serve
   ```
   Open http://localhost:8000

## Flow

- **Admins** can create games, create/import prompt cards, start/stop rounds, and complete rounds.
- **Users** register/login, join games via 6-character code, and play only after the game has started.
- **Lobby:** Admin starts a round → a random prompt card is shown.
- **Building:** Each player clicks “Get tiles” (up to 15 per click, 40 max), drags tiles onto their plate (max 30), and submits. Admin can “Stop round” when ready.
- **Voting:** Everyone ranks each submission (1 = best, N = worst). Admin then “Complete round”; the lowest total rank wins the round and gets a point.
- **Between rounds:** Players can “Top up tiles”; admin starts the next round.

## Deploy to IONOS Deploy Now

1. **Connect the repo** in [IONOS Deploy Now](https://www.ionos.com/hosting/deploy-now): create a PHP project and link this GitHub repository. Add the required **IONOS_API_KEY** (and any deployment SSH secrets) as GitHub repository secrets.

2. **Set the web root** in the IONOS project to **`public`** so the Laravel app is served correctly (Document root / Publish directory = `public`).

3. **Push to the branch** that Deploy Now watches (or trigger the workflow from the Actions tab). The build will:
   - Install Composer and npm dependencies and run `npm run build`
   - Render `.deploy-now/ransom_notes/.env.template` as `.env` (with `APP_URL` set to your site URL)
   - Run post-deploy on the server: migrations, `config:cache`, `route:cache`, and `key:generate` if `.env` has no `APP_KEY`

4. **Persistent data** (kept between deploys): `database/database.sqlite`, `storage/`, and `.env`. The SQLite DB and uploads persist; `.env` is kept so `APP_KEY` and any edits are not overwritten.

5. **First deploy:** The server will create the DB and run migrations. Then open your Deploy Now URL and register an account; make an admin user in tinker or your DB if needed.

## Making a user admin

In tinker or a new migration/seeder:

```php
\App\Models\User::where('email', 'user@example.com')->update(['is_admin' => true]);
```

## API (Sanctum)

- `POST /api/register` – name, email, password, password_confirmation  
- `POST /api/login` – email, password  
- Authenticated requests use header: `Authorization: Bearer <token>`

All game actions (games, tiles, rounds, votes, prompt cards) are under `/api/` and documented via the Vue app and `routes/api.php`.
