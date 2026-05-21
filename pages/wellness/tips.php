<?php
/**
 * MoodMap - Wellness Tips (Animated)
 */

$pageTitle = 'Wellness';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$todayMood = getTodayMood($userId);
$currentMood = isset($_GET['mood']) && in_array($_GET['mood'], MOOD_TYPES) ? $_GET['mood'] : ($todayMood ? $todayMood['mood_type'] : 'calm');

// Tips by mood
$tips = [
    'happy' => [
        ['🎉', 'Share Your Joy', 'Call someone you love and spread the positivity around!', 'bg-gradient-to-br from-yellow-400 to-orange-500'],
        ['📝', 'Gratitude Journal', 'Write down 3 things that made you smile today', 'bg-gradient-to-br from-pink-400 to-red-500'],
        ['🎵', 'Create Playlist', 'Make a collection of happy songs for future days', 'bg-gradient-to-br from-green-400 to-teal-500'],
        ['📸', 'Capture Moments', 'Take photos to remember this good day', 'bg-gradient-to-br from-blue-400 to-purple-500'],
    ],
    'sad' => [
        ['☀️', 'Get Some Sunlight', '10 minutes of natural light can boost your serotonin', 'bg-gradient-to-br from-yellow-400 to-orange-500'],
        ['🏃', 'Move Your Body', 'A short walk releases endorphins and clears your mind', 'bg-gradient-to-br from-green-400 to-teal-500'],
        ['💬', 'Talk to Someone', 'Share your feelings with a friend or family member', 'bg-gradient-to-br from-blue-400 to-cyan-500'],
        ['🎧', 'Listen to Music', 'Put on your favorite uplifting songs', 'bg-gradient-to-br from-purple-400 to-pink-500'],
    ],
    'anxious' => [
        ['🧘', 'Deep Breathing', 'Try 4-7-8: Inhale 4s, hold 7s, exhale 8s. Repeat 4 times.', 'bg-gradient-to-br from-teal-400 to-green-500'],
        ['✍️', 'Write It Out', 'List your worries, then write a counter for each one', 'bg-gradient-to-br from-purple-400 to-indigo-500'],
        ['🎧', 'Calming Sounds', 'Listen to nature sounds, rain, or white noise', 'bg-gradient-to-br from-blue-400 to-cyan-500'],
        ['🌿', 'Grounding Exercise', 'Name 5 things you see, 4 you hear, 3 you touch', 'bg-gradient-to-br from-green-400 to-lime-500'],
    ],
    'calm' => [
        ['📚', 'Read a Book', 'Enjoy a good book with a cup of tea', 'bg-gradient-to-br from-amber-400 to-orange-500'],
        ['🧘', 'Meditate', 'Deepen your calm with 10 minutes of meditation', 'bg-gradient-to-br from-purple-400 to-pink-500'],
        ['🌿', 'Nature Time', 'Spend some quiet time in a garden or by plants', 'bg-gradient-to-br from-green-400 to-teal-500'],
        ['🎨', 'Creative Time', 'Draw, color, or do something creative', 'bg-gradient-to-br from-blue-400 to-indigo-500'],
    ],
    'energetic' => [
        ['💪', 'Workout', 'Channel that energy into a great workout session', 'bg-gradient-to-br from-red-400 to-orange-500'],
        ['📋', 'Be Productive', 'Tackle your to-do list while you have the energy', 'bg-gradient-to-br from-blue-400 to-indigo-500'],
        ['🎨', 'Create Something', 'Paint, write, build, code, or craft something new', 'bg-gradient-to-br from-purple-400 to-pink-500'],
        ['🏃', 'Go Outside', 'Run, cycle, or do outdoor activities', 'bg-gradient-to-br from-green-400 to-cyan-500'],
    ],
];

$currentTips = $tips[$currentMood] ?? $tips['calm'];

include '../../includes/header.php';
?>

