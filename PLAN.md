# PLAN: yourlink.app

## 1. Overview
yourlink.app is a high-performance link shortening service built with Laravel 13, Livewire 4, and TailwindCSS 4. It focuses on user customization, ease of use for guests, and strict DSGVO compliance.

## 2. Technical Stack
- **Framework:** Laravel 13
- **Frontend:** Livewire 4 (SFC/MFC components)
- **Styling:** TailwindCSS 4
- **Database:** SQLite (default, can be swapped for PostgreSQL/MySQL)
- **Testing:** Pest 4
- **Authentication:** Socialite (OAuth), Fortify-like custom implementation for Livewire.

## 3. Database Schema

### `users`
- `id` (ULID)
- `name`
- `email` (unique)
- `password` (nullable for OAuth users)
- `provider_id` (OAuth)
- `provider_name` (OAuth)
- `settings` (JSON: preferences, default expiration, etc.)
- `created_at`, `updated_at`

### `links`
- `id` (ULID)
- `user_id` (ULID, nullable for guests)
- `original_url` (text)
- `hash` (string, unique, 7 chars for guests, customizable for users)
- `title` (string, nullable)
- `description` (text, nullable)
- `is_active` (boolean, default: true)
- `settings` (JSON: password protection, expiration, total click limit)
- `expires_at` (datetime, nullable)
- `created_at`, `updated_at`, `deleted_at` (soft deletes)

### `link_clicks`
- `id` (ULID)
- `link_id` (ULID, constrained)
- `ip_address` (string, anonymized/masked for DSGVO)
- `user_agent` (text)
- `referer` (text, nullable)
- `country` (string, 2 chars, nullable)
- `city` (string, nullable)
- `is_robot` (boolean)
- `created_at`

## 4. Key Features

### Guest Features
- Create short links with 7-character random hashes.
- No account required.
- Basic redirection analytics.

### Registered User Features (Individuals - 100% Free)
- High customization in Link Settings:
    - Custom aliases (slugs).
    - Password protection for links.
    - Expiration dates/times.
    - Click limits.
    - Title/Description overrides for social sharing (OG tags).
- Dashboard to manage links and view detailed (DSGVO-conform) analytics.
- OAuth login (Google, GitHub).
- Email registration with reCaptcha protection.

### Enterprise Features
- Custom domains (future consideration).
- Team management.
- API access.
- CTA: Contact `business@ternis-edv.de`.

## 5. DSGVO Compliance (Privacy by Design)
- **IP Anonymization:** Store masked IP addresses (e.g., `192.168.1.0` instead of `192.168.1.45`).
- **Data Minimization:** Only collect necessary data for redirection and basic analytics.
- **User Rights:** Simple interface for users to delete their account and all associated data.
- **No Tracking Cookies:** Redirection should be cookie-less by default.

## 6. Development Phases

### Phase 1: Foundation (Current)
- [ ] Database Migrations & Models.
- [ ] Factories & Seeders.
- [ ] Basic redirection logic (Hash resolution).

### Phase 2: Authentication
- [ ] Laravel Socialite integration.
- [ ] Registration with reCaptcha.
- [ ] Profile management.

### Phase 3: Shortening Engine
- [ ] Guest shortening (7-char hash).
- [ ] Registered user shortening (Customization).
- [ ] Validation (URL safety, blacklists).

### Phase 4: Frontend & UI
- [ ] Landing page (shortener front-and-center).
- [ ] User Dashboard.
- [ ] Analytics views.
- [ ] TailwindCSS 4 styling.

### Phase 5: Testing & Polishing
- [ ] Pest test suite (Feature & Unit).
- [ ] Browser tests for critical paths.
- [ ] DSGVO audit (IP masking verification).
