<?php
/**
 * MoodMap - Log Mood (Minimal)
 */

$pageTitle = 'Log Mood';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$error = '';

// Handle AJAX quick log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_log'])) {
    header('Content-Type: application/json');
    $moodType = sanitize($_POST['mood_type'] ?? '');
    
    if (!empty($moodType) && in_array($moodType, MOOD_TYPES)) {
        $result = logMood($userId, $moodType, 3, '');
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid mood']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $moodType = sanitize($_POST['mood_type'] ?? '');
    $intensity = (int)($_POST['intensity'] ?? 3);
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($moodType) || !in_array($moodType, MOOD_TYPES)) {
        $error = 'Please select a mood';
    } else {
        $result = logMood($userId, $moodType, $intensity, $notes);
        if ($result['success']) {
            redirectWith(BASE_URL . '/pages/dashboard/index.php', $result['message'], 'success');
        } else {
            $error = $result['message'];
        }
    }
}

include '../../includes/header.php';
?>

<div class="max-w-lg mx-auto space-y-6">
    
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-2xl font-bold text-slate-800">How are you feeling?</h1>
        <p class="text-slate-500 text-sm mt-1">Track your emotional journey</p>
    </div>
    
    <?php if ($error): ?>
    <div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="space-y-6">
        <input type="hidden" name="mood_type" id="selected_mood" value="">
        
        <!-- Mood Grid -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <div class="grid grid-cols-5 gap-4">
                <?php foreach (MOOD_CONFIG as $mood => $config): ?>
                <button type="button" data-mood="<?php echo $mood; ?>"
                        class="mood-btn flex flex-col items-center p-3 rounded-xl transition-all hover:scale-105"
                        style="background: <?php echo $config['color']; ?>15;">
                    <span class="text-4xl mb-2"><?php echo $config['emoji']; ?></span>
                    <span class="text-xs text-slate-600 capitalize"><?php echo $mood; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Details Section (hidden until mood selected) -->
        <div id="details" class="hidden space-y-4">
            
            <!-- Intensity -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <label class="font-medium text-slate-800 mb-3 block">Intensity</label>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" data-intensity="<?php echo $i; ?>"
                            class="intensity-btn flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-medium hover:border-primary hover:text-primary transition-all">
                        <?php echo $i; ?>
                    </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="intensity" id="intensity" value="3">
            </div>
            
            <!-- Notes -->
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <label class="font-medium text-slate-800 mb-3 block">Notes (optional)</label>
                <textarea name="notes" rows="3" placeholder="What's on your mind?" 
                          class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none resize-none"></textarea>
            </div>
            
            <!-- Submit -->
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-4 rounded-xl font-semibold hover:shadow-lg transition-all">
                Save Mood
            </button>
        </div>
        
    </form>
    
</div>

<script>
// Mood selection
document.querySelectorAll('.mood-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.mood-btn').forEach(b => {
            b.classList.remove('ring-2', 'ring-primary', 'scale-110');
        });
        this.classList.add('ring-2', 'ring-primary', 'scale-110');
        document.getElementById('selected_mood').value = this.dataset.mood;
        document.getElementById('details').classList.remove('hidden');
    });
});

// Intensity selection
document.querySelectorAll('.intensity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.intensity-btn').forEach(b => {
            b.classList.remove('bg-primary', 'text-white', 'border-primary');
            b.classList.add('border-slate-200', 'text-slate-600');
        });
        this.classList.add('bg-primary', 'text-white', 'border-primary');
        this.classList.remove('border-slate-200', 'text-slate-600');
        document.getElementById('intensity').value = this.dataset.intensity;
    });
});

// Pre-select intensity 3
document.querySelector('[data-intensity="3"]')?.click();
</script>

<?php include '../../includes/footer.php'; ?>
