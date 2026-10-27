<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Redirect if already logged in
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Assuming $db connection is available via bootstrap or a model
        // Example using PDO:
        global $pdo; // Or your database connection instance
        
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            
            setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/partials/header.php';
?>

<section class="form-page">
    <div class="form-heading">
        <p class="eyebrow">Authentication</p>
        <h1>System Login</h1>
        <p>Enter your credentials to access the customer management dashboard.</p>
    </div>

    <div class="card form-card">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="status">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="customer-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">Log In</button>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

<p style="margin-top: 20px; text-align: center; font-size: 14px;">
    Don't have an account? <a href="register.php">Create one</a>
</p>