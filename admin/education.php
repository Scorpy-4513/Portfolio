<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $school = trim((string) ($_POST['school'] ?? ''));
    $degree = trim((string) ($_POST['degree'] ?? ''));
    $period = trim((string) ($_POST['period'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);

    if ($school === '' || $degree === '') {
        set_flash('error', 'School and degree are required.');
        redirect('education.php');
    }

    if (($action === 'delete') && !empty($_POST['id'])) {
        $stmt = $pdo->prepare('DELETE FROM education WHERE id = :id');
        $stmt->execute([':id' => (int) $_POST['id']]);
        set_flash('success', 'Education entry deleted.');
        redirect('education.php');
    }

    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare('UPDATE education SET school = :school, degree = :degree, period = :period, description = :description, sort_order = :sort_order WHERE id = :id');
        $stmt->execute([
            ':id' => (int) $_POST['id'],
            ':school' => $school,
            ':degree' => $degree,
            ':period' => $period,
            ':description' => $description,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Education entry updated.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO education (school, degree, period, description, sort_order) VALUES (:school, :degree, :period, :description, :sort_order)');
        $stmt->execute([
            ':school' => $school,
            ':degree' => $degree,
            ':period' => $period,
            ':description' => $description,
            ':sort_order' => $sortOrder,
        ]);
        set_flash('success', 'Education entry added.');
    }

    redirect('education.php');
}

$education = $pdo->query('SELECT * FROM education ORDER BY sort_order ASC, id ASC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education | Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f2ee;
            color: #1b1d1f;
        }
        .wrap {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
        .panel {
            background: #fff;
            border: 1px solid #dfe3d9;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(21,26,30,0.08);
        }
        h1 {
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #2d5f4c;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn.secondary {
            background: #fff;
            color: #1b1d1f;
            border: 1px solid #dfe3d9;
        }
        .btn.danger {
            background: #a1251b;
            color: #fff;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .alert.success {
            background: #ecfdf5;
            border: 1px solid #9fe7c4;
            color: #146c43;
        }
        .alert.error {
            background: #fef3f2;
            border: 1px solid #f5c5c2;
            color: #b42318;
        }
        form {
            display: grid;
            gap: 16px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        input, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dfe3d9;
            border-radius: 8px;
            font-size: 16px;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        th, td {
            border: 1px solid #dfe3d9;
            text-align: left;
            padding: 12px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>Education</h1>
            <div>
                <a href="dashboard.php" class="btn secondary">Back to Dashboard</a>
            </div>
        </div>

        <?php if ($flashSuccess): ?>
            <div class="alert success"><?php echo e($flashSuccess); ?></div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert error"><?php echo e($flashError); ?></div>
        <?php endif; ?>

        <div class="panel">
            <form method="post" action="education.php">
                <div class="grid">
                    <div>
                        <label for="school">School</label>
                        <input id="school" name="school" type="text" required>
                    </div>
                    <div>
                        <label for="degree">Degree</label>
                        <input id="degree" name="degree" type="text" required>
                    </div>
                    <div>
                        <label for="period">Period</label>
                        <input id="period" name="period" type="text">
                    </div>
                    <div>
                        <label for="sort_order">Order</label>
                        <input id="sort_order" name="sort_order" type="number" value="0">
                    </div>
                </div>

                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <button type="submit" class="btn">Add Education</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Degree</th>
                        <th>Period</th>
                        <th>Order</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($education as $item): ?>
                        <tr>
                            <td><?php echo e((string) ($item['school'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['degree'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['period'] ?? '')); ?></td>
                            <td><?php echo e((string) ($item['sort_order'] ?? 0)); ?></td>
                            <td>
                                <form method="post" action="education.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($item['id'] ?? '')); ?>">
                                    <button type="submit" class="btn danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
