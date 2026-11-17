<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function fetch_profile(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM profile WHERE id = 1 LIMIT 1');
    return $stmt->fetch() ?: [];
}

function fetch_skills(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM skills ORDER BY sort_order ASC, id ASC');
    return $stmt->fetchAll() ?: [];
}

function fetch_projects(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, id ASC');
    $projects = $stmt->fetchAll() ?: [];

    foreach ($projects as &$project) {
        $techStmt = $pdo->prepare('SELECT technology FROM project_technologies WHERE project_id = :project_id ORDER BY sort_order ASC, technology ASC');
        $techStmt->execute([':project_id' => $project['id']]);
        $project['technologies'] = array_column($techStmt->fetchAll() ?: [], 'technology');
    }

    return $projects;
}

function fetch_education(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, end_date DESC, id DESC');
    return $stmt->fetchAll() ?: [];
}

function fetch_experiences(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM experiences ORDER BY sort_order ASC, start_date DESC, id DESC');
    return $stmt->fetchAll() ?: [];
}

function fetch_settings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $rows = $stmt->fetchAll() ?: [];
    $settings = [];

    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}
