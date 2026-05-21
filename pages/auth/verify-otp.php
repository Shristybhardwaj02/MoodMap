<?php
/**
 * MoodMap - OTP Verification
 * 
 * This page handles email verification via OTP.
 */

require_once '../../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard/index.php');
    exit;
}

// Check if email is set for verification
if (!isset($_SESSION['verify_email'])) {
    redirectWith(BASE_URL . '/pages/auth/login.php', 'Please login or signup first.', 'warning');
}

$email = $_SESSION['verify_email'];
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = sanitize($_POST['otp'] ?? '');
    
    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        $error = 'Please enter a valid 6-digit OTP';
    } else {
        $result = verifyOTP($email, $otp);
        
        if ($result['success']) {
            unset($_SESSION['verify_email']);
            redirectWith(BASE_URL . '/pages/dashboard/index.php', $result['message'], 'success');
        } else {
            $error = $result['message'];
        }
    }
}

// Handle resend OTP
if (isset($_GET['resend'])) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $otp = generateOTP();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        
        $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = ? WHERE id = ?");
        $stmt->execute([$otp, $otpExpires, $user['id']]);
        
        sendOTPEmail($email, $user['name'], $otp);
        
        $flashMessage = ['message' => 'New OTP sent to your email!', 'type' => 'success'];
    }
}

$flashMessage = $flashMessage ?? getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo APP_NAME; ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366F1',
                        secondary: '#8B5CF6',
                        accent: '#10B981',
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body class="font-poppins bg-gradient-to-br from-indigo-50 via-purple-50 to-white min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center space-x-2">
                <img src="<?php echo ASSETS_URL; ?>/images/logo.svg" alt="MoodMap" class="h-12">
                <span class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">MoodMap</span>
            </a>
        </div>
        
        <!-- Verification Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 text-center">
            
            <!-- Icon -->
            <div class="w-20 h-20 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Verify Your Email</h1>
            <p class="text-slate-500 mb-6">
                We've sent a 6-digit code to<br>
                <span class="font-medium text-slate-700"><?php echo htmlspecialchars($email); ?></span>
            </p>
            
            <?php if (isset($flashMessage) && $flashMessage): ?>
            <div class="mb-6 p-4 <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-600' : 'bg-red-50 border-red-200 text-red-600'; ?> border rounded-xl text-sm">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <!-- OTP Input -->
                <div>
                    <input type="text" 
                           name="otp" 
                           maxlength="6"
                           class="w-full text-center text-3xl tracking-[1rem] font-bold px-4 py-4 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="••••••"
                           pattern="\d{6}"
                           required
                           autofocus>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all duration-300">
                    Verify Email
                </button>
            </form>
            
            <!-- Resend OTP -->
            <div class="mt-6">
                <p class="text-slate-500 text-sm">
                    Didn't receive the code? 
                    <a href="?resend=1" class="text-primary font-semibold hover:underline">Resend OTP</a>
                </p>
                <p class="text-slate-400 text-xs mt-2">
                    OTP expires in <?php echo OTP_EXPIRY_MINUTES; ?> minutes
                </p>
            </div>
        </div>
        
        <!-- Back Link -->
        <p class="text-center mt-6">
            <a href="login.php" class="text-slate-500 hover:text-primary transition-colors">
                ← Back to Login
            </a>
        </p>
    </div>
    
    <script>
        // Auto-focus and format OTP input
        const otpInput = document.querySelector('input[name="otp"]');
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    </script>
</body>
</html>
