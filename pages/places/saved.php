<?php
/**
 * MoodMap - Saved Places
 * 
 * View and manage saved places.
 */

$pageTitle = 'Saved Places';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Get saved places
$savedPlaces = getSavedPlaces($userId);

include '../../includes/header.php';
?>

<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Saved Places</h1>
            <p class="text-slate-500">Your favorite spots to visit</p>
        </div>
        
        <a href="nearby.php" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl hover:bg-primary/90 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Find More Places
        </a>
    </div>
    
    <?php if (!empty($savedPlaces)): ?>
    
    <!-- Saved Places Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($savedPlaces as $place): ?>
        <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all" id="place-<?php echo $place['id']; ?>">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">
                        <?php
                        $icons = [
                            'cafe' => '☕',
                            'restaurant' => '🍽️',
                            'park' => '🌳',
                            'spa' => '💆',
                            'gym' => '🏋️',
                            'movie_theater' => '🎬',
                            'shopping_mall' => '🛍️',
                            'art_gallery' => '🎨',
                            'library' => '📚',
                            'bar' => '🍸',
                            'bakery' => '🥐',
                            'default' => '📍'
                        ];
                        echo $icons[$place['place_type']] ?? $icons['default'];
                        ?>
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($place['place_name']); ?></h3>
                    <p class="text-sm text-slate-500 capitalize"><?php echo str_replace('_', ' ', $place['place_type']); ?></p>
                    <p class="text-xs text-slate-400 mt-1">Saved <?php echo timeAgo($place['saved_at']); ?></p>
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($place['place_name']); ?>&query_place_id=<?php echo $place['google_place_id']; ?>" 
                   target="_blank"
                   class="flex-1 text-center bg-primary text-white py-2 rounded-xl text-sm font-medium hover:bg-primary/90 transition-colors">
                    📍 Open in Maps
                </a>
                <button onclick="removePlace(<?php echo $place['id']; ?>)"
                        class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    
    <!-- Empty State -->
    <div class="bg-white rounded-2xl p-12 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-slate-100 rounded-full flex items-center justify-center">
            <span class="text-4xl">🔖</span>
        </div>
        <h3 class="text-xl font-semibold text-slate-800 mb-2">No saved places yet</h3>
        <p class="text-slate-500 mb-6">Discover and save places that match your mood!</p>
        <a href="nearby.php" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Find Places Near Me
        </a>
    </div>
    
    <?php endif; ?>
    
</div>

<script>
function removePlace(placeId) {
    if (!confirm('Remove this place from saved?')) return;
    
    fetch('<?php echo BASE_URL; ?>/api/places.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            place_id: placeId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('place-' + placeId).remove();
            showToast('Place removed', 'success');
        } else {
            showToast('Failed to remove place', 'error');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
