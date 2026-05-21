<?php
/**
 * MoodMap - Mood Analytics
 * 
 * Charts and insights about mood patterns.
 */

$pageTitle = 'Mood Analytics';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Get analytics for different periods
$weeklyAnalytics = getMoodAnalytics($userId, 'week');
$monthlyAnalytics = getMoodAnalytics($userId, 'month');

$moods = getMoodConfig();

// Prepare chart data
$moodColors = [];
$moodLabels = [];
foreach ($moods as $type => $config) {
    $moodColors[$type] = $config['color'];
    $moodLabels[$type] = $config['label'];
}

include '../../includes/header.php';
?>

<style>
    .analytics-card {
        transition: all 0.3s ease;
    }
    .analytics-card:hover {
        transform: translateY(-4px);
    }
    .stat-value {
        animation: countUp 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    @keyframes countUp {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="text-center text-white py-4">
        <h1 class="text-2xl font-black mb-2">📈 Mood Analytics</h1>
        <p class="text-white/80 font-medium">Discover patterns in your emotional journey</p>
    </div>
    
    <div class="flex justify-end">
        <a href="history.php" class="glass-card px-4 py-2 rounded-xl text-sm font-bold text-slate-700 flex items-center gap-2 hover:scale-105 transition-transform">
            📅 History View
        </a>
    </div>
    
    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="analytics-card glass-card rounded-2xl p-5">
            <p class="text-xs text-slate-500 font-bold mb-1">📅 This Week</p>
            <p class="stat-value text-3xl font-black bg-gradient-to-r from-primary to-purple-500 bg-clip-text text-transparent"><?php echo $weeklyAnalytics['total_logs']; ?></p>
            <p class="text-xs text-slate-400 font-bold">mood logs</p>
        </div>
        
        <div class="analytics-card glass-card rounded-2xl p-5">
            <p class="text-xs text-slate-500 font-bold mb-1">📆 This Month</p>
            <p class="stat-value text-3xl font-black bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent"><?php echo $monthlyAnalytics['total_logs']; ?></p>
            <p class="text-xs text-slate-400 font-bold">mood logs</p>
        </div>
        
        <div class="analytics-card glass-card rounded-2xl p-5">
            <p class="text-xs text-slate-500 font-bold mb-1">😊 Most Common</p>
            <?php if ($weeklyAnalytics['most_common_mood']): 
                $commonConfig = getMoodConfig($weeklyAnalytics['most_common_mood']);
            ?>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-2xl"><?php echo $commonConfig['emoji']; ?></span>
                <span class="font-black text-slate-800"><?php echo $commonConfig['label']; ?></span>
            </div>
            <?php else: ?>
            <p class="text-slate-400 font-bold">No data</p>
            <?php endif; ?>
        </div>
        
        <div class="analytics-card glass-card rounded-2xl p-5">
            <p class="text-xs text-slate-500 font-bold mb-1">⚡ Avg Intensity</p>
            <?php 
            $avgIntensity = 0;
            if (!empty($weeklyAnalytics['distribution'])) {
                $total = 0;
                foreach ($weeklyAnalytics['distribution'] as $d) {
                    $total += $d['avg_intensity'];
                }
                $avgIntensity = round($total / count($weeklyAnalytics['distribution']), 1);
            }
            ?>
            <p class="stat-value text-3xl font-black bg-gradient-to-r from-green-500 to-teal-500 bg-clip-text text-transparent"><?php echo $avgIntensity; ?>/5</p>
            <p class="text-xs text-slate-400 font-bold">this week</p>
        </div>
    </div>
    
    <div class="grid lg:grid-cols-2 gap-6">
        
        <!-- Mood Distribution Pie Chart -->
        <div class="analytics-card glass-card rounded-3xl p-6">
            <h2 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">🥧 Mood Distribution</h2>
            
            <?php if (!empty($monthlyAnalytics['distribution'])): ?>
            <div class="chart-container" style="height: 300px;">
                <canvas id="moodPieChart"></canvas>
            </div>
            <?php else: ?>
            <div class="text-center py-12 text-slate-500 font-medium">
                <p class="text-3xl mb-2">📊</p>
                <p>Not enough data yet. Keep logging your moods!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Weekly Trend Line Chart -->
        <div class="analytics-card glass-card rounded-3xl p-6">
            <h2 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">📈 Weekly Trend</h2>
            
            <?php if (!empty($weeklyAnalytics['daily'])): ?>
            <div class="chart-container" style="height: 300px;">
                <canvas id="moodLineChart"></canvas>
            </div>
            <?php else: ?>
            <div class="text-center py-12 text-slate-500 font-medium">
                <p class="text-3xl mb-2">📉</p>
                <p>Not enough data yet. Keep logging your moods!</p>
            </div>
            <?php endif; ?>
        </div>
        
    </div>
    
    <!-- Mood Breakdown -->
    <div class="analytics-card glass-card rounded-3xl p-6">
        <h2 class="text-lg font-black text-slate-800 mb-5 flex items-center gap-2">🎭 Mood Breakdown</h2>
        
        <?php if (!empty($monthlyAnalytics['distribution'])): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($monthlyAnalytics['distribution'] as $dist): 
                $config = getMoodConfig($dist['mood_type']);
                $percentage = round(($dist['count'] / $monthlyAnalytics['total_logs']) * 100);
            ?>
            <div class="p-4 rounded-2xl transition-transform hover:scale-105" style="background: linear-gradient(135deg, <?php echo $config['color']; ?>20, <?php echo $config['color']; ?>05);">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl"><?php echo $config['emoji']; ?></span>
                    <span class="font-black text-slate-800"><?php echo $config['label']; ?></span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black" style="color: <?php echo $config['color']; ?>;">
                        <?php echo $dist['count']; ?>
                    </span>
                    <span class="text-slate-500 text-sm font-bold">(<?php echo $percentage; ?>%)</span>
                </div>
                <div class="mt-2 h-2 bg-white rounded-full overflow-hidden shadow-inner">
                    <div class="h-full rounded-full transition-all duration-500" 
                         style="width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, <?php echo $config['color']; ?>, <?php echo $config['color']; ?>80);"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-slate-500">
            <p class="font-bold">Log more moods to see detailed analytics!</p>
            <a href="<?php echo BASE_URL; ?>/pages/dashboard/index.php" class="inline-block mt-3 text-primary font-black hover:underline">Log a mood →</a>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Insights -->
    <div class="analytics-card glass-card rounded-3xl p-6 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-gradient-to-br from-primary/10 to-purple-500/10 rounded-full blur-2xl"></div>
        
        <h2 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2 relative z-10">💡 Your Insights</h2>
        
        <div class="grid md:grid-cols-2 gap-4 relative z-10">
            <?php if ($weeklyAnalytics['most_common_mood']): ?>
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-4 border border-white/50">
                <p class="text-slate-600 font-medium">
                    <span class="font-black">🎯 Most frequent:</span> You've been feeling 
                    <span class="font-black text-primary"><?php echo getMoodConfig($weeklyAnalytics['most_common_mood'])['label']; ?></span> 
                    most often this week.
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($monthlyAnalytics['total_logs'] > 0): ?>
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-4 border border-white/50">
                <p class="text-slate-600 font-medium">
                    <span class="font-black">📊 Consistency:</span> You've logged 
                    <span class="font-black text-green-500"><?php echo $monthlyAnalytics['total_logs']; ?> moods</span> 
                    this month. Keep it up!
                </p>
            </div>
            <?php endif; ?>
            
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-4 border border-white/50">
                <p class="text-slate-600 font-medium">
                    <span class="font-black">⏰ Tip:</span> Logging consistently at the same times daily helps identify patterns better.
                </p>
            </div>
            
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-4 border border-white/50">
                <p class="text-slate-600 font-medium">
                    <span class="font-black">💜 Remember:</span> All emotions are valid. Tracking helps you understand yourself better.
                </p>
            </div>
        </div>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moodColors = <?php echo json_encode($moodColors); ?>;
    const moodLabels = <?php echo json_encode($moodLabels); ?>;
    
    // Pie Chart - Mood Distribution
    <?php if (!empty($monthlyAnalytics['distribution'])): ?>
    const pieCtx = document.getElementById('moodPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php 
                echo implode(',', array_map(function($d) use ($moodLabels) {
                    return "'" . $moodLabels[$d['mood_type']] . "'";
                }, $monthlyAnalytics['distribution']));
            ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($monthlyAnalytics['distribution'], 'count')); ?>],
                backgroundColor: [<?php 
                    echo implode(',', array_map(function($d) use ($moodColors) {
                        return "'" . $moodColors[$d['mood_type']] . "'";
                    }, $monthlyAnalytics['distribution']));
                ?>],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });
    <?php endif; ?>
    
    // Line Chart - Weekly Trend
    <?php if (!empty($weeklyAnalytics['daily'])): ?>
    const lineCtx = document.getElementById('moodLineChart').getContext('2d');
    
    // Group by date and get average intensity
    const dailyData = {};
    <?php foreach ($weeklyAnalytics['daily'] as $mood): ?>
    dailyData['<?php echo $mood['date']; ?>'] = dailyData['<?php echo $mood['date']; ?>'] || [];
    dailyData['<?php echo $mood['date']; ?>'].push(<?php echo $mood['mood_intensity']; ?>);
    <?php endforeach; ?>
    
    const labels = Object.keys(dailyData).sort();
    const data = labels.map(date => {
        const intensities = dailyData[date];
        return intensities.reduce((a, b) => a + b, 0) / intensities.length;
    });
    
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: labels.map(d => new Date(d).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })),
            datasets: [{
                label: 'Mood Intensity',
                data: data,
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366F1',
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    min: 1,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php include '../../includes/footer.php'; ?>
