# ExpenseTrack — Production Deployment Guide

**Stack:** Laravel 11 · PHP 8.2 · PostgreSQL (Neon.tech) · Docker · Render Free Tier

---

## Pre-flight Checklist

Before you start, make sure you have accounts at:

| Service | URL | Cost |
|---------|-----|------|
| **GitHub** | github.com | Free |
| **Neon.tech** | neon.tech | Free (0.5 GB) |
| **Render** | render.com | Free Web Service |

You also need **Git** installed locally and the **GitHub CLI** (`gh`) — or you can do GitHub steps via the web UI.

---

## Part 1 — Prepare the Codebase

### 1.1 Verify the project builds cleanly

```bash
cd D:\setec\Year4\Claude\expense-tracker

# Confirm no PHP syntax errors
php artisan about

# Confirm local tests pass (if you add any)
php artisan migrate:fresh --seed
```

### 1.2 Create a `.gitignore` (if not present)

Laravel ships with a `.gitignore`. Confirm these lines are in it:

```
/vendor
/node_modules
.env
/storage/*.key
/public/hot
/public/storage
/bootstrap/cache/*.php
database/database.sqlite
```

The `.env` file must **never** be committed — all secrets travel through Render's environment variable dashboard.

---

## Part 2 — Push to GitHub

### 2.1 Initialise the repository

```bash
cd D:\setec\Year4\Claude\expense-tracker

git init
git add .
git commit -m "Initial commit – ExpenseTrack Laravel app"
```

### 2.2 Create a remote repository and push

**Option A — GitHub CLI (fastest):**

```bash
gh repo create expense-tracker --public --source=. --remote=origin --push
```

**Option B — GitHub web UI:**

1. Go to **github.com → New repository**
2. Name it `expense-tracker`, keep it **Public** (required for Render free tier), click **Create repository**
3. Back in your terminal:

```bash
git remote add origin https://github.com/YOUR_USERNAME/expense-tracker.git
git branch -M main
git push -u origin main
```

### 2.3 Confirm the push

Open `https://github.com/YOUR_USERNAME/expense-tracker` — you should see all project files including the `Dockerfile` and `render.yaml` in the root.

---

## Part 3 — Create the PostgreSQL Database on Neon.tech

### 3.1 Sign up and create a project

1. Go to **neon.tech** → **Sign up** (free, no credit card)
2. Click **"New Project"**
3. Fill in:
   - **Project name:** `expense-tracker`
   - **Database name:** `neondb` (default is fine)
   - **Region:** Choose the region closest to you (e.g., `AWS us-east-2`)
4. Click **"Create project"**

### 3.2 Copy the connection string

After creation, the dashboard shows a **Connection Details** panel.

1. Select the **"Connection string"** tab
2. Make sure the format is set to **"Postgres"** (not psql/nodejs)
3. Copy the string — it looks like:

```
postgresql://chivorn:AbCdEfGh@ep-rapid-snow-123456.us-east-2.aws.neon.tech/neondb?sslmode=require
```

> **Important:** Note the `?sslmode=require` at the end — this is mandatory. Neon.tech refuses unencrypted connections.

Save this string somewhere safe temporarily. You will paste it into Render in Part 4.

### 3.3 Run the schema manually (optional verification step)

If you want to verify the connection before deploying, you can run migrations directly against Neon from your local machine:

```bash
# Set env vars temporarily
$env:DB_CONNECTION="pgsql"
$env:DATABASE_URL="postgresql://chivorn:AbCdEfGh@ep-rapid-snow-123456.us-east-2.aws.neon.tech/neondb?sslmode=require"

php artisan migrate
```

You should see the migrations table and both `expenses` and `borrows` tables created. To verify:

```bash
php artisan db:show
```

Expected output lists `expenses`, `borrows`, `sessions`, `cache`, `jobs`, and `migrations` tables.

### 3.4 Raw SQL schema (for manual inspection or DBA review)

If you ever need to recreate the schema by hand (e.g., disaster recovery), here is the exact DDL:

