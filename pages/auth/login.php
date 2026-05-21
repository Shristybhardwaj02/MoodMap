<?php
/**
 * MoodMap - User Login
 * 
 * This page handles user authentication.
 */

require_once '../../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard/index.php');
    exit;
}

$errors = [];
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Please enter your password';
    }
    
    // CSRF verification
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Check for redirect URL
            $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/pages/dashboard/index.php';
            unset($_SESSION['redirect_after_login']);
            
            redirectWith($redirect, 'Welcome back, ' . $_SESSION['user_name'] . '!', 'success');
        } else {
            // Check if needs verification
            if (isset($result['needs_verification']) && $result['needs_verification']) {
                $_SESSION['verify_email'] = $result['email'];
                redirectWith(BASE_URL . '/pages/auth/verify-otp.php', $result['message'], 'warning');
            }
            
            $errors['general'] = $result['message'];
        }
    }
}

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    
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
                    },
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Nunito', sans-serif; }
        
        /* Animated Background */
        .auth-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
            z-index: -2;
        }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating Particles */
        .particles { position: fixed; inset: 0; overflow: hidden; z-index: -1; }
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: floatUp linear infinite;
            opacity: 0.4;
        }
        @keyframes floatUp {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.4; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Glow Effect */
        .glow {
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.3);
        }
        
        /* Bounce Button */
        .bounce-btn {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .bounce-btn:hover {
            transform: translateY(-3px) scale(1.02);
        }
        .bounce-btn:active {
            transform: scale(0.98);
        }
        
        /* Input Focus Animation */
        .auth-input {
            transition: all 0.3s ease;
        }
        .auth-input:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        
        /* Float Animation */
        .float-anim {
            animation: floatBounce 4s ease-in-out infinite;
        }
        @keyframes floatBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        /* Mood Illustration */
        .mood-illustration {
            position: fixed;
            bottom: -30px;
            right: -30px;
            font-size: 180px;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
            animation: floatBounce 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden">
    
    <!-- Animated Background -->
    <div class="auth-bg"></div>
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Mood Illustration -->
    <div class="mood-illustration">🌈</div>
    
    <div class="w-full max-w-md relative z-10">
        <!-- Logo -->
        <div class="text-center mb-8 float-anim">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center gap-2 bounce-btn">
                <span class="text-3xl">✨</span>
                <span class="text-3xl font-black bg-gradient-to-r from-white via-yellow-200 to-white bg-clip-text text-transparent">MoodMap</span>
            </a>
            <p class="text-white/80 mt-2 font-bold">Welcome back! 👋</p>
        </div>
        
        <!-- Login Card -->
        <div class="glass-card rounded-3xl shadow-2xl p-8 glow">
            
            <?php if ($flashMessage): ?>
            <div class="mb-6 p-4 <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-600' : ($flashMessage['type'] === 'warning' ? 'bg-yellow-50 border-yellow-200 text-yellow-600' : 'bg-red-50 border-red-200 text-red-600'); ?> border rounded-xl text-sm font-bold">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-bold">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="login-form" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($email); ?>"
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['email']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="you@example.com"
                           required>
                    <?php if (isset($errors['email'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-bold text-slate-700">Password</label>
                        <a href="forgot-password.php" class="text-sm text-primary hover:underline font-bold">Forgot?</a>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['password']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="Enter your password"
                           required>
                    <?php if (isset($errors['password'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="w-5 h-5 text-primary border-slate-300 rounded-lg focus:ring-primary">
                    <label for="remember" class="ml-2 text-sm text-slate-600 font-bold">Remember me</label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="bounce-btn w-full bg-gradient-to-r from-primary via-purple-500 to-pink-500 text-white py-4 rounded-2xl font-black text-lg hover:shadow-2xl">
                    Login ✨
                </button>
            </form>
            
            <!-- Divider -->
            <div class="my-6 flex items-center">
                <div class="flex-1 border-t border-slate-200"></div>
                <span class="px-4 text-sm text-slate-400 font-bold">or</span>
                <div class="flex-1 border-t border-slate-200"></div>
            </div>
            
            <!-- Signup Link -->
            <p class="text-center text-slate-600 font-bold">
                Don't have an account? 
                <a href="signup.php" class="text-primary font-black hover:underline">Sign Up</a>
            </p>
        </div>
        
        <!-- Back to Home -->
        <p class="text-center mt-6">
            <a href="<?php echo BASE_URL; ?>" class="text-white/80 hover:text-white transition-colors font-bold bounce-btn inline-block">
                ← Back to Home
            </a>
        </p>
        
        <!-- Demo Credentials -->
        <div class="mt-6 glass-card rounded-2xl p-4 text-center text-sm">
            <p class="text-slate-500 font-bold">🔑 Demo Account:</p>
            <p class="text-slate-700 font-mono font-bold">test@moodmap.com / test123</p>
        </div>
    </div>
    
    <script>
    // Create floating particles
    function createParticles() {
        const container = document.getElementById('particles');
        const colors = ['#ffffff', '#ffd700', '#ff6b6b', '#4ecdc4', '#a18cd1'];
        
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.width = (Math.random() * 15 + 8) + 'px';
            particle.style.height = particle.style.width;
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            particle.style.animationDelay = (Math.random() * 15) + 's';
            particle.style.animationDuration = (15 + Math.random() * 10) + 's';
            container.appendChild(particle);
        }
    }
    document.addEventListener('DOMContentLoaded', createParticles);
    </script>
</body>
</html>
