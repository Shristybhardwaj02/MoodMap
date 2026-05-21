<?php
/**
 * MoodMap - Landing Page (Animated)
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodMap - Track Your Mood, Discover Your Day</title>
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
    <style>
        * { font-family: 'Nunito', sans-serif; }
        
        /* Animated Gradient Background */
        .hero-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
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
            animation: floatUp 20s infinite ease-in-out;
            opacity: 0.4;
        }
        @keyframes floatUp {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.4; }
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
        }
        
        /* Navbar Glass */
        .nav-glass {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
        }
        
        /* Mood Emoji Animation */
        .mood-emoji-card {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
        }
        .mood-emoji-card:hover {
            transform: scale(1.2) rotate(5deg);
            z-index: 10;
        }
        
        /* Float Animation */
        .float-anim {
            animation: floatBounce 3s ease-in-out infinite;
        }
        @keyframes floatBounce {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Slide Up Animation */
        .slide-up {
            animation: slideUp 0.8s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Glow Effect */
        .glow {
            box-shadow: 0 0 80px rgba(99, 102, 241, 0.4);
        }
        
        /* Feature Card Hover */
        .feature-card {
            transition: all 0.4s ease;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.2);
        }
        
        /* Pulse Dot */
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.5); }
        }
        
        /* Mood Illustration */
        .mood-illustration {
            position: fixed;
            bottom: -30px;
            right: -30px;
            font-size: 200px;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
            animation: floatBounce 4s ease-in-out infinite;
        }
        
        /* Stat Number Animation */
        .stat-bounce {
            animation: statPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes statPop {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden hero-bg">
    
    <!-- Floating Particles -->
    <div class="particles" id="particles"></div>
    
    <!-- Mood Illustration -->
    <div class="mood-illustration">🌈</div>
    
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 nav-glass border-b border-white/30">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="index.php" class="text-2xl font-black bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent flex items-center gap-2 bounce-btn">
                <span>✨</span> MoodMap
            </a>
            <div class="flex items-center gap-4">
                <a href="#features" class="hidden md:block text-slate-600 hover:text-primary font-bold text-sm bounce-btn">Features</a>
                <a href="pages/auth/login.php" class="text-slate-600 hover:text-primary font-bold text-sm bounce-btn">Login</a>
                <a href="pages/auth/signup.php" class="bg-gradient-to-r from-primary to-secondary text-white px-5 py-2.5 rounded-full font-bold text-sm hover:shadow-xl bounce-btn">
                    Get Started 🚀
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="pt-28 pb-16 px-6 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left Content -->
                <div class="space-y-8 slide-up">
                    <div class="inline-flex items-center gap-2 bg-white/20 text-white px-5 py-2.5 rounded-full text-sm font-bold backdrop-blur-sm">
                        <span class="w-2 h-2 bg-green-400 rounded-full pulse-dot"></span>
                        Track • Discover • Feel Better
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight">
                        Understand Your
                        <span class="bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent"> Emotions</span> ✨
                    </h1>
                    
                    <p class="text-lg text-white/80 leading-relaxed max-w-lg font-medium">
                        Log your mood daily and get personalized recommendations for places, activities, and wellness tips that match how you're feeling.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="pages/auth/signup.php" class="bg-white text-primary px-8 py-4 rounded-2xl font-black text-center hover:shadow-2xl bounce-btn flex items-center justify-center gap-2">
                            Start Free <span class="text-xl">→</span>
                        </a>
                        <a href="#how" class="border-2 border-white/50 text-white px-8 py-4 rounded-2xl font-bold text-center hover:bg-white/10 bounce-btn backdrop-blur-sm">
                            See How It Works
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex gap-10 pt-4">
                        <div class="stat-bounce" style="animation-delay: 0.2s;">
                            <div class="text-4xl font-black text-white">5</div>
                            <div class="text-white/70 text-sm font-bold">Moods</div>
                        </div>
                        <div class="stat-bounce" style="animation-delay: 0.4s;">
                            <div class="text-4xl font-black text-white">50+</div>
                            <div class="text-white/70 text-sm font-bold">Tips</div>
                        </div>
                        <div class="stat-bounce" style="animation-delay: 0.6s;">
                            <div class="text-4xl font-black text-white">∞</div>
                            <div class="text-white/70 text-sm font-bold">Places</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Mood Card -->
                <div class="relative slide-up" style="animation-delay: 0.3s;">
                    <div class="absolute inset-0 bg-white/20 rounded-3xl blur-3xl"></div>
                    <div class="relative glass-card rounded-3xl p-8 glow float-anim">
                        <h3 class="text-xl font-black text-slate-800 mb-6 text-center">How are you feeling? 🎭</h3>
                        <div class="grid grid-cols-5 gap-3">
                            <div class="mood-emoji-card text-center p-4 rounded-2xl bg-gradient-to-br from-yellow-100 to-orange-100">
                                <div class="text-4xl mb-2">😊</div>
                                <div class="text-xs font-bold text-slate-700">Happy</div>
                            </div>
                            <div class="mood-emoji-card text-center p-4 rounded-2xl bg-gradient-to-br from-blue-100 to-cyan-100">
                                <div class="text-4xl mb-2">😢</div>
                                <div class="text-xs font-bold text-slate-700">Sad</div>
                            </div>
                            <div class="mood-emoji-card text-center p-4 rounded-2xl bg-gradient-to-br from-purple-100 to-pink-100">
                                <div class="text-4xl mb-2">😰</div>
                                <div class="text-xs font-bold text-slate-700">Anxious</div>
                            </div>
                            <div class="mood-emoji-card text-center p-4 rounded-2xl bg-gradient-to-br from-green-100 to-teal-100">
                                <div class="text-4xl mb-2">😌</div>
                                <div class="text-xs font-bold text-slate-700">Calm</div>
                            </div>
                            <div class="mood-emoji-card text-center p-4 rounded-2xl bg-gradient-to-br from-red-100 to-orange-100">
                                <div class="text-4xl mb-2">⚡</div>
                                <div class="text-xs font-bold text-slate-700">Energetic</div>
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t border-slate-200 text-center">
                            <p class="text-sm text-slate-500 font-medium">Select your mood to get personalized recommendations</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-20 px-6 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-16 slide-up">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Everything You Need 🎯</h2>
                <p class="text-white/70 max-w-xl mx-auto font-medium">Simple tools to track your emotions and discover what helps you feel better</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="feature-card glass-card rounded-3xl p-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mb-6 text-3xl bounce-btn">
                        😊
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-3">Mood Tracking</h3>
                    <p class="text-slate-500 font-medium">Log your mood with one tap. Track patterns and understand yourself better.</p>
                </div>
                
                <div class="feature-card glass-card rounded-3xl p-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-pink-500 rounded-2xl flex items-center justify-center mb-6 text-3xl bounce-btn">
                        📍
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-3">Smart Places</h3>
                    <p class="text-slate-500 font-medium">Discover cafes, parks, and spots that match your current mood.</p>
                </div>
                
                <div class="feature-card glass-card rounded-3xl p-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl flex items-center justify-center mb-6 text-3xl bounce-btn">
                        💡
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-3">Wellness Tips</h3>
                    <p class="text-slate-500 font-medium">Get personalized activities and tips to improve how you feel.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works -->
    <section id="how" class="py-20 px-6 relative z-10">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-4">How It Works 🚀</h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center slide-up" style="animation-delay: 0.1s;">
                    <div class="w-20 h-20 mx-auto bg-white/20 rounded-full flex items-center justify-center text-4xl mb-4 backdrop-blur-sm bounce-btn">1️⃣</div>
                    <h3 class="text-xl font-black text-white mb-2">Log Your Mood</h3>
                    <p class="text-white/70 font-medium">Tap how you're feeling in seconds</p>
                </div>
                <div class="text-center slide-up" style="animation-delay: 0.2s;">
                    <div class="w-20 h-20 mx-auto bg-white/20 rounded-full flex items-center justify-center text-4xl mb-4 backdrop-blur-sm bounce-btn">2️⃣</div>
                    <h3 class="text-xl font-black text-white mb-2">Get Recommendations</h3>
                    <p class="text-white/70 font-medium">Places & tips matched to your mood</p>
                </div>
                <div class="text-center slide-up" style="animation-delay: 0.3s;">
                    <div class="w-20 h-20 mx-auto bg-white/20 rounded-full flex items-center justify-center text-4xl mb-4 backdrop-blur-sm bounce-btn">3️⃣</div>
                    <h3 class="text-xl font-black text-white mb-2">Feel Better</h3>
                    <p class="text-white/70 font-medium">Track progress & discover patterns</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-20 px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <div class="glass-card rounded-3xl p-12 glow">
                <h2 class="text-3xl md:text-4xl font-black text-slate-800 mb-4">Ready to Start? 🎉</h2>
                <p class="text-slate-500 mb-8 font-medium">Begin your emotional wellness journey today. It's free!</p>
                <a href="pages/auth/signup.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-10 py-4 rounded-2xl font-black text-lg hover:shadow-2xl bounce-btn">
                    Create Free Account ✨
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="py-8 px-6 relative z-10 border-t border-white/10">
        <div class="max-w-5xl mx-auto text-center">
            <p class="text-white/50 font-medium">Made with ❤️ for your wellbeing</p>
            <p class="text-white/30 text-sm mt-2">© 2024 MoodMap. College Exhibition Project.</p>
        </div>
    </footer>

<script>
// Create floating particles
function createParticles() {
    const container = document.getElementById('particles');
    const colors = ['#ffffff', '#ffd700', '#ff6b6b', '#4ecdc4', '#a18cd1'];
    
    for (let i = 0; i < 25; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.width = (Math.random() * 20 + 10) + 'px';
        particle.style.height = particle.style.width;
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        particle.style.animationDelay = (Math.random() * 20) + 's';
        particle.style.animationDuration = (20 + Math.random() * 10) + 's';
        container.appendChild(particle);
    }
}

document.addEventListener('DOMContentLoaded', createParticles);
</script>

</body>
</html>
