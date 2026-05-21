<?php
/**
 * MoodMap - Food Suggestions
 * 
 * Mood-boosting food recommendations.
 */

$pageTitle = 'Food Ideas';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$todayMood = getTodayMood($userId);
$currentMoodType = $todayMood ? $todayMood['mood_type'] : 'calm';
$moodConfig = getMoodConfig($currentMoodType);

// Get food suggestions for each mood
$moods = getMoodConfig();

include '../../includes/header.php';
?>

<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Food & Drink Ideas</h1>
            <p class="text-slate-500">Mood-boosting foods to nourish your body and mind</p>
        </div>
    </div>
    
    <!-- Current Mood Recommendations -->
    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl"
                 style="background-color: <?php echo $moodConfig['color']; ?>30;">
                <?php echo $moodConfig['emoji']; ?>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-800">
                    Perfect foods for your <?php echo $moodConfig['label']; ?> mood
                </h2>
                <p class="text-slate-600">What you eat affects how you feel!</p>
            </div>
        </div>
        
        <?php 
        $foodSuggestions = getFoodSuggestions($currentMoodType, 4);
        if (!empty($foodSuggestions)):
        ?>
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($foodSuggestions as $food): ?>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">🍽️</span>
                    <div>
                        <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($food['title']); ?></h3>
                        <p class="text-slate-600 text-sm mt-1"><?php echo htmlspecialchars($food['content']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-slate-500">No food suggestions available for this mood. Check back later!</p>
        <?php endif; ?>
    </div>
    
    <!-- Browse by Mood -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Browse Food Ideas by Mood</h2>
        
        <!-- Mood Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ($moods as $type => $config): ?>
            <button onclick="showMoodFoods('<?php echo $type; ?>')"
                    class="food-tab flex items-center gap-2 px-4 py-2 rounded-xl transition-all <?php echo $type === $currentMoodType ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>"
                    data-mood="<?php echo $type; ?>">
                <span><?php echo $config['emoji']; ?></span>
                <span><?php echo $config['label']; ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Foods Container -->
        <?php foreach ($moods as $type => $config): 
            $foods = getFoodSuggestions($type, 6);
        ?>
        <div id="foods-<?php echo $type; ?>" 
             class="mood-foods <?php echo $type === $currentMoodType ? '' : 'hidden'; ?>">
            
            <?php if (!empty($foods)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($foods as $food): ?>
                <div class="border border-slate-200 rounded-xl p-4 hover:border-orange-300 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-orange-100">
                            <span class="text-lg">🍴</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($food['title']); ?></h3>
                            <p class="text-slate-600 text-sm mt-1"><?php echo htmlspecialchars($food['content']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-slate-500">
                <p>No food suggestions available for this mood yet.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Nutrition Tips -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">🥗 Nutrition & Mood Connection</h2>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🥬</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Leafy Greens</h3>
                        <p class="text-sm text-slate-600">Rich in folate, which helps produce serotonin - the "happy hormone"</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🐟</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Fatty Fish</h3>
                        <p class="text-sm text-slate-600">Omega-3 fatty acids support brain health and reduce anxiety</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🫐</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Berries</h3>
                        <p class="text-sm text-slate-600">Antioxidants help reduce inflammation linked to depression</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🍫</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Dark Chocolate</h3>
                        <p class="text-sm text-slate-600">Contains compounds that boost endorphins and serotonin</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🥜</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Nuts & Seeds</h3>
                        <p class="text-sm text-slate-600">Magnesium helps regulate stress hormones</p>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span>🍊</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">Citrus Fruits</h3>
                        <p class="text-sm text-slate-600">Vitamin C helps reduce cortisol and anxiety</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Find Nearby Restaurants -->
    <div class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-2xl p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Hungry?</h2>
                <p class="text-slate-600">Find restaurants and cafes near you</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/pages/places/nearby.php" 
               class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Find Nearby Places
            </a>
        </div>
    </div>
    
</div>

<script>
function showMoodFoods(mood) {
    // Hide all foods
    document.querySelectorAll('.mood-foods').forEach(el => el.classList.add('hidden'));
    
    // Show selected
    document.getElementById('foods-' + mood).classList.remove('hidden');
    
    // Update tabs
    document.querySelectorAll('.food-tab').forEach(tab => {
        if (tab.dataset.mood === mood) {
            tab.classList.remove('bg-slate-100', 'text-slate-700');
            tab.classList.add('bg-primary', 'text-white');
        } else {
            tab.classList.remove('bg-primary', 'text-white');
            tab.classList.add('bg-slate-100', 'text-slate-700');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
