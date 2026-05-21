<?php
/**
 * MoodMap - Reset Password
 * 
 * Set new password after verification.
 */

$pageTitle = 'Reset Password';
require_once '../../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/pages/dashboard/index.php');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    redirectWith(BASE_URL . '/pages/auth/login.php', 'Invalid or expired reset link', 'error');
}

// Verify token
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    redirectWith(BASE_URL . '/pages/auth/login.php', 'Invalid or expired reset link', 'error');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        redirectWith(BASE_URL . '/pages/auth/login.php', 'Password reset successfully! Please login with your new password.', 'success');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - MoodMap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366F1',
                        secondary: '#8B5CF6'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center gap-2">
                <img src="<?php echo ASSETS_URL; ?>/images/logo.svg" alt="MoodMap" class="h-12 w-12">
                <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    MoodMap
                </span>
            </a>
        </div>
        
        <!-- Reset Password Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="text-3xl">🔐</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Set New Password</h1>
                <p class="text-slate-500 mt-2">Create a strong password for your account</p>
            </div>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                        New Password
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="Enter new password"
                           minlength="6"
                           required>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2">
                        Confirm Password
                    </label>
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="Confirm new password"
                           minlength="6"
                           required>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 rounded-xl font-semibold hover:shadow-lg transition-all">
                    Reset Password
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-primary hover:underline text-sm">
                    ← Back to Login
                </a>
            </div>
            
        </div>
        
    </div>
    
</body>
</html>
