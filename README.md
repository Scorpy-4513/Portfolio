<!-- coderabbit-ai-generated:v1 -->

# Ajie C. Javier | Dynamic Portfolio

A PHP and MySQL continuation of the midterm portfolio. The original pages, content, colors, navigation, and light/dark control remain, with a dashboard to manage the site.

## Features

- Editable personal information, contact links, title, logo, profile image and default appearance.
- Add, edit and delete skills, projects, education, experience and achievements. Projects and achievements can have images.
- Contact messages saved in MySQL, with read/unread and delete controls.
- Password hashes, session renewal and inactivity timeout, login rate limiting, prepared statements, escaped output, CSRF tokens, contact throttling and a honeypot.
- Images are checked by content and dimensions, re-encoded as JPEG and stored outside the web root.

## Requirements

<!-- coderabbit-ai-generated:v1 -->

<!-- coderabbit-ai-generated-section:v1:f3a41d6da403518097d3e75dccccbcbd4c0e811232551a8e6a2eb4fab8192605 -->

PHP 8.1 or newer with PDO MySQL, GD (with JPEG, PNG and WebP support), fileinfo and mbstring; MySQL 8 or MariaDB; a writable PHP session directory; and a PHP-capable web server.

<!-- /coderabbit-ai-generated:v1 --> Apache requires mod_rewrite and AllowOverride enabled for the included `.htaccess` rules. Use HTTPS for the whole site. A persistent database and a persistent upload directory are required across redeployments.

GitHub Pages serves static files and cannot run PHP or MySQL. The old Pages deployment workflow has been removed to avoid publishing an outdated static copy. Deploy to a PHP host and point an existing custom domain to it. A `github.io` address cannot be pointed to another PHP host; ask your hosting provider or instructor about an alternative if the midterm used that address. Keep the same address only when your hosting and domain allow it.

## Database setup

Create an empty database with utf8mb4 and a dedicated database user with only the permissions needed for this database. Import the sample data once:

```sh
mysql -u YOUR_USER -p YOUR_DATABASE < database/portfolio.sql
```

Do not re-import this file over a live database: it creates tables and sample records. Back up the database and upload directory regularly. The SQL file contains no administrator account or password.

## Configuration and administrator setup

Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `UPLOAD_DIR` in the PHP process environment using your host's private configuration. `.env.example` documents the names; the application reads environment variables directly and does not load an `.env` file. Never commit credentials. Create `UPLOAD_DIR` outside the document root, make it writable by PHP, and keep it persistent. Do not use a publicly executable upload directory. Restrict access to deployment configuration, backups, and server logs.

Run the following on the server in a private terminal with the same database environment. The script prompts for a username and a password of 12 to 72 bytes; it stores only the password hash. Use a unique strong password and provide it privately to the instructor.

```sh
php scripts/create-admin.php
```

Open `/admin/login.php` over HTTPS. The dashboard includes password change and logout. Failed sign-ins are limited by both account name and client IP; after repeated attempts, wait 15 minutes without retrying. The web server must pass HTTPS status to PHP correctly. Configure TLS at the server or a trusted proxy; do not enable `APP_ALLOW_HTTP` in production.

The Apache rules route the original `.html` page URLs to the dynamic pages and deny web access to internal directories. On Nginx or another server, configure equivalent rules before going live: route `/index.html`, `/about.html`, `/skills.html`, `/projects.html`, `/resume.html`, and `/contact.html` to their corresponding `.php` files; deny public access to `includes`, `database`, `scripts`, hidden files and documentation; disable directory listings; and run PHP through a properly configured PHP handler. Verify these rules on the actual host.

## Local development

After installing and starting MySQL, import the database, set the environment variables above, and create a writable directory outside the checkout for uploads. For local testing only, set `APP_ALLOW_HTTP=1` and run:

```sh
php -S 127.0.0.1:8000 router.php
```

Open `http://127.0.0.1:8000/` and `/admin/login.php`. The built-in server is for development only. The `router.php` file supports the original `.html` URLs locally. Never use this server or `APP_ALLOW_HTTP` on a public deployment.

## Using the dashboard

Edit personal information and settings from the sidebar. Select a content section to add, edit, or delete a record; changes appear on the public website immediately. Choose JPEG, PNG, or WebP images up to 2 MB. The server converts uploaded images to JPEG. Messages can be marked read or unread or deleted. The appearance setting chooses the default; a visitor's saved light/dark preference takes precedence.

## Submission and demonstration

After deployment, test login, each content section, image upload, contact submission and message management on desktop and mobile. Capture screenshots of the public pages, dashboard, CRUD forms, database and responsive layout. Record and upload your own demonstration to Facebook and YouTube, submit both links and the live URL in the LMS, invite the instructor through GitHub, and share login credentials privately. Do not publish credentials in the repository.
