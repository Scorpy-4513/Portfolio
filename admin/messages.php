<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute([':id' => $id]);
        set_flash('success', 'Message deleted.');
        redirect('messages.php');
    }
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll() ?: [];
$flashSuccess = get_flash('success');
$flashError = get_flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f2ee;
            color: #1b1d1f;
        }
        .wrap {
            max-width: 1100px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dfe3d9;
            text-align: left;
            padding: 12px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <h1>Messages</h1>
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
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td><?php echo e((string) ($message['name'] ?? '')); ?></td>
                            <td><?php echo e((string) ($message['email'] ?? '')); ?></td>
                            <td><?php echo nl2br(e((string) ($message['message'] ?? ''))); ?></td>
                            <td><?php echo e((string) ($message['created_at'] ?? '')); ?></td>
                            <td>
                                <form method="post" action="messages.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo e((string) ($message['id'] ?? '')); ?>">
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
