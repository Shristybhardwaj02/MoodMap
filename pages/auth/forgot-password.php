<?php
/**
 * MoodMap - Forgot Password
 */

require_once '../../includes/config.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard/index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $result = requestPasswordReset($email);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#6366F1', secondary: '#8B5CF6' } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body class="font-poppins bg-gradient-to-br from-indigo-50 via-purple-50 to-white min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center space-x-2">
                <img src="<?php echo ASSETS_URL; ?>/images/logo.svg" alt="MoodMap" class="h-12">
                <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">MoodMap</span>
            </a>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl p-8">
            
            <?php if ($success): ?>
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 mb-2">Check Your Email</h1>
                <p class="text-slate-500 mb-6">If your email is registered, you'll receive a password reset link shortly.</p>
                <a href="login.php" class="text-primary font-semibold hover:underline">Back to Login</a>
            </div>
            <?php else: ?>
            
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Forgot Password?</h1>
                <p class="text-slate-500 mt-2">No worries, we'll send you reset instructions.</p>
            </div>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" 
                           class="form-input w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0"
                           placeholder="you@example.com" required>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                    Send Reset Link
                </button>
            </form>
            
            <?php endif; ?>
        </div>
        
        <p class="text-center mt-6">
            <a href="login.php" class="text-slate-500 hover:text-primary transition-colors">← Back to Login</a>
        </p>
    </div>
</body>
</html>
