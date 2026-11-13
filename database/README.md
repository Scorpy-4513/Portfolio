# Database setup

1. Create a MySQL database using your hosting control panel or local MySQL client, for example `portfolio`.
2. Select that database and import `schema.sql` (for example with phpMyAdmin's **Import** tab).
3. The schema creates the portfolio content, administrator, login rate-limit, and contact-message tables. It seeds starter public content from the existing static portfolio. Re-importing uses `INSERT IGNORE` for this starter content and will not overwrite edits.
4. No administrator account or password is seeded. Create the first administrator through the project's protected setup procedure once that is implemented; never insert a plaintext password.
5. Keep database credentials in server environment variables or a local, untracked configuration file. Do not commit credentials or real administrator passwords.

The schema is intended for MySQL 8.0+ with InnoDB and `utf8mb4` support.