<style>
    /* Page-specific animations */
    .tip-card {
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .tip-card:hover {
        transform: translateY(-8px) scale(1.02);
    }
    .tip-card:active {
        transform: scale(0.98);
    }
    
    .mood-pill {
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    .mood-pill:hover {
        transform: scale(1.1) rotate(-3deg);
    }
    .mood-pill.active {
        animation: pillPop 0.4s ease;
    }
    @keyframes pillPop {
        50% { transform: scale(1.2); }
    }
    
    .activity-card {
        transition: all 0.3s ease;
    }
    .activity-card:hover {
        transform: scale(1.08);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .activity-card:active {
        transform: scale(0.95);
    }
    
    .breathing-circle {
        animation: breathe 8s infinite ease-in-out;
    }
    @keyframes breathe {
        0%, 100% { transform: scale(1); opacity: 0.3; }
        50% { transform: scale(1.3); opacity: 0.1; }
    }
    
    .tip-icon {
        animation: iconFloat 3s ease-in-out infinite;
    }
    @keyframes iconFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
</style>

<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="text-center text-white py-4">
        <h1 class="text-2xl font-black mb-2">💡 Wellness Tips</h1>
        <p class="text-white/80 font-medium">
            Personalized for 
            <?php echo getMoodConfig($currentMood)['emoji']; ?> 
            <span class="capitalize"><?php echo $currentMood; ?></span> mood
        </p>
    </div>
    
    <!-- Tips Cards -->
    <div class="space-y-4">
        <?php foreach ($currentTips as $i => $tip): ?>
        <div class="tip-card glass-card rounded-3xl p-6 flex items-start gap-5" style="animation-delay: <?php echo $i * 0.1; ?>s">
            <div class="tip-icon w-16 h-16 <?php echo $tip[3]; ?> rounded-2xl flex items-center justify-center text-3xl text-white shadow-lg flex-shrink-0">
                <?php echo $tip[0]; ?>
            </div>
            <div>
                <h3 class="font-black text-slate-800 text-lg"><?php echo $tip[1]; ?></h3>
                <p class="text-slate-500 mt-1 font-medium"><?php echo $tip[2]; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Quick Activities -->
    <div class="glass-card rounded-3xl p-6 glow overflow-hidden relative">
        <!-- Breathing Circle Background -->
        <div class="breathing-circle absolute -top-20 -right-20 w-60 h-60 bg-gradient-to-br from-primary to-purple-500 rounded-full"></div>
        
        <div class="relative z-10">
            <h2 class="font-black text-slate-800 text-lg mb-5 flex items-center gap-2">
                ⚡ Quick Activities
            </h2>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="startBreathing()" class="activity-card bg-gradient-to-br from-teal-400 to-green-500 rounded-2xl p-5 text-white text-center">
                    <p class="text-3xl mb-2">🧘</p>
                    <p class="font-black">Breathe</p>
                    <p class="text-xs opacity-80">2 min exercise</p>
                </button>
                <button onclick="startWalk()" class="activity-card bg-gradient-to-br from-orange-400 to-red-500 rounded-2xl p-5 text-white text-center">
                    <p class="text-3xl mb-2">🚶</p>
                    <p class="font-black">Walk</p>
                    <p class="text-xs opacity-80">5 min timer</p>
                </button>
                <button onclick="drinkWater()" class="activity-card bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl p-5 text-white text-center">
                    <p class="text-3xl mb-2">💧</p>
                    <p class="font-black">Hydrate</p>
                    <p class="text-xs opacity-80">Drink water</p>
                </button>
                <button onclick="goOutside()" class="activity-card bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl p-5 text-white text-center">
                    <p class="text-3xl mb-2">🌿</p>
                    <p class="font-black">Fresh Air</p>
                    <p class="text-xs opacity-80">Step outside</p>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mood Switch -->
    <div class="glass-card rounded-3xl p-6">
        <h2 class="font-black text-slate-800 text-lg mb-4">Tips for other moods</h2>
        <div class="flex gap-3 flex-wrap">
            <?php foreach (MOOD_CONFIG as $mood => $config): ?>
            <a href="?mood=<?php echo $mood; ?>" 
               class="mood-pill flex items-center gap-2 px-5 py-3 rounded-full text-sm font-bold <?php echo $mood === $currentMood ? 'active bg-gradient-to-r from-primary to-secondary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                <span class="text-lg"><?php echo $config['emoji']; ?></span>
                <span class="capitalize"><?php echo $mood; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>
</main>

<!-- Breathing Modal -->
<div id="breathingModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBreathing()"></div>
    <div class="relative glass-card rounded-3xl p-8 max-w-sm w-full text-center">
        <button onclick="closeBreathing()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        <h3 class="text-xl font-black text-slate-800 mb-4">🧘 Breathing Exercise</h3>
        <div id="breathCircle" class="w-40 h-40 mx-auto rounded-full bg-gradient-to-br from-teal-400 to-green-500 flex items-center justify-center mb-4">
            <span id="breathText" class="text-white font-black text-xl">Ready</span>
        </div>
        <p id="breathInstruction" class="text-slate-500 font-medium">Tap circle to start</p>
        <button onclick="startBreathingCycle()" id="breathBtn" class="mt-4 bg-gradient-to-r from-primary to-secondary text-white px-8 py-3 rounded-full font-bold bounce-btn">
            Start
        </button>
    </div>
</div>

<script>
// Update background based on mood
document.addEventListener('DOMContentLoaded', () => {
    const mood = '<?php echo $currentMood; ?>';
    const bg = document.getElementById('moodBg');
    const illustration = document.getElementById('moodIllustration');
    
    bg.className = 'mood-bg bg-' + mood;
    
    const illustrations = { happy: '🌞', sad: '🌧️', anxious: '🌪️', calm: '🌿', energetic: '⚡' };
    illustration.textContent = illustrations[mood] || '🌈';
});

// Quick Activities
function startBreathing() {
    document.getElementById('breathingModal').classList.remove('hidden');
}

function closeBreathing() {
    document.getElementById('breathingModal').classList.add('hidden');
    breathRunning = false;
}

let breathRunning = false;
async function startBreathingCycle() {
    if (breathRunning) return;
    breathRunning = true;
    
    const circle = document.getElementById('breathCircle');
    const text = document.getElementById('breathText');
    const instruction = document.getElementById('breathInstruction');
    const btn = document.getElementById('breathBtn');
    
    btn.textContent = 'Running...';
    btn.disabled = true;
    
    const phases = [
        { text: 'Inhale', duration: 4000, scale: 1.3 },
        { text: 'Hold', duration: 7000, scale: 1.3 },
        { text: 'Exhale', duration: 8000, scale: 1 }
    ];
    
    for (let round = 0; round < 3; round++) {
        instruction.textContent = `Round ${round + 1} of 3`;
        
        for (const phase of phases) {
            if (!breathRunning) break;
            text.textContent = phase.text;
            circle.style.transform = `scale(${phase.scale})`;
            circle.style.transition = `transform ${phase.duration}ms ease-in-out`;
            await new Promise(r => setTimeout(r, phase.duration));
        }
    }
    
    if (breathRunning) {
        text.textContent = '✨';
        instruction.textContent = 'Great job! You completed the exercise.';
    }
    
    btn.textContent = 'Start Again';
    btn.disabled = false;
    breathRunning = false;
}

function startWalk() {
    alert('🚶 5 minute walk timer started!\n\nGet up and take a short walk. You\'ll feel refreshed!');
}

function drinkWater() {
    alert('💧 Great reminder!\n\nDrink a full glass of water now. Staying hydrated helps your mood!');
}

function goOutside() {
    alert('🌿 Fresh air time!\n\nStep outside for a few minutes. Nature helps reset your mind.');
}
</script>

<?php include '../../includes/footer.php'; ?>
