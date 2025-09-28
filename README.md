# Habit-tracking app

## 日本語ガイド
An explanation in English is provided below
### 概要
Laravel 12 製の習慣トラッカーです。毎日のタスク（チェックイン）を登録し、達成状況やスキップ理由を記録しながら、タイムログや日報も管理できます。カレンダー表示や履歴ページで進捗を振り返ることが可能です。

### 主な機能
- 当日のタスクを作成・編集し、完了やスキップ（理由付き）を記録。前日の内容を自動引き継ぎ。
- ブラウザ上のタイマーと API で作業時間を記録し、一覧やカレンダーで可視化。
- 日報に活動内容と気分 / 努力度 (1-5) を残し、履歴を参照・編集。
- FullCalendar によりタスクとタイムログを重ねて表示。
- 直近 30 日間の非スキップ率を集計し、平均値やチャートで確認。

### 技術スタック
- PHP 8.2 / Laravel 12
- MySQL 8（Docker 構成では phpMyAdmin 付き）
- Vite / Tailwind CSS / Alpine.js
- Docker Compose（PHP-FPM, Nginx, MySQL, phpMyAdmin）

### 1. ローカル環境での動作
要件: PHP 8.2+, Composer, Node.js 18+, npm 9+

```bash
cp .env.example .env              # DB_* や APP_URL を環境に合わせて変更
composer install
npm install
php artisan key:generate
php artisan migrate --seed        # シードが不要なら省略可
npm run dev                       # Vite 開発サーバー (http://localhost:5173)
php artisan serve                 # Laravel サーバー (http://127.0.0.1:8000)
```

`vite: command not found` が表示された場合は `npm install` が完了しているか確認してください。

### 2. Docker ワークフロー
要件: Docker Desktop または Docker Engine + Docker Compose v2

1. `.env` の DB 接続設定をコンテナ向けに変更します。
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=secret
   ```
2. コンテナをビルドして起動します。
   ```bash
   docker compose up --build -d
   ```
3. 初回のみ、PHP コンテナ内で依存関係をインストールします。
   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```
4. 必要に応じて Vite の開発サーバーを起動します。
   ```bash
   docker compose exec app npm run dev -- --host 0.0.0.0 --port 5173
   ```

アクセス先:
- アプリ (Nginx) → http://localhost:8080
- Vite HMR → http://localhost:5173
- phpMyAdmin → http://localhost:8081 (`laravel` / `secret`)
- MySQL → localhost:3308（外部クライアント接続用）

終了する場合:
```bash
docker compose down
```

### アセットのビルド
```bash
npm run build
```
Docker 使用時:
```bash
docker compose exec app npm run build
```

### テスト
```bash
php artisan test
```
Docker 使用時:
```bash
docker compose exec app php artisan test
```

### よく使う Artisan コマンド
```bash
php artisan migrate:fresh --seed
php artisan queue:listen
php artisan cache:clear
```

---

## English Guide

### Overview
A Laravel 12 application for tracking daily habits and time usage. Users can plan tasks, mark results, log daily reports, and review progress from calendar and history views.

### Features
- Plan, complete, or skip daily check-ins with optional reasons and automatic carry-over from the previous day.
- Lightweight time tracking with browser timers, API-backed logs, and calendar overlays.
- Daily reports capturing reflections plus mood and effort ratings.
- Dashboard calendar that combines scheduled tasks with recorded time logs via FullCalendar.
- History page visualising non-skip rates and trends across the last 30 days.

### Tech Stack
- PHP 8.2, Laravel 12
- MySQL 8 (phpMyAdmin provided in Docker stack)
- Vite, Tailwind CSS, Alpine.js
- Docker Compose: PHP-FPM, Nginx, MySQL, phpMyAdmin

### 1. Local Development
Prerequisites: PHP 8.2+, Composer, Node.js 18+, npm 9+

```bash
cp .env.example .env              # adjust DB_*, APP_URL, etc.
composer install
npm install
php artisan key:generate
php artisan migrate --seed        # optional seeders if available
npm run dev                       # Vite dev server (http://localhost:5173)
php artisan serve                 # Laravel app (http://127.0.0.1:8000)
```

If you see `vite: command not found`, ensure `npm install` completed successfully.

### 2. Docker Workflow
Prerequisites: Docker Desktop or Docker Engine + Docker Compose v2

1. Adjust `.env` to point at the containers:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=laravel
   DB_PASSWORD=secret
   ```
2. Build and start the stack:
   ```bash
   docker compose up --build -d
   ```
3. Install dependencies inside the PHP container (first run only):
   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```
4. Start the Vite dev server inside the container when needed:
   ```bash
   docker compose exec app npm run dev -- --host 0.0.0.0 --port 5173
   ```

Service endpoints:
- App (Nginx) → http://localhost:8080
- Vite HMR → http://localhost:5173
- phpMyAdmin → http://localhost:8081 (default credentials `laravel` / `secret`)
- MySQL → localhost:3308 (for external clients)

Stop and remove containers when finished:
```bash
docker compose down
```

### Building Assets
```bash
npm run build
```
When using Docker:
```bash
docker compose exec app npm run build
```

### Testing
```bash
php artisan test
```
With Docker:
```bash
docker compose exec app php artisan test
```

### Useful Artisan Commands
```bash
php artisan migrate:fresh --seed
php artisan queue:listen
php artisan cache:clear
```

## License
This project is open source under the MIT license.
