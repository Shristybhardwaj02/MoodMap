<?php
/**
 * MoodMap - Wellness Activities
 * 
 * Mood-based activity suggestions.
 */

$pageTitle = 'Activities';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$todayMood = getTodayMood($userId);
$currentMoodType = $todayMood ? $todayMood['mood_type'] : 'calm';
$moodConfig = getMoodConfig($currentMoodType);

// Get activities for each mood category
$moods = getMoodConfig();

include '../../includes/header.php';
?>

<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Wellness Activities</h1>
            <p class="text-slate-500">Activities to boost your mood</p>
        </div>
    </div>
    
    <!-- Current Mood Recommendations -->
    <div class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl"
                 style="background-color: <?php echo $moodConfig['color']; ?>30;">
                <?php echo $moodConfig['emoji']; ?>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    Recommended for your <?php echo $moodConfig['label']; ?> mood
                </h2>
                <p class="text-slate-600">Activities specifically chosen to help you right now</p>
            </div>
        </div>
        
        <?php 
        $recommendedActivities = getActivities($currentMoodType, 4);
        if (!empty($recommendedActivities)):
        ?>
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($recommendedActivities as $activity): ?>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <h3 class="font-semibold text-slate-800 mb-2"><?php echo htmlspecialchars($activity['title']); ?></h3>
                <p class="text-slate-600 text-sm"><?php echo htmlspecialchars($activity['content']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-slate-500">No activities available for this mood. Check back later!</p>
        <?php endif; ?>
    </div>
    
    <!-- Browse by Mood -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Browse Activities by Mood</h2>
        
        <!-- Mood Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ($moods as $type => $config): ?>
            <button onclick="showMoodActivities('<?php echo $type; ?>')"
                    class="mood-tab flex items-center gap-2 px-4 py-2 rounded-xl transition-all <?php echo $type === $currentMoodType ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>"
                    data-mood="<?php echo $type; ?>">
                <span><?php echo $config['emoji']; ?></span>
                <span><?php echo $config['label']; ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Activities Container -->
        <?php foreach ($moods as $type => $config): 
            $activities = getActivities($type, 6);
        ?>
        <div id="activities-<?php echo $type; ?>" 
             class="mood-activities <?php echo $type === $currentMoodType ? '' : 'hidden'; ?>">
            
            <?php if (!empty($activities)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($activities as $activity): ?>
                <div class="border border-slate-200 rounded-xl p-4 hover:border-primary transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: <?php echo $config['color']; ?>20;">
                            <span class="text-lg">✨</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($activity['title']); ?></h3>
                            <p class="text-slate-600 text-sm mt-1"><?php echo htmlspecialchars($activity['content']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-slate-500">
                <p>No activities available for this mood yet.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Breathing Exercises -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">🧘 Quick Breathing Exercises</h2>
        
        <div class="grid md:grid-cols-3 gap-4">
            <!-- Box Breathing -->
            <div class="border border-slate-200 rounded-xl p-5 hover:shadow-md transition-all cursor-pointer"
                 onclick="startBreathing('box')">
                <h3 class="font-semibold text-slate-800 mb-2">Box Breathing</h3>
                <p class="text-sm text-slate-500 mb-3">4-4-4-4 technique for calm</p>
                <div class="flex items-center gap-2 text-primary text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start 2 min session
                </div>
            </div>
            
            <!-- 4-7-8 Breathing -->
            <div class="border border-slate-200 rounded-xl p-5 hover:shadow-md transition-all cursor-pointer"
                 onclick="startBreathing('relaxing')">
                <h3 class="font-semibold text-slate-800 mb-2">4-7-8 Breathing</h3>
                <p class="text-sm text-slate-500 mb-3">Relaxing technique for anxiety</p>
                <div class="flex items-center gap-2 text-primary text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start 3 min session
                </div>
            </div>
            
            <!-- Energizing Breath -->
            <div class="border border-slate-200 rounded-xl p-5 hover:shadow-md transition-all cursor-pointer"
                 onclick="startBreathing('energizing')">
                <h3 class="font-semibold text-slate-800 mb-2">Energizing Breath</h3>
                <p class="text-sm text-slate-500 mb-3">Quick breaths to boost energy</p>
                <div class="flex items-center gap-2 text-primary text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start 1 min session
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Breathing Exercise Modal -->
<div id="breathing-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 text-center">
        <button onclick="stopBreathing()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <h3 id="breathing-title" class="text-xl font-semibold text-slate-800 mb-2">Box Breathing</h3>
        <p id="breathing-instruction" class="text-slate-500 mb-6">Follow the circle</p>
        
        <div class="relative w-48 h-48 mx-auto mb-6">
            <div id="breathing-circle" class="w-full h-full rounded-full bg-primary/20 flex items-center justify-center transition-all duration-1000">
                <span id="breathing-text" class="text-3xl font-bold text-primary">4</span>
            </div>
        </div>
        
        <p id="breathing-phase" class="text-lg text-slate-600 mb-4">Breathe In</p>
        
        <button onclick="stopBreathing()" class="text-slate-500 hover:text-slate-700">
            End Session
        </button>
    </div>
</div>

<script>
function showMoodActivities(mood) {
    // Hide all activities
    document.querySelectorAll('.mood-activities').forEach(el => el.classList.add('hidden'));
    
    // Show selected
    document.getElementById('activities-' + mood).classList.remove('hidden');
    
    // Update tabs
    document.querySelectorAll('.mood-tab').forEach(tab => {
        if (tab.dataset.mood === mood) {
            tab.classList.remove('bg-slate-100', 'text-slate-700');
            tab.classList.add('bg-primary', 'text-white');
        } else {
            tab.classList.remove('bg-primary', 'text-white');
            tab.classList.add('bg-slate-100', 'text-slate-700');
        }
    });
}

let breathingInterval = null;

function startBreathing(type) {
    const modal = document.getElementById('breathing-modal');
    const circle = document.getElementById('breathing-circle');
    const text = document.getElementById('breathing-text');
    const phase = document.getElementById('breathing-phase');
    const title = document.getElementById('breathing-title');
    
    const patterns = {
        box: { inhale: 4, hold1: 4, exhale: 4, hold2: 4, name: 'Box Breathing' },
        relaxing: { inhale: 4, hold1: 7, exhale: 8, hold2: 0, name: '4-7-8 Breathing' },
        energizing: { inhale: 2, hold1: 0, exhale: 2, hold2: 0, name: 'Energizing Breath' }
    };
    
    const pattern = patterns[type];
    title.textContent = pattern.name;
    modal.classList.remove('hidden');
    
    let currentPhase = 'inhale';
    let count = pattern.inhale;
    
    function updateBreathing() {
        text.textContent = count;
        
        if (currentPhase === 'inhale') {
            phase.textContent = 'Breathe In';
            circle.style.transform = 'scale(1.2)';
        } else if (currentPhase === 'hold1') {
            phase.textContent = 'Hold';
            circle.style.transform = 'scale(1.2)';
        } else if (currentPhase === 'exhale') {
            phase.textContent = 'Breathe Out';
            circle.style.transform = 'scale(1)';
        } else if (currentPhase === 'hold2') {
            phase.textContent = 'Hold';
            circle.style.transform = 'scale(1)';
        }
        
        count--;
        
        if (count < 0) {
            // Move to next phase
            if (currentPhase === 'inhale' && pattern.hold1 > 0) {
                currentPhase = 'hold1';
                count = pattern.hold1;
            } else if (currentPhase === 'inhale' || currentPhase === 'hold1') {
                currentPhase = 'exhale';
                count = pattern.exhale;
            } else if (currentPhase === 'exhale' && pattern.hold2 > 0) {
                currentPhase = 'hold2';
                count = pattern.hold2;
            } else {
                currentPhase = 'inhale';
                count = pattern.inhale;
            }
        }
    }
    
    updateBreathing();
    breathingInterval = setInterval(updateBreathing, 1000);
}

function stopBreathing() {
    clearInterval(breathingInterval);
    document.getElementById('breathing-modal').classList.add('hidden');
    document.getElementById('breathing-circle').style.transform = 'scale(1)';
}
</script>

<?php include '../../includes/footer.php'; ?>