```sql
-- expenses table
CREATE TABLE expenses (
    id         BIGSERIAL PRIMARY KEY,
    date       DATE         NOT NULL,
    amount     NUMERIC(10,2) NOT NULL,
    category   VARCHAR(50)  NOT NULL CHECK (category IN ('Food','Transport','Bills','Entertainment','Other')),
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ  DEFAULT NOW(),
    updated_at TIMESTAMPTZ  DEFAULT NOW()
);
CREATE INDEX idx_expenses_date     ON expenses (date);
CREATE INDEX idx_expenses_category ON expenses (category);

-- borrows table
CREATE TABLE borrows (
    id             BIGSERIAL PRIMARY KEY,
    borrower_name  VARCHAR(255) NOT NULL,
    amount         NUMERIC(10,2) NOT NULL,
    date_borrowed  DATE         NOT NULL,
    due_date       DATE,
    status         VARCHAR(20)  NOT NULL DEFAULT 'unpaid'
                   CHECK (status IN ('unpaid','partially_paid','paid')),
    notes          TEXT,
    created_at     TIMESTAMPTZ  DEFAULT NOW(),
    updated_at     TIMESTAMPTZ  DEFAULT NOW()
);
CREATE INDEX idx_borrows_status       ON borrows (status);
CREATE INDEX idx_borrows_date_borrowed ON borrows (date_borrowed);

-- Safe connection-test seed (idempotent — won't duplicate on re-run)
INSERT INTO expenses (date, amount, category, description)
SELECT NOW(), 1.00, 'Other', 'Connection test – safe to delete'
WHERE NOT EXISTS (SELECT 1 FROM expenses LIMIT 1);
```

---

## Part 4 — Deploy on Render

### 4.1 Create a new Web Service

1. Log in to **render.com**
2. Click **"New +"** → **"Web Service"**
3. Select **"Build and deploy from a Git repository"**
4. Click **"Connect GitHub"** and authorise Render (one-time)
5. Find `expense-tracker` in the repository list and click **"Connect"**

### 4.2 Configure the service settings

On the configuration page fill in:

| Field | Value |
|-------|-------|
| **Name** | `expense-tracker` |
| **Region** | Same region as your Neon database |
| **Branch** | `main` |
| **Runtime** | `Docker` ← Render auto-detects this from the Dockerfile |
| **Instance Type** | `Free` |

Leave the build and start commands blank — the `Dockerfile` and `entrypoint.sh` handle everything.

### 4.3 Add environment variables

Scroll down to the **"Environment Variables"** section. Click **"Add Environment Variable"** for each row:

| Key | Value | Notes |
|-----|-------|-------|
| `APP_NAME` | `ExpenseTrack` | |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `APP_KEY` | *(leave blank)* | Render generates this automatically via `render.yaml` |
| `APP_URL` | `https://expense-tracker-XXXX.onrender.com` | Fill in after first deploy |
| `DB_CONNECTION` | `pgsql` | |
| `DATABASE_URL` | `postgresql://user:pass@host/db?sslmode=require` | Paste from Neon Step 3.2 |
| `DB_SSLMODE` | `require` | |
| `SESSION_DRIVER` | `database` | |
| `CACHE_STORE` | `database` | |
| `QUEUE_CONNECTION` | `sync` | |
| `LOG_CHANNEL` | `stderr` | Sends logs to Render's log viewer |
| `LOG_LEVEL` | `error` | |

> **Tip:** If you already have `render.yaml` in your repo, Render reads most of these automatically. You still need to manually add `DATABASE_URL` because it is marked `sync: false` for security.

### 4.4 Trigger the first build

Click **"Create Web Service"**. Render will:

1. Clone your GitHub repository
2. Build the Docker image (takes ~3–5 minutes first time)
3. Run the `entrypoint.sh` which:
   - Caches Laravel config/routes/views
   - Runs `php artisan migrate --force` against your Neon database
   - Starts nginx + php-fpm via supervisor

Watch the build logs in real time from the Render dashboard.

A successful boot looks like:

```
[boot] nginx will listen on port 10000
[boot] storage permissions set
[boot] Caching config / routes / views...
[boot] Running database migrations...
  2026_05_26_000001_create_expenses_table ......... DONE
  2026_05_26_000002_create_borrows_table .......... DONE
[boot] Migrations complete
[boot] Starting nginx + php-fpm via supervisor...
```

### 4.5 Update APP_URL

Once the deploy succeeds, Render shows your live URL at the top:

```
https://expense-tracker-xxxx.onrender.com
```

Go to **Environment → Edit** and update `APP_URL` to this exact URL. Then click **"Save Changes"** — Render will trigger a re-deploy automatically.

---

