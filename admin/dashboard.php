<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_session'])) {
    header('Location: login.php');
    exit;
}

// Verify session is valid
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM admin_sessions 
                      WHERE session_id = :session_id 
                      AND expires_at > NOW()');
$stmt->execute(['session_id' => $_SESSION['admin_session']]);

if (!$stmt->fetch()) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch subscribers
$stmt = $db->query('SELECT * FROM subscriptions ORDER BY subscribed_at DESC');
$subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <span class="navbar-brand mb-0 h1">Admin Dashboard</span>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Subscribers</h4>
                <a href="export.php" class="btn btn-success">Export CSV</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Subscribed Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscriber['id']); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['subscribed_at']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $subscriber['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($subscriber['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="updateStatus(<?php echo $subscriber['id']; ?>, '<?php echo $subscriber['status'] === 'active' ? 'unsubscribed' : 'active'; ?>')">
                                        <?php echo $subscriber['status'] === 'active' ? 'Unsubscribe' : 'Activate'; ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(id, status) {
        if (confirm('Are you sure you want to update this subscriber\'s status?')) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id, status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            });
        }
    }
    </script>
</body>
</html>