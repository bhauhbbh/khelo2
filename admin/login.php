<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $_ENV['ADMIN_USERNAME'] && 
        password_verify($password, $_ENV['ADMIN_PASSWORD'])) {
        
        // Generate secure session ID
        $session_id = bin2hex(random_bytes(32));
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO admin_sessions (session_id, admin_username, expires_at) 
                             VALUES (:session_id, :username, DATE_ADD(NOW(), INTERVAL 2 HOUR))');
        $stmt->execute([
            'session_id' => $session_id,
            'username' => $username
        ]);
        
        $_SESSION['admin_session'] = $session_id;
        header('Location: dashboard.php');
        exit;
    }
    
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>