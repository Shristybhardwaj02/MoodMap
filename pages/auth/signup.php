<?php
/**
 * MoodMap - User Registration (Signup)
 * 
 * This page handles new user registration with form validation.
 * After successful registration, user is redirected to OTP verification.
 */

require_once '../../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard/index.php');
    exit;
}

$errors = [];
$formData = ['name' => '', 'email' => '', 'phone' => ''];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData['name'] = sanitize($_POST['name'] ?? '');
    $formData['email'] = sanitize($_POST['email'] ?? '');
    $formData['phone'] = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($formData['name']) || strlen($formData['name']) < 2) {
        $errors['name'] = 'Please enter your name (at least 2 characters)';
    }
    
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (!empty($formData['phone']) && !preg_match('/^\d{10}$/', preg_replace('/\D/', '', $formData['phone']))) {
        $errors['phone'] = 'Please enter a valid 10-digit phone number';
    }
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // CSRF verification
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $result = registerUser($formData['name'], $formData['email'], $formData['phone'], $password);
        
        if ($result['success']) {
            // Auto-verified for demo - go directly to login
            if (isset($result['auto_verified']) && $result['auto_verified']) {
                redirectWith(BASE_URL . '/pages/auth/login.php', 'Account created! Please login.', 'success');
            } else {
                $_SESSION['verify_email'] = $formData['email'];
                redirectWith(BASE_URL . '/pages/auth/verify-otp.php', $result['message'], 'success');
            }
        } else {
            $errors['general'] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo APP_NAME; ?></title>
    
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
<body class="min-h-screen flex items-center justify-center p-4 overflow-x-hidden">
    
    <!-- Animated Background -->
    <div class="auth-bg"></div>
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Mood Illustration -->
    <div class="mood-illustration">🚀</div>
    
    <div class="w-full max-w-md relative z-10 my-8">
        <!-- Logo -->
        <div class="text-center mb-6 float-anim">
            <a href="<?php echo BASE_URL; ?>" class="inline-flex items-center gap-2 bounce-btn">
                <span class="text-3xl">✨</span>
                <span class="text-3xl font-black bg-gradient-to-r from-white via-yellow-200 to-white bg-clip-text text-transparent">MoodMap</span>
            </a>
            <p class="text-white/80 mt-2 font-bold">Start your wellness journey! 🎉</p>
        </div>
        
        <!-- Signup Card -->
        <div class="glass-card rounded-3xl shadow-2xl p-8 glow">
            
            <?php if (isset($errors['general'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-bold">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="signup-form" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo htmlspecialchars($formData['name']); ?>"
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['name']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="Enter your full name"
                           required>
                    <?php if (isset($errors['name'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($formData['email']); ?>"
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['email']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="you@example.com"
                           required>
                    <?php if (isset($errors['email'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Phone (Optional) -->
                <div>
                    <label for="phone" class="block text-sm font-bold text-slate-700 mb-2">
                        Phone Number <span class="text-slate-400 font-medium">(optional)</span>
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($formData['phone']); ?>"
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['phone']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="1234567890">
                    <?php if (isset($errors['phone'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['phone']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['password']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="Create a password (min 6 characters)"
                           required>
                    <p id="password-strength" class="text-sm mt-1 font-bold"></p>
                    <?php if (isset($errors['password'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-bold text-slate-700 mb-2">Confirm Password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="auth-input w-full px-5 py-4 rounded-2xl border-2 <?php echo isset($errors['confirm_password']) ? 'border-red-300' : 'border-slate-200'; ?> focus:border-primary focus:ring-0 font-medium"
                           placeholder="Confirm your password"
                           required>
                    <?php if (isset($errors['confirm_password'])): ?>
                    <p class="text-red-500 text-sm mt-1 font-bold"><?php echo $errors['confirm_password']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="bounce-btn w-full bg-gradient-to-r from-primary via-purple-500 to-pink-500 text-white py-4 rounded-2xl font-black text-lg hover:shadow-2xl mt-2">
                    Create Account 🚀
                </button>
            </form>
            
            <!-- Divider -->
            <div class="my-6 flex items-center">
                <div class="flex-1 border-t border-slate-200"></div>
                <span class="px-4 text-sm text-slate-400 font-bold">or</span>
                <div class="flex-1 border-t border-slate-200"></div>
            </div>
            
            <!-- Login Link -->
            <p class="text-center text-slate-600 font-bold">
                Already have an account? 
                <a href="login.php" class="text-primary font-black hover:underline">Login</a>
            </p>
        </div>
        
        <!-- Back to Home -->
        <p class="text-center mt-6">
            <a href="<?php echo BASE_URL; ?>" class="text-white/80 hover:text-white transition-colors font-bold bounce-btn inline-block">
                ← Back to Home
            </a>
        </p>
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
