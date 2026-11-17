# Ajie C. Javier Portfolio

A PHP and MySQL portfolio website with public pages and an admin dashboard for managing profile information, skills, projects, education, experience, and site settings.

## Features

- Database-backed public portfolio pages
- Admin dashboard with content management for profile, skills, projects, education, and experience
- Site title, description, and theme color settings
- Contact form and admin message management
- Password-hashed admin accounts, login rate limiting, sessions, and CSRF protection

## Requirements

- PHP 8.1 or later with PDO MySQL enabled
- MySQL 8.0 or later
- PHP's built-in development server for local testing, or Apache/Nginx for deployment

## Local setup

If you already have this repository locally, use that checkout; you do not need to clone it again. Make sure you are on the `fix/admin-schema-align` branch and pull its latest changes. Otherwise, clone the repository once:

```bash
git clone https://github.com/Scorpy-4513/Portfolio.git
cd Portfolio
git switch fix/admin-schema-align
```

Create a MySQL database and import the schema:

```bash
mysql -u root -p -e "CREATE DATABASE portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p portfolio < database/schema.sql
```

Copy `.env.example` to `.env` and set your local database credentials:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=portfolio
DB_USER=root
DB_PASS=your-local-database-password
```

Do not commit `.env` or put real credentials in the repository.

Start the local PHP server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open <http://127.0.0.1:8000/> for the public site.

## Create the first admin account

The schema does not seed an administrator account. After configuring the database and starting the app, visit <http://127.0.0.1:8000/admin/setup.php> and use the one-time setup form to create the first admin account. The setup page becomes unavailable once an admin exists. Then sign in at <http://127.0.0.1:8000/admin/login.php>.

## Admin pages

- `admin/setup.php` — one-time first-admin setup
- `admin/login.php` and `admin/logout.php`
- `admin/dashboard.php`
- `admin/profile.php`
- `admin/skills.php`
- `admin/projects.php`
- `admin/education.php`
- `admin/experience.php`
- `admin/settings.php`
- `admin/messages.php`

## Public pages

- `index.php`
- `about.php`
- `skills.php`
- `projects.php`
- `resume.php`
- `contact.php`

## Database

The schema and database-specific notes are in `database/schema.sql` and `database/README.md`. The schema includes starter public content and intentionally does not create an admin account.

## Security and deployment

- Keep `.env` and admin passwords private; `.env` is ignored by Git.
- Use HTTPS in production and store credentials in the host's environment or an untracked configuration file.
- Keep PHP and MySQL updated, and restrict admin access to trusted users.
- The built-in PHP server is for local development only; use a properly configured production web server when deploying.

## License

For academic and personal portfolio use.