## Part 5 — Verify the Deployment

### 5.1 Health check

```bash
curl https://expense-tracker-xxxx.onrender.com/health
# Expected: {"status":"ok","timestamp":"2026-05-26T..."}
```

### 5.2 Smoke-test the pages

Open these URLs in a browser:

| URL | Expected |
|-----|----------|
| `/` | Dashboard with summary cards |
| `/expenses` | Expense tracker table |
| `/borrows` | Borrow management table |

### 5.3 Test a form submission

1. Click **"Add Expense"** on the dashboard
2. Fill in Date, Amount, Category, Description and submit
3. The new row should appear in the table and the dashboard total should update

---

## Part 6 — Render Free Tier Quirks & How to Handle Them

### The 15-minute spin-down

Render's free web service **shuts down** after 15 minutes of zero traffic. The next incoming request wakes it up — this cold start takes **25–40 seconds** because Docker has to restart and run the entrypoint.

The app handles this gracefully:
- `PDO::ATTR_PERSISTENT => false` — no stale connection objects carried across restarts
- Sessions use the **database** driver — stored in PostgreSQL, not in memory, so they survive
- Cache uses the **database** driver — same reason

Neon.tech also auto-suspends its serverless compute after 5 minutes of idle. Both wake up on the first incoming query — total cold-start latency is ~35–45 seconds on the very first request after a long idle period.

### Option A: Accept the spin-down (simplest)

Just warn users that the first load after inactivity is slow. For personal use this is fine.

### Option B: Use a free pinger service to keep the service warm

Services like **UptimeRobot** (free plan) can ping your `/health` endpoint every 5 minutes, preventing the spin-down.

1. Go to **uptimerobot.com** → **Create Monitor**
2. Monitor type: **HTTP(s)**
3. URL: `https://expense-tracker-xxxx.onrender.com/health`
4. Monitoring interval: **5 minutes**

This keeps both Render and Neon.tech warm as long as you stay within UptimeRobot's free tier (50 monitors, 5-minute intervals).

> **Note:** Render's terms of service allow keep-alive pings on free tier as of 2025. Check their current ToS if this matters to you.

---

## Troubleshooting

### Build fails: "composer: command not found"

The Dockerfile uses a multi-stage build. Make sure the `Dockerfile` in your repo root is the one from this project (it imports `composer:2` in the build stage).

### Deploy fails: "could not connect to server"

- Verify `DATABASE_URL` is set correctly in Render's environment variables
- The URL must end in `?sslmode=require`
- Check that Neon.tech project is active (not suspended/deleted)
- Go to Render → Logs and look for the exact PDO error

### "SQLSTATE[08006]" / SSL error

Set `DB_SSLMODE=require` in Render env vars. Neon.tech mandates SSL.

### 500 error after deploy

Turn on debug temporarily:
1. Render → Environment → set `APP_DEBUG=true`
2. Trigger a redeploy
3. Visit the broken page — Laravel's full error page shows the stack trace
4. Fix the issue, then set `APP_DEBUG=false` again

### Migrations run on every restart

That is by design — `migrate --force` is idempotent. Already-applied migrations are skipped in milliseconds.

### Sessions lost between requests

Make sure `SESSION_DRIVER=database` is set in Render env vars. If it is set to `file`, sessions are stored on the ephemeral container filesystem and lost on restart.

---

## Re-Deploying After Code Changes

```bash
# Make your changes, then:
git add .
git commit -m "describe change"
git push origin main
```

Render detects the push and auto-rebuilds. Zero config needed.

---

## Directory Map of Deployment Files

```
expense-tracker/
├── Dockerfile                  ← Multi-stage PHP 8.2-fpm-alpine image
├── render.yaml                 ← Render Blueprint (auto-configures the service)
├── .dockerignore               ← Keeps image lean
├── docker/
│   ├── entrypoint.sh           ← Runs at container boot: migrate → cache → start
│   ├── nginx.conf              ← Main nginx config (gzip, logging, size limits)
│   ├── nginx-site.conf         ← Laravel site block (NGINX_PORT placeholder)
│   ├── supervisord.conf        ← Process manager: php-fpm + nginx
│   └── php.ini                 ← OPcache + production PHP settings
├── config/
│   └── database.php            ← DATABASE_URL support + Neon-safe PDO options
└── routes/
    └── web.php                 ← Includes /health endpoint for Render ping
```
