<?php
/**
 * MoodMap - Mood History (Enhanced)
 */

$pageTitle = 'Mood History';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$moods = getMoodHistory($userId, 30);

include '../../includes/header.php';
?>

<style>
    .mood-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mood-card:hover {
        transform: translateX(4px);
    }
    .date-badge {
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>

<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="text-center text-white py-4">
        <h1 class="text-2xl font-black mb-2">📊 Mood History</h1>
        <p class="text-white/80 font-medium">Your emotional journey over time</p>
    </div>
    
    <div class="flex items-center justify-between">
        <p class="text-white/70 font-bold text-sm">Last 30 days</p>
        <a href="<?php echo BASE_URL; ?>/pages/mood/analytics.php" class="glass-card px-4 py-2 rounded-xl text-sm font-bold text-slate-700 flex items-center gap-2 hover:scale-105 transition-transform">
            📊 Analytics
        </a>
    </div>
    
    <!-- Mood List -->
    <?php if ($moods): ?>
    <div class="space-y-3">
        <?php 
        $currentDate = '';
        foreach ($moods as $index => $mood):
            $date = date('M j, Y', strtotime($mood['logged_at']));
            $isNewDate = $date !== $currentDate;
            $currentDate = $date;
        ?>
        
        <?php if ($isNewDate): ?>
        <div class="date-badge flex items-center gap-2 pt-4">
            <span class="text-white/60 text-xs font-bold"><?php echo $date; ?></span>
            <div class="flex-1 h-px bg-white/20"></div>
        </div>
        <?php endif; ?>
        
        <div class="mood-card glass-card rounded-2xl p-4 flex items-start gap-4" style="animation-delay: <?php echo $index * 0.05; ?>s">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl shadow-lg" 
                 style="background: linear-gradient(135deg, <?php echo getMoodConfig($mood['mood_type'])['color']; ?>50, <?php echo getMoodConfig($mood['mood_type'])['color']; ?>20);">
                <?php echo getMoodConfig($mood['mood_type'])['emoji']; ?>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <p class="font-black text-slate-800 capitalize"><?php echo $mood['mood_type']; ?></p>
                    <p class="text-xs text-slate-400 font-bold"><?php echo date('g:i A', strtotime($mood['logged_at'])); ?></p>
                </div>
                <div class="flex gap-1 mt-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="w-5 h-1.5 rounded-full <?php echo $i <= ($mood['mood_intensity'] ?? 3) ? 'bg-gradient-to-r from-primary to-purple-500' : 'bg-slate-200'; ?>"></div>
                    <?php endfor; ?>
                </div>
                <?php if (!empty($mood['notes'])): ?>
                <p class="text-sm text-slate-500 mt-2 font-medium"><?php echo htmlspecialchars($mood['notes']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="glass-card rounded-3xl p-12 text-center glow">
        <p class="text-5xl mb-4">📝</p>
        <p class="text-slate-600 font-bold text-lg">No moods logged yet</p>
        <p class="text-slate-400 font-medium mt-2">Start tracking your emotions today!</p>
        <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="inline-block mt-6 bg-gradient-to-r from-primary to-secondary text-white px-8 py-3 rounded-2xl font-black hover:shadow-xl transition-all">Log Your First Mood ✨</a>
    </div>
    <?php endif; ?>
    
</div>

<?php include '../../includes/footer.php'; ?>
