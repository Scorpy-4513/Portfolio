CREATE TABLE settings (
  id INT PRIMARY KEY,
  site_title VARCHAR(150) NOT NULL,
  name VARCHAR(150) NOT NULL,
  introduction TEXT NOT NULL,
  biography TEXT NOT NULL,
  goals TEXT NOT NULL,
  interests TEXT NOT NULL,
  email VARCHAR(190) NOT NULL,
  github VARCHAR(255) NOT NULL,
  facebook VARCHAR(255) NOT NULL,
  instagram VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) NOT NULL DEFAULT '',
  logo_image VARCHAR(255) NOT NULL DEFAULT '',
  appearance VARCHAR(10) NOT NULL DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE admins (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE login_attempts (attempt_key CHAR(64) PRIMARY KEY, attempts INT NOT NULL, last_attempt DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE skills (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, category VARCHAR(100) NOT NULL, level INT NOT NULL, description TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE projects (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, technologies VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE education (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, organization VARCHAR(200) NOT NULL, period VARCHAR(100) NOT NULL, description TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE experience (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, organization VARCHAR(200) NOT NULL, period VARCHAR(100) NOT NULL, description TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE achievements (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, organization VARCHAR(200) NOT NULL, period VARCHAR(100) NOT NULL, description TEXT NOT NULL, image VARCHAR(255) NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE messages (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, email VARCHAR(190) NOT NULL, subject VARCHAR(200) NOT NULL, message TEXT NOT NULL, is_read TINYINT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, sender_key CHAR(64) NOT NULL, INDEX (sender_key, created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO settings VALUES (1, 'Ajie C. Javier | Portfolio', 'Ajie C. Javier', 'I''m an Information Technology student who builds clean, functional web projects. I care about writing code that''s easy to read and interfaces that get out of the person''s way. This is a running collection of what I''ve been building and learning.', 'I''m a 3rd year Information Technology student at Negros Oriental State University, Bayawan-Santa Catalina Campus. I got into tech because it keeps challenging me to learn something new, and I like exploring how things work. Over the past few years I''ve built several Java-based systems, from inventory tracking to ordering platforms, and I''m now branching out into web development with HTML, CSS, and JavaScript.', 'I want to keep expanding my knowledge and eventually build real things that can help people. I''m interested in growing my skills in both software development and networking.', 'Outside of school, I like to draw and sketch, listen to music, and play FPS games. I also enjoy working with Adobe Animate and Cisco Packet Tracer.', 'ajiecrespo4513@gmail.com', 'https://github.com/Scorpy-4513', 'https://www.facebook.com/share/19ipu87WiY/', 'https://instagram.com/aji.jvr', 'images/Ajieprofile.jpeg', '', 'light');
INSERT INTO skills (name, category, level, description) VALUES
('C++','Programming',85,'Used to practice programming logic, loops, functions, and object-oriented basics.'),('HTML','Web',70,'Builds the structure of my web pages, from headings and forms to page layout.'),('CSS','Web',55,'Styles my pages using Flexbox, Grid, and responsive layouts.'),('JavaScript','Web',20,'Adds interaction like menus, validation, and click events. Still learning.'),('Java','Programming',50,'My main language for the desktop systems I built in NetBeans.'),('PHP','Web',40,'Learning server-side scripting to connect web pages with a database.'),('MySQL','Database',75,'Stores and manages data, like the inventory records in my clinic system.'),('Java Swing / GUI Development','Programming',50,'Builds the windows, buttons, and forms in my Java desktop projects.'),('Networking / Cisco Packet Tracer','Networking',70,'Designs and simulates networks using Cisco Packet Tracer.'),('Adobe Animate','Design',50,'Creates animations, like my 2-minute product motion ad.'),('UI Design','Design',80,'Plans clear, simple layouts that are easy to use.'),('Problem Solving','General',75,'Breaks a problem into small steps and tests until it works.');
INSERT INTO projects (title, description, technologies, image) VALUES
('Business/Product Landing Page - Skeleton Design','A one-page responsive website skeleton created using HTML and CSS. This project focuses on practicing website structure, layout, navigation, product sections, pricing, forms, and responsive design for desktop and mobile screens.','HTML, CSS','images/Project1.png'),
('BAI-GER – Burger Product Advertisement Animation','A 2-minute animated product advertisement created in Adobe Animate. The project showcases the BAI-GER burger through animated text, product presentation, transitions, and promotional content.','Adobe Animate','images/Project2.jpg'),
('Personal Portfolio Website','A responsive personal portfolio website built with modern web technologies. This project showcases my skills, projects, and experience in a clean, user-friendly interface.','HTML, CSS, JavaScript','images/Project3.png'),
('CBR Vet Clinic – Medicine Tracker and Inventory Management System','A proposed management system for CBR Veterinary Clinic that focuses on tracking medicine inventory, stock levels, expiration dates, and appointments.','HTML, CSS, JavaScript, PHP, MySQL','images/Project4.jfif');
INSERT INTO education (title, organization, period, description) VALUES ('Information Technology','Negros Oriental State University (NORSU)','2024 — Present','Bayawan-Santa Catalina Campus'),('Computer System Servicing (CSS)','Tambakan, Sta. Catalina National High School','2022 — 2024','Hardware, networking, and troubleshooting.');
INSERT INTO achievements (title, organization, period, description) VALUES ('Cisco / Networking','', 'Coursework','Networking coursework and Cisco Packet Tracer activities.'),('Adobe Animate','','Coursework','Adobe Animate coursework.');
