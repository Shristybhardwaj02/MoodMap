<?php
/**
 * MoodMap - Profile Page
 * 
 * View and manage user profile.
 */

$pageTitle = 'My Profile';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$streak = getMoodStreak($userId);

include '../../includes/header.php';
?>

<style>
    /* Profile-specific animations */
    .profile-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .profile-card:hover {
        transform: translateY(-4px);
    }
    
    .stat-card {
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .stat-card:hover {
        transform: translateY(-6px) scale(1.03);
    }
    
    .avatar-ring {
        animation: avatarPulse 3s ease-in-out infinite;
    }
    @keyframes avatarPulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2); }
        50% { box-shadow: 0 0 0 8px rgba(99, 102, 241, 0.1); }
    }
    
    .menu-item {
        transition: all 0.25s ease;
    }
    .menu-item:hover {
        transform: translateX(6px);
        background: rgba(99, 102, 241, 0.08);
    }
    .menu-item:active {
        transform: scale(0.98);
    }
    
    .stat-value {
        animation: statPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    @keyframes statPop {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .mood-bubble {
        transition: all 0.3s ease;
    }
    .mood-bubble:hover {
        transform: scale(1.15) rotate(5deg);
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Profile Header Card -->
    <div class="text-center text-white py-4 mb-2">
        <h1 class="text-2xl font-black">👤 My Profile</h1>
        <p class="text-white/80 font-medium">Your wellness journey at a glance</p>
    </div>
    
    <div class="profile-card glass-card rounded-3xl p-8 glow">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <!-- Avatar with animated ring -->
            <div class="avatar-ring w-28 h-28 rounded-full bg-gradient-to-br from-primary via-purple-500 to-pink-500 flex items-center justify-center text-5xl text-white font-black shadow-2xl">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            
            <div class="text-center md:text-left flex-1">
                <h1 class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="text-slate-500 font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-sm text-slate-400 mt-1 font-medium">🎉 Member since <?php echo formatDate($user['created_at'], 'M Y'); ?></p>
            </div>
            
            <a href="edit.php" class="flex items-center gap-2 bg-gradient-to-r from-primary to-secondary text-white px-5 py-2.5 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
        </div>
    </div>
    
    <!-- Stats Cards with animations -->
    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card glass-card rounded-2xl p-5 text-center" style="animation-delay: 0.1s;">
            <div class="stat-value text-3xl font-black bg-gradient-to-r from-primary to-purple-500 bg-clip-text text-transparent" style="animation-delay: 0.2s;"><?php echo $streak['total_logs'] ?? 0; ?></div>
            <div class="text-slate-500 text-sm mt-1 font-bold">Total Moods</div>
        </div>
        <div class="stat-card glass-card rounded-2xl p-5 text-center" style="animation-delay: 0.2s;">
            <div class="stat-value text-3xl font-black text-orange-500 flex items-center justify-center gap-1" style="animation-delay: 0.3s;">
                <span class="text-2xl">🔥</span><?php echo $streak['current_streak'] ?? 0; ?>
            </div>
            <div class="text-slate-500 text-sm mt-1 font-bold">Current Streak</div>
        </div>
        <div class="stat-card glass-card rounded-2xl p-5 text-center" style="animation-delay: 0.3s;">
            <div class="stat-value text-3xl font-black text-yellow-500 flex items-center justify-center gap-1" style="animation-delay: 0.4s;">
                <span class="text-2xl">⭐</span><?php echo $streak['longest_streak'] ?? 0; ?>
            </div>
            <div class="text-slate-500 text-sm mt-1 font-bold">Best Streak</div>
        </div>
    </div>
    
    <!-- Mood Summary Card -->
    <div class="profile-card glass-card rounded-3xl p-6 overflow-hidden relative">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-primary/20 to-purple-500/20 rounded-full blur-2xl"></div>
        
        <h2 class="text-lg font-black text-slate-800 mb-5 flex items-center gap-2 relative z-10">
            🎭 Your Mood Journey
        </h2>
        
        <?php 
        $analytics = getMoodAnalytics($userId, 'all');
        if (!empty($analytics['distribution'])):
        ?>
        <div class="grid grid-cols-5 gap-3 relative z-10">
            <?php 
            $moods = getMoodConfig();
            foreach ($moods as $type => $config): 
                $count = 0;
                foreach ($analytics['distribution'] as $dist) {
                    if ($dist['mood_type'] === $type) {
                        $count = $dist['count'];
                        break;
                    }
                }
            ?>
            <div class="mood-bubble text-center p-3 rounded-2xl bg-white/50 backdrop-blur-sm">
                <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center text-3xl"
                     style="background: linear-gradient(135deg, <?php echo $config['color']; ?>30, <?php echo $config['color']; ?>10);">
                    <?php echo $config['emoji']; ?>
                </div>
                <div class="font-black text-slate-800 mt-2 text-lg"><?php echo $count; ?></div>
                <div class="text-xs text-slate-500 font-bold capitalize"><?php echo $type; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-slate-500 relative z-10">
            <p class="text-3xl mb-3">📝</p>
            <p class="font-bold">Start logging moods to see your journey!</p>
            <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="inline-block mt-3 text-primary hover:underline font-bold">Log your first mood →</a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Quick Links Menu -->
    <div class="profile-card glass-card rounded-3xl overflow-hidden">
        <div class="p-4 border-b border-slate-200/50">
            <h2 class="font-black text-slate-800 flex items-center gap-2">⚡ Quick Access</h2>
        </div>
        
        <a href="edit.php" class="menu-item flex items-center justify-between p-4 border-b border-slate-100/50">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="font-bold text-slate-700">Edit Profile</span>
            </div>
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        
        <a href="settings.php" class="menu-item flex items-center justify-between p-4 border-b border-slate-100/50">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-gradient-to-br from-slate-400 to-slate-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="font-bold text-slate-700">Settings</span>
            </div>
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/pages/mood/analytics.php" class="menu-item flex items-center justify-between p-4 border-b border-slate-100/50">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="font-bold text-slate-700">Mood Analytics</span>
            </div>
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/pages/places/saved.php" class="menu-item flex items-center justify-between p-4">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-gradient-to-br from-pink-400 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </div>
                <span class="font-bold text-slate-700">Saved Places</span>
            </div>
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
    
    <!-- Danger Zone -->
    <div class="profile-card glass-card rounded-3xl p-6 border border-red-200/50">
        <h2 class="text-lg font-black text-red-500 mb-2 flex items-center gap-2">⚠️ Danger Zone</h2>
        <p class="text-slate-500 text-sm mb-4 font-medium">Once you delete your account, there is no going back. Please be certain.</p>
        <button onclick="confirmDeleteAccount()" 
                class="text-red-500 hover:text-white hover:bg-red-500 border border-red-300 px-4 py-2 rounded-xl text-sm font-bold transition-all">
            Delete my account
        </button>
    </div>
    
</div>

<script>
function confirmDeleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
        if (confirm('This will permanently delete all your data including mood history, saved places, and preferences. Continue?')) {
            window.location.href = 'delete-account.php';
        }
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
