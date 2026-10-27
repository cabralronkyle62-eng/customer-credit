<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Redirect if already logged in
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? null);

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $password === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = 'That username is already taken. Please choose another.';
        } else {
            // Hash password and insert new user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $insertStmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
            $insertStmt->execute([$username, $passwordHash]);

            setFlash('success', 'Account created successfully! You can now log in.');
            header('Location: login.php');
            exit;
        }
    }
}

$pageTitle = 'Create Account';
require __DIR__ . '/partials/header.php';
?>

<section class="form-page">
    <div class="form-heading">
        <p class="eyebrow">Registration</p>
        <h1>Create an account</h1>
        <p>Register a new administrator account to access the system.</p>
    </div>

    <div class="card form-card">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="status">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="customer-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-actions" style="display: flex; gap: 10px; align-items: center;">
                <button type="submit" class="button button-primary">Create Account</button>
                <a href="login.php" class="button button-secondary">Back to Login</a>
            </div>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>