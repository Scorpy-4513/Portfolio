-- MySQL schema for Ajie C. Javier's dynamic portfolio.
-- Select the target database before importing this file.
-- No administrator credentials or password hashes are included.

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    identifier_hash CHAR(64) NOT NULL,
    attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
    first_attempt_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (identifier_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS profile (
    id TINYINT UNSIGNED NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    headline VARCHAR(180) NOT NULL DEFAULT '',
    introduction TEXT NOT NULL,
    biography TEXT NOT NULL,
    email VARCHAR(254) NOT NULL DEFAULT '',
    phone VARCHAR(40) NOT NULL DEFAULT '',
    location VARCHAR(160) NOT NULL DEFAULT '',
    profile_image VARCHAR(255) NOT NULL DEFAULT 'images/Ajieprofile.jpeg',
    github_url VARCHAR(255) NOT NULL DEFAULT '',
    facebook_url VARCHAR(255) NOT NULL DEFAULT '',
    instagram_url VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT chk_profile_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    proficiency TINYINT UNSIGNED NOT NULL DEFAULT 50,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_skills_sort (sort_order, id),
    CONSTRAINT chk_skill_proficiency CHECK (proficiency <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    project_url VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_slug (slug),
    KEY idx_projects_public_sort (is_published, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_technologies (
    project_id BIGINT UNSIGNED NOT NULL,
    technology VARCHAR(80) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (project_id, technology),
    CONSTRAINT fk_project_technologies_project
        FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS education (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    institution VARCHAR(180) NOT NULL,
    qualification VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_education_sort (sort_order, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS experiences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization VARCHAR(180) NOT NULL,
    position VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_experiences_sort (sort_order, start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS achievements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(180) NOT NULL,
    issuer VARCHAR(180) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    achieved_on DATE NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_achievements_sort (sort_order, achieved_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_name VARCHAR(120) NOT NULL,
    sender_email VARCHAR(254) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_read_date (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Idempotent starter content based on the existing midterm portfolio.
INSERT IGNORE INTO profile
    (id, full_name, headline, introduction, biography, email, profile_image, github_url, facebook_url, instagram_url)
VALUES
    (1, 'Ajie C. Javier', 'Information Technology Student',
     'I am an Information Technology student who builds clean, functional web projects. I care about readable code and interfaces that get out of the way.',
     'This is a running collection of what I have been building and learning as an IT student.',
     'ajiecrespo4513@gmail.com', 'images/Ajieprofile.jpeg',
     'https://github.com/Scorpy-4513', 'https://www.facebook.com/share/19ipu87WiY/', 'https://instagram.com/aji.jvr');

INSERT IGNORE INTO skills (id, name, category, proficiency, description, sort_order) VALUES
    (1, 'HTML', 'Web Development', 65, 'Building semantic page structures.', 1),
    (2, 'CSS', 'Web Development', 60, 'Styling responsive layouts and interfaces.', 2),
    (3, 'JavaScript', 'Web Development', 45, 'Adding interactive behavior to websites.', 3),
    (4, 'PHP', 'Backend Development', 35, 'Developing server-rendered web applications.', 4),
    (5, 'MySQL', 'Database', 35, 'Working with relational data and SQL.', 5);

INSERT IGNORE INTO projects (id, title, slug, description, image_path, sort_order) VALUES
    (1, 'Business/Product Landing Page - Skeleton Design', 'business-product-landing-page', 'A one-page responsive website skeleton created using HTML and CSS, focused on page structure, layout, navigation, product sections, pricing, forms, and responsive design.', 'images/Project1.png', 1),
    (2, 'BAI-GER – Burger Product Advertisement Animation', 'bai-ger-burger-advertisement', 'A two-minute animated product advertisement created in Adobe Animate, showcasing a burger through animated text, product presentation, transitions, and promotional content.', 'images/Project2.jpg', 2),
    (3, 'Personal Portfolio Website', 'personal-portfolio-website', 'A responsive personal portfolio website built with HTML, CSS, and JavaScript to showcase skills, projects, and experience.', 'images/Project3.png', 3),
    (4, 'CBR Vet Clinic – Medicine Tracker and Inventory Management System', 'cbr-vet-clinic-medicine-tracker', 'A proposed management system for tracking veterinary medicine inventory, stock levels, expiration dates, and appointments.', 'images/Project4.jfif', 4);

INSERT IGNORE INTO project_technologies (project_id, technology, sort_order) VALUES
    (1, 'HTML', 1), (1, 'CSS', 2),
    (2, 'Adobe Animate', 1),
    (3, 'HTML', 1), (3, 'CSS', 2), (3, 'JavaScript', 3),
    (4, 'HTML', 1), (4, 'CSS', 2), (4, 'JavaScript', 3), (4, 'PHP', 4), (4, 'MySQL', 5);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('site_title', 'Ajie C. Javier | Portfolio'),
    ('site_description', 'Personal portfolio of Ajie C. Javier, Information Technology student.'),
    ('theme_color', '#2D5F4C');
