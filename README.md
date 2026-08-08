# Aura Interiors — Interior Design Studio Website

A complete, production-ready website + admin panel for a Pakistani interior
design company, built with plain PHP 8, MySQL (PDO), HTML5/CSS3 and vanilla
JavaScript — no frameworks required. Designed for a lead-generation goal:
WhatsApp inquiries, calls and consultation-form submissions.

## Stack

- PHP 8+ (PDO, prepared statements throughout, GD for image processing)
- MySQL 8 / MariaDB 10.4+
- Hand-written CSS design system (no Bootstrap/Tailwind bloat) + vanilla JS
- Font Awesome (CDN) for icons, Google Fonts (Fraunces + Plus Jakarta Sans)

## Project Structure

```
/admin              Admin panel (auth-gated) — dashboard, CRUD, settings
/ajax                AJAX endpoints (inquiry form, click tracking)
/assets/css          style.css (public site) + admin.css (admin panel)
/assets/js           main.js (public site) + admin.js (admin panel)
/assets/images/demo  Generated placeholder photography (replace with real work)
/config              config.php, database.php, session.php
/database            database.sql — full schema + demo data
/includes            Shared PHP includes (header, footer, functions, SEO)
/uploads             User-uploaded images (admin panel), PHP execution disabled
install.php          One-time web installer
```

Public pages (`index.php`, `about.php`, `services.php`, `projects.php`, ...)
live in the project root and each simply requires `includes/bootstrap.php`.
There's no front controller / router — this keeps deployment on shared cPanel
hosting trivial (just upload and go) while still separating data (`/config`),
logic (`includes/functions.php`), and presentation (`includes/header.php` /
`footer.php` + each page's own markup).

## Local Setup (XAMPP / WAMP / MAMP)

1. Copy this folder into your server's web root, e.g. `C:\xampp\htdocs\idms`.
2. Start Apache and MySQL.
3. Open `config/config.php` and confirm `DB_HOST`, `DB_USER`, `DB_PASS` match
   your local MySQL (defaults `root` / empty password work for stock XAMPP).
4. Visit `http://localhost/idms/install.php` in your browser and fill in the
   installer form (your name, email, password). This creates the
   `idms_interior` database, loads the schema + demo content, and sets up
   your real admin login with a securely hashed password.
5. Visit `http://localhost/idms/index.php` for the website, and
   `http://localhost/idms/admin/index.php` to log in to the admin panel.
6. **Delete `install.php`** once installed (or leave it — it self-locks via
   `config/installed.lock` and refuses to run twice).

## cPanel / Shared Hosting Deployment

1. Create a MySQL database and user via cPanel → MySQL Databases, and note
   the host/db name/user/password (cPanel usually prefixes these with your
   account name, e.g. `cpuser_idms`).
2. Upload the entire project to `public_html` (or a subfolder) via File
   Manager or FTP.
3. Edit `config/config.php` with your cPanel database credentials.
4. Visit `https://yourdomain.com/install.php` and complete the installer.
5. Delete `install.php` and `tools/` after installation.
6. In the admin panel → **Settings**, replace all placeholder content:
   company name, logo, favicon, phone/WhatsApp number, address, social
   links, hero text, trust statistics, and the Google Maps embed URL.
7. Replace demo photography: every image under `assets/images/demo/` is a
   generated placeholder (labelled "AURA INTERIORS — DEMO IMAGE"). Re-upload
   real project photography through the admin panel for Projects, Services,
   Gallery, Before/After, Testimonials, Team and Blog — nothing is
   hard-coded, everything flows through the database.

## Default Admin Login (development only)

Created interactively by `install.php` — there is no hard-coded password.
If you skip the installer and load `database/database.sql` directly, the
seeded `admins` row has a placeholder (invalid) hash; you must still run
`install.php` once (or manually run `UPDATE admins SET password_hash =
'<php password_hash() output>'`) before you can log in.

## Security Notes

- All database queries use PDO prepared statements.
- CSRF tokens are required on every state-changing form (public + admin).
- Sessions are HttpOnly, SameSite=Lax, and regenerated on login.
- Login is rate-limited (5 failed attempts → 15 minute lockout per account).
- Uploaded files are validated by real MIME type + `getimagesize()`, renamed
  to random filenames, and `/uploads/.htaccess` disables PHP execution in
  that directory as defense-in-depth against upload-based RCE.
- Admin pages require `admin/includes/auth.php`, which redirects
  unauthenticated requests to the login screen.

## Editable From the Admin Panel (no code changes needed)

Company info, logo/favicon, phone/WhatsApp/email/address, social links,
business hours, hero heading/subheading, trust statistics, cities served,
default SEO title/description, Google Analytics ID, services, projects
(+ multi-image galleries), before/after transformations, testimonials, team
members, blog posts/categories, and every inquiry's status
(NEW → CONTACTED → IN DISCUSSION → CONVERTED → NOT INTERESTED → CLOSED).

## WhatsApp Integration

Every "WhatsApp us" button links to `https://wa.me/<number>?text=<message>`
using the number and default message configured in **Settings** — never
hard-coded in the templates (`includes/functions.php:whatsapp_link()`).

## SEO

Every public page sets a unique `<title>`, meta description, canonical URL,
Open Graph/Twitter tags, and JSON-LD structured data (`LocalBusiness`,
`Service`, `Article`, `BreadcrumbList`) via `includes/seo.php`.

## Analytics

Lightweight first-party event tracking (page views, project views, WhatsApp
clicks, call clicks, inquiry submissions) is logged to the `analytics_events`
table and visualized in **Admin → Analytics**, no external service required.
Optionally add a Google Analytics 4 ID in Settings for full GA4 tracking.

## Known Trade-offs (by design, for a maintainable single-deploy PHP site)

- URLs use query strings for detail pages (`project-detail.php?slug=...`)
  rather than a rewritten `/projects/slug` path, avoiding a routing layer —
  simple to deploy on any PHP host, no `mod_rewrite` dependency for the app
  to function (`.htaccess` only adds hardening + optional gzip/caching).
- Blog/project "content" fields accept trusted admin-authored HTML (no
  public user input reaches these fields) rather than shipping a heavier
  WYSIWYG editor dependency.
