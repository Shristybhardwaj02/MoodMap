<?php
/**
 * MoodMap - Dashboard (Animated with Fixed Week View)
 */

$pageTitle = 'Dashboard';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$todayMood = getTodayMood($userId);
$streak = getMoodStreak($userId);

// Get all moods for last 7 days
function getWeekMoods($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM moods 
            WHERE user_id = ? AND logged_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            ORDER BY logged_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

$weekMoods = getWeekMoods($userId);
$moodsByDate = [];
foreach ($weekMoods as $m) {
    $date = date('Y-m-d', strtotime($m['logged_at']));
    if (!isset($moodsByDate[$date])) $moodsByDate[$date] = [];
    $moodsByDate[$date][] = $m;
}

// Handle AJAX mood log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mood_type'])) {
    header('Content-Type: application/json');
    $moodType = sanitize($_POST['mood_type']);
    $intensity = (int)($_POST['intensity'] ?? 3);
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (in_array($moodType, MOOD_TYPES)) {
        $result = logMood($userId, $moodType, $intensity, $notes);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid mood']);
    }
    exit;
}

include '../../includes/header.php';
?>

<style>
    /* Dashboard-specific animations */
    .mood-emoji {
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        cursor: pointer;
    }
    .mood-emoji:hover {
        transform: scale(1.3) rotate(10deg);
    }
    .mood-emoji.selected {
        animation: selectedPulse 0.6s ease;
        transform: scale(1.2);
    }
    @keyframes selectedPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.4) rotate(15deg); }
        100% { transform: scale(1.2); }
    }
    
    .intensity-btn {
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .intensity-btn:hover {
        transform: scale(1.15);
    }
    .intensity-btn.active {
        animation: intensityPop 0.3s ease;
        background: linear-gradient(135deg, #6366F1, #8B5CF6);
        color: white;
        border-color: transparent;
    }
    @keyframes intensityPop {
        50% { transform: scale(1.3); }
    }
    
    .day-card {
        transition: all 0.3s ease;
    }
    .day-card:hover {
        transform: translateX(5px);
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    .day-card.expanded {
        box-shadow: 0 15px 50px rgba(99, 102, 241, 0.2);
    }
    
    .mood-entry {
        animation: moodSlide 0.4s ease;
    }
    @keyframes moodSlide {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    .confetti {
        position: fixed;
        top: -10px;
        z-index: 1000;
        animation: confettiFall 3s linear forwards;
    }
    @keyframes confettiFall {
        to { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }
    
    .ripple {
        position: relative;
        overflow: hidden;
    }
    .ripple::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255,255,255,0.4);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    .ripple:active::after {
        width: 300px;
        height: 300px;
    }
    
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
    }
    
    .expand-arrow {
        transition: transform 0.3s ease;
    }
    .day-card.expanded .expand-arrow {
        transform: rotate(180deg);
    }
</style>

<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Greeting -->
    <div class="text-center text-white py-4">
        <h1 class="text-3xl font-black mb-2">
            <?php 
            $hour = date('H');
            if ($hour < 12) echo '🌅 Good Morning';
            elseif ($hour < 17) echo '☀️ Good Afternoon';
            else echo '🌙 Good Evening';
            ?>, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!
        </h1>
        <p class="text-white/80 text-lg font-medium">How are you feeling right now?</p>
    </div>
    
    <!-- Mood Selection Card -->
    <div class="glass-card rounded-3xl p-6 glow">
        <h2 class="font-black text-slate-800 mb-5 text-center text-lg">Tap your mood</h2>
        
        <div class="grid grid-cols-5 gap-3 mb-4">
            <?php foreach (MOOD_CONFIG as $mood => $config): ?>
            <button onclick="selectMood('<?php echo $mood; ?>')" 
                    class="mood-emoji flex flex-col items-center p-3 rounded-2xl" 
                    data-mood="<?php echo $mood; ?>">
                <span class="text-4xl md:text-5xl mb-2"><?php echo $config['emoji']; ?></span>
                <span class="text-[10px] md:text-xs font-bold text-slate-600 capitalize"><?php echo $mood; ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Expanded Options -->
        <div id="moodOptions" class="hidden space-y-4 pt-4 border-t border-slate-200">
            <input type="hidden" id="selectedMood" value="">
            
            <div>
                <label class="text-sm font-bold text-slate-600 mb-3 block text-center">Intensity Level</label>
                <div class="flex justify-center gap-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" onclick="setIntensity(<?php echo $i; ?>)" 
                            class="intensity-btn w-11 h-11 rounded-full border-2 border-slate-300 text-slate-600 font-black text-lg" data-val="<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </button>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div>
                <input type="text" id="moodNotes" placeholder="✏️ Add a quick note..." 
                       class="w-full border-2 border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-primary/20 focus:border-primary outline-none text-center font-medium">
            </div>
            
            <button onclick="submitMood()" class="ripple w-full bg-gradient-to-r from-primary via-purple-500 to-pink-500 text-white py-4 rounded-2xl font-black text-lg hover:shadow-2xl">
                Save Mood ✨
            </button>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="grid grid-cols-3 gap-3">
        <div class="stat-card glass-card rounded-2xl p-4 text-center bounce-btn">
            <p class="text-3xl font-black text-orange-500"><?php echo $streak['current_streak'] ?? 0; ?></p>
            <p class="text-xs font-bold text-slate-500"><span class="fire-emoji">🔥</span> Streak</p>
        </div>
        <div class="stat-card glass-card rounded-2xl p-4 text-center bounce-btn">
            <p class="text-3xl font-black text-purple-500"><?php echo $streak['total_logs'] ?? 0; ?></p>
            <p class="text-xs font-bold text-slate-500">📊 Total</p>
        </div>
        <div class="stat-card glass-card rounded-2xl p-4 text-center bounce-btn">
            <p class="text-3xl font-black text-green-500"><?php echo $streak['longest_streak'] ?? 0; ?></p>
            <p class="text-xs font-bold text-slate-500">🏆 Best</p>
        </div>
    </div>
    
    <!-- Week View -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="font-black text-slate-800 mb-4 flex items-center gap-2 text-lg">
            <span>📅</span> This Week
        </h2>
        
        <div class="space-y-3">
            <?php
            $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $today = new DateTime();
            
            for ($i = 6; $i >= 0; $i--):
                $date = (clone $today)->modify("-$i days");
                $dateStr = $date->format('Y-m-d');
                $dayMoods = $moodsByDate[$dateStr] ?? [];
                $isToday = $i === 0;
                $moodCount = count($dayMoods);
                $primaryMood = $dayMoods[0] ?? null;
            ?>
            <div class="day-card rounded-2xl border-2 <?php echo $isToday ? 'border-primary bg-primary/5' : 'border-slate-100 bg-white'; ?> overflow-hidden" data-date="<?php echo $dateStr; ?>">
                <!-- Day Header -->
                <div class="flex items-center justify-between p-4 cursor-pointer" onclick="toggleDay('<?php echo $dateStr; ?>')">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl <?php echo $primaryMood ? 'bg-gradient-to-br from-primary/20 to-purple-200' : 'bg-slate-100'; ?>">
                            <?php echo $primaryMood ? getMoodConfig($primaryMood['mood_type'])['emoji'] : '😶'; ?>
                        </div>
                        <div>
                            <p class="font-black text-slate-800">
                                <?php echo $isToday ? '✨ Today' : ($i === 1 ? 'Yesterday' : $weekDays[$date->format('w')]); ?>
                            </p>
                            <p class="text-sm text-slate-400 font-medium"><?php echo $date->format('M j'); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <?php if ($moodCount > 0): ?>
                        <span class="bg-gradient-to-r from-primary to-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            <?php echo $moodCount; ?> mood<?php echo $moodCount > 1 ? 's' : ''; ?>
                        </span>
                        <span class="expand-arrow text-slate-300">▼</span>
                        <?php else: ?>
                        <span class="text-slate-300 text-xs font-bold">No mood</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Day Details (with all moods) -->
                <?php if ($moodCount > 0): ?>
                <div class="day-details hidden border-t border-slate-100 p-4 bg-gradient-to-b from-slate-50 to-white">
                    <div class="space-y-2">
                        <?php foreach ($dayMoods as $idx => $mood): ?>
                        <div class="mood-entry flex items-center gap-3 bg-white rounded-xl p-3 shadow-sm border border-slate-100" style="animation-delay: <?php echo $idx * 0.1; ?>s">
                            <span class="text-2xl"><?php echo getMoodConfig($mood['mood_type'])['emoji']; ?></span>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-700 capitalize"><?php echo $mood['mood_type']; ?></p>
                                <p class="text-xs text-slate-400"><?php echo date('g:i A', strtotime($mood['logged_at'])); ?></p>
                                <?php if (!empty($mood['notes'])): ?>
                                <p class="text-xs text-slate-500 mt-1 truncate"><?php echo htmlspecialchars($mood['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-1">
                                <?php for ($j = 1; $j <= 5; $j++): ?>
                                <div class="w-2 h-2 rounded-full <?php echo $j <= ($mood['mood_intensity'] ?? 3) ? 'bg-gradient-to-r from-primary to-purple-500' : 'bg-slate-200'; ?>"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="grid grid-cols-2 gap-4">
        <a href="<?php echo BASE_URL; ?>/pages/places/nearby.php" class="glass-card rounded-2xl p-5 bounce-btn flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-red-400 to-orange-500 rounded-xl flex items-center justify-center text-2xl">📍</div>
            <div>
                <p class="font-black text-slate-800">Places</p>
                <p class="text-xs text-slate-500 font-medium">Find spots</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/wellness/tips.php" class="glass-card rounded-2xl p-5 bounce-btn flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-teal-500 rounded-xl flex items-center justify-center text-2xl">💡</div>
            <div>
                <p class="font-black text-slate-800">Wellness</p>
                <p class="text-xs text-slate-500 font-medium">Feel better</p>
            </div>
        </a>
    </div>

</div>
</main>

<script>
// Mood themes for background
const moodThemes = {
    happy: { bg: 'bg-happy', illustration: '🌞', particles: ['#ffd700', '#ff6b6b', '#ffa07a'] },
    sad: { bg: 'bg-sad', illustration: '🌧️', particles: ['#4facfe', '#00f2fe', '#43e97b'] },
    anxious: { bg: 'bg-anxious', illustration: '🌪️', particles: ['#a18cd1', '#fbc2eb', '#f5576c'] },
    calm: { bg: 'bg-calm', illustration: '🌿', particles: ['#11998e', '#38ef7d', '#a8edea'] },
    energetic: { bg: 'bg-energetic', illustration: '⚡', particles: ['#fc466b', '#3f5efb', '#00d9ff'] }
};

let selectedMood = null;
let selectedIntensity = 3;

// Update background theme
function updateTheme(mood) {
    const bg = document.getElementById('moodBg');
    const illustration = document.getElementById('moodIllustration');
    const particles = document.querySelectorAll('.particle');
    
    bg.className = 'mood-bg';
    
    if (mood && moodThemes[mood]) {
        bg.classList.add(moodThemes[mood].bg);
        illustration.textContent = moodThemes[mood].illustration;
        
        particles.forEach((p, i) => {
            const colors = moodThemes[mood].particles;
            p.style.background = colors[i % colors.length];
        });
    } else {
        bg.classList.add('bg-default');
        illustration.textContent = '🌈';
    }
}

// Select mood
function selectMood(mood) {
    selectedMood = mood;
    document.getElementById('selectedMood').value = mood;
    
    updateTheme(mood);
    
    document.querySelectorAll('.mood-emoji').forEach(btn => {
        if (btn.dataset.mood === mood) {
            btn.classList.add('selected');
            btn.style.opacity = '1';
        } else {
            btn.classList.remove('selected');
            btn.style.opacity = '0.4';
        }
    });
    
    const options = document.getElementById('moodOptions');
    options.classList.remove('hidden');
    
    setIntensity(3);
}

// Set intensity
function setIntensity(val) {
    selectedIntensity = val;
    document.querySelectorAll('.intensity-btn').forEach(btn => {
        if (parseInt(btn.dataset.val) === val) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// Create confetti
function createConfetti() {
    const colors = ['#ff6b6b', '#ffd700', '#4ecdc4', '#a18cd1', '#ff9ff3', '#54a0ff', '#6366F1'];
    for (let i = 0; i < 60; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.width = (Math.random() * 12 + 6) + 'px';
        confetti.style.height = confetti.style.width;
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
        confetti.style.animationDelay = (Math.random() * 0.5) + 's';
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 3000);
    }
}

// Submit mood
function submitMood() {
    if (!selectedMood) return;
    
    const notes = document.getElementById('moodNotes').value;
    
    createConfetti();
    
    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mood_type=${selectedMood}&intensity=${selectedIntensity}&notes=${encodeURIComponent(notes)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            setTimeout(() => location.reload(), 1200);
        } else {
            alert(data.message || 'Error saving mood');
        }
    });
}

// Toggle day details
function toggleDay(dateStr) {
    const card = document.querySelector(`.day-card[data-date="${dateStr}"]`);
    const details = card?.querySelector('.day-details');
    
    if (!details) return;
    
    const isExpanded = card.classList.contains('expanded');
    
    // Close all others
    document.querySelectorAll('.day-card').forEach(c => {
        c.classList.remove('expanded');
        c.querySelector('.day-details')?.classList.add('hidden');
    });
    
    // Toggle current
    if (!isExpanded) {
        card.classList.add('expanded');
        details.classList.remove('hidden');
    }
}

// Auto-expand today on load
document.addEventListener('DOMContentLoaded', () => {
    const today = '<?php echo date('Y-m-d'); ?>';
    setTimeout(() => toggleDay(today), 500);
});
</script>

<?php include '../../includes/footer.php'; ?>
