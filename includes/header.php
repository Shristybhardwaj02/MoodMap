<?php
/**
 * MoodMap - Animated Header with Consistent Navbar
 */

if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$moodStreak = isLoggedIn() ? getMoodStreak(getCurrentUserId()) : null;
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366F1',
                        secondary: '#8B5CF6',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Nunito', sans-serif; }
        
        /* Animated Gradient Background */
        .mood-bg {
            transition: all 1s ease;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .bg-default { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); }
        .bg-happy { background: linear-gradient(135deg, #f6d365 0%, #fda085 50%, #ff6b6b 100%); }
        .bg-sad { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #43e97b 100%); }
        .bg-anxious { background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 50%, #f5576c 100%); }
        .bg-calm { background: linear-gradient(135deg, #11998e 0%, #38ef7d 50%, #a8edea 100%); }
        .bg-energetic { background: linear-gradient(135deg, #fc466b 0%, #3f5efb 50%, #00d9ff 100%); }
        
        /* Floating Particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
            opacity: 0.5;
        }
        @keyframes float {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.5; }
            90% { opacity: 0.5; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }
        
        /* Bouncy Buttons */
        .bounce-btn {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .bounce-btn:hover {
            transform: scale(1.08) rotate(-2deg);
        }
        .bounce-btn:active {
            transform: scale(0.95);
        }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.5);
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Navbar Glass */
        .nav-glass {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
        }
        
        /* Fire Animation */
        .fire-emoji {
            animation: fireWiggle 0.5s infinite alternate;
            display: inline-block;
        }
        @keyframes fireWiggle {
            from { transform: rotate(-5deg) scale(1); }
            to { transform: rotate(5deg) scale(1.1); }
        }
        
        /* Mood Illustration */
        .mood-illustration {
            position: fixed;
            bottom: -20px;
            right: -20px;
            font-size: 180px;
            opacity: 0.08;
            pointer-events: none;
            transition: all 1s ease;
            z-index: 0;
        }
        
        /* Glow Effect */
        .glow {
            box-shadow: 0 0 60px rgba(99, 102, 241, 0.3);
        }
        
        /* Pulse Dot */
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }
        
        /* Nav Link Animation */
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #6366F1, #8B5CF6);
            border-radius: 2px;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .nav-link.active {
            color: #6366F1;
        }
        
        /* Mobile Nav Item */
        .mobile-nav-item {
            transition: all 0.3s ease;
        }
        .mobile-nav-item:active {
            transform: scale(0.9);
        }
        .mobile-nav-item.active {
            color: #6366F1;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden">
    
    <!-- Animated Background -->
    <div id="moodBg" class="mood-bg bg-default"></div>
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Mood Illustration -->
    <div id="moodIllustration" class="mood-illustration">🌈</div>
    
    <?php if ($flashMessage): ?>
    <div id="toast" class="fixed top-20 right-4 z-[100] px-6 py-3 rounded-xl shadow-lg text-white font-bold <?php echo $flashMessage['type'] === 'success' ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-red-400 to-red-600'; ?>" style="animation: slideUp 0.4s ease;">
        <?php echo htmlspecialchars($flashMessage['message']); ?>
    </div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 3000);</script>
    <?php endif; ?>
    
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 nav-glass border-b border-white/30">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="text-2xl font-black bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent flex items-center gap-2 bounce-btn">
                <span>✨</span> MoodMap
            </a>
            
            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-8">
                <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="nav-link font-bold text-sm <?php echo $currentDir === 'dashboard' ? 'active text-primary' : 'text-slate-600'; ?>">
                    🏠 Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/places/nearby.php" class="nav-link font-bold text-sm <?php echo $currentDir === 'places' ? 'active text-primary' : 'text-slate-600'; ?>">
                    📍 Places
                </a>
                <a href="<?php echo BASE_URL; ?>/pages/wellness/tips.php" class="nav-link font-bold text-sm <?php echo $currentDir === 'wellness' ? 'active text-primary' : 'text-slate-600'; ?>">
                    💡 Wellness
                </a>
            </div>
            
            <!-- User Section -->
            <div class="flex items-center gap-3">
                <?php if ($moodStreak && $moodStreak['current_streak'] > 0): ?>
                <div class="bg-gradient-to-r from-orange-400 to-red-500 text-white px-4 py-2 rounded-full font-bold text-sm flex items-center gap-2 bounce-btn">
                    <span class="fire-emoji">🔥</span>
                    <span><?php echo $moodStreak['current_streak']; ?></span>
                </div>
                <?php endif; ?>
                
                <div class="relative group">
                    <button class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary text-white font-black text-sm flex items-center justify-center bounce-btn shadow-lg">
                        <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
                    </button>
                    <div class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform group-hover:translate-y-0 translate-y-2">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="font-bold text-slate-800"><?php echo htmlspecialchars($currentUser['name'] ?? ''); ?></p>
                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/pages/profile/index.php" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 font-medium">
                            <span>👤</span> Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/mood/history.php" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 font-medium">
                            <span>📊</span> Mood History
                        </a>
                        <a href="<?php echo BASE_URL; ?>/pages/auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-red-50 font-medium">
                            <span>🚪</span> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Bottom Nav -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 nav-glass border-t border-white/30 safe-area-pb">
        <div class="grid grid-cols-4 h-16 px-2">
            <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="mobile-nav-item flex flex-col items-center justify-center gap-1 <?php echo $currentDir === 'dashboard' ? 'active' : 'text-slate-400'; ?>">
                <span class="text-xl">🏠</span>
                <span class="text-[10px] font-bold">Home</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/places/nearby.php" class="mobile-nav-item flex flex-col items-center justify-center gap-1 <?php echo $currentDir === 'places' ? 'active' : 'text-slate-400'; ?>">
                <span class="text-xl">📍</span>
                <span class="text-[10px] font-bold">Places</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/wellness/tips.php" class="mobile-nav-item flex flex-col items-center justify-center gap-1 <?php echo $currentDir === 'wellness' ? 'active' : 'text-slate-400'; ?>">
                <span class="text-xl">💡</span>
                <span class="text-[10px] font-bold">Wellness</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/profile/index.php" class="mobile-nav-item flex flex-col items-center justify-center gap-1 <?php echo $currentDir === 'profile' ? 'active' : 'text-slate-400'; ?>">
                <span class="text-xl">👤</span>
                <span class="text-[10px] font-bold">Profile</span>
            </a>
        </div>
    </nav>
    
    <!-- Main Content Wrapper -->
    <main class="pt-20 pb-24 md:pb-8 px-4 relative z-10">

<script>
// Create floating particles
function createParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    
    const colors = ['#667eea', '#764ba2', '#f093fb', '#6366F1', '#8B5CF6'];
    
    for (let i = 0; i < 15; i++) {
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

// Initialize particles on load
document.addEventListener('DOMContentLoaded', createParticles);
</script>
