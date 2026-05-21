<?php
/**
 * MoodMap - About Page
 */

$pageTitle = 'About';
$isPublicPage = true;

// Check if user is logged in for navigation
$isLoggedIn = isset($_SESSION['user_id']);
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="flex items-center gap-2">
                    <img src="assets/images/logo.svg" alt="MoodMap" class="h-10 w-10">
                    <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        MoodMap
                    </span>
                </a>
                
                <div class="flex items-center gap-4">
                    <?php if ($isLoggedIn): ?>
                    <a href="pages/dashboard/index.php" class="bg-primary text-white px-4 py-2 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                        Dashboard
                    </a>
                    <?php else: ?>
                    <a href="pages/auth/login.php" class="text-slate-600 hover:text-primary transition-colors">Login</a>
                    <a href="pages/auth/signup.php" class="bg-primary text-white px-4 py-2 rounded-xl font-medium hover:bg-primary/90 transition-colors">
                        Get Started
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-4 max-w-4xl">
            
            <!-- Hero -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4">About MoodMap</h1>
                <p class="text-xl text-slate-600">Your emotional wellness companion</p>
            </div>
            
            <!-- Mission -->
            <section class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Our Mission</h2>
                <p class="text-slate-600 leading-relaxed">
                    MoodMap was created with a simple belief: understanding your emotions is the first step to improving your mental well-being. 
                    Our mission is to help you track, understand, and improve your emotional health through simple daily mood logging, 
                    personalized recommendations, and actionable insights.
                </p>
            </section>
            
            <!-- What We Offer -->
            <section class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">What We Offer</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-1">Mood Tracking</h3>
                            <p class="text-slate-600 text-sm">Log your daily emotions with our beautiful mood picker and track patterns over time.</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">📍</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-1">Location-Based Recommendations</h3>
                            <p class="text-slate-600 text-sm">Discover nearby places that match your current mood using Google Maps integration.</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">✨</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-1">Wellness Activities</h3>
                            <p class="text-slate-600 text-sm">Get personalized activity suggestions and breathing exercises based on how you feel.</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">💡</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800 mb-1">Daily Insights</h3>
                            <p class="text-slate-600 text-sm">Receive inspirational quotes, food suggestions, and tips tailored to your mood.</p>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Privacy -->
            <section class="bg-white rounded-3xl p-8 shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Your Privacy Matters</h2>
                <p class="text-slate-600 leading-relaxed mb-4">
                    We take your privacy seriously. Your mood data belongs to you and you alone. We never sell your personal information 
                    to third parties. All data is securely stored and encrypted. You can export or delete your data at any time.
                </p>
                <ul class="list-disc list-inside text-slate-600 space-y-2">
                    <li>Your data is never sold to third parties</li>
                    <li>Secure encryption for all sensitive information</li>
                    <li>Export your data anytime in JSON format</li>
                    <li>Delete your account and all data permanently if you wish</li>
                </ul>
            </section>
            
            <!-- Tech Stack -->
            <section class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-3xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Built With ❤️</h2>
                <p class="text-slate-600 mb-4">
                    MoodMap is a college project built with modern web technologies:
                </p>
                <div class="flex flex-wrap gap-3">
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">PHP 7.4+</span>
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">MySQL</span>
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">Tailwind CSS</span>
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">Chart.js</span>
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">Google Places API</span>
                    <span class="bg-white px-4 py-2 rounded-full text-sm font-medium text-slate-700">XAMPP</span>
                </div>
            </section>
            
            <!-- Contact -->
            <section class="bg-white rounded-3xl p-8 shadow-lg text-center">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Get In Touch</h2>
                <p class="text-slate-600 mb-6">
                    Have questions, suggestions, or feedback? We'd love to hear from you!
                </p>
                <a href="mailto:support@moodmap.com" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Contact Us
                </a>
            </section>
            
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-slate-500">
                © <?php echo date('Y'); ?> MoodMap. Made with 💜 for College Exhibition.
            </p>
        </div>
    </footer>
    
</body>
</html>
