<?php
/**
 * MoodMap - Settings
 * 
 * User preferences and app settings.
 */

$pageTitle = 'Settings';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();

// Get user preferences
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$userId]);
$prefs = $stmt->fetch() ?: [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reminders = isset($_POST['daily_reminders']) ? 1 : 0;
    $reminderTime = sanitize($_POST['reminder_time'] ?? '09:00');
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    
    // Upsert preferences
    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, daily_reminder_enabled, reminder_time, push_notifications)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            daily_reminder_enabled = VALUES(daily_reminder_enabled),
            reminder_time = VALUES(reminder_time),
            push_notifications = VALUES(push_notifications)
    ");
    $stmt->execute([$userId, $reminders, $reminderTime, $notifications]);
    
    // Refresh preferences
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $prefs = $stmt->fetch();
    
    $success = 'Settings saved successfully!';
}

include '../../includes/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Page Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="index.php" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Settings</h1>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-xl text-sm">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-6">
        
        <!-- Notifications -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Notifications</h2>
            
            <div class="space-y-4">
                <label class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 cursor-pointer">
                    <div>
                        <p class="font-medium text-slate-800">Daily Mood Reminders</p>
                        <p class="text-sm text-slate-500">Get reminded to log your mood</p>
                    </div>
                    <input type="checkbox" 
                           name="daily_reminders"
                           <?php echo ($prefs['daily_reminder_enabled'] ?? 0) ? 'checked' : ''; ?>
                           class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                </label>
                
                <div class="px-4">
                    <label for="reminder_time" class="block text-sm font-medium text-slate-700 mb-2">Reminder Time</label>
                    <input type="time" 
                           name="reminder_time" 
                           id="reminder_time"
                           value="<?php echo $prefs['reminder_time'] ?? '09:00'; ?>"
                           class="px-4 py-2 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors">
                </div>
                
                <label class="flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 cursor-pointer">
                    <div>
                        <p class="font-medium text-slate-800">Push Notifications</p>
                        <p class="text-sm text-slate-500">Receive browser notifications</p>
                    </div>
                    <input type="checkbox" 
                           name="notifications"
                           <?php echo ($prefs['push_notifications'] ?? 0) ? 'checked' : ''; ?>
                           class="w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
                </label>
            </div>
        </div>
        
        <!-- App Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">About MoodMap</h2>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-600">Version</span>
                    <span class="font-medium text-slate-800">1.0.0</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-600">Developer</span>
                    <span class="font-medium text-slate-800">Your Name</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-slate-600">Contact</span>
                    <a href="mailto:support@moodmap.com" class="text-primary hover:underline">support@moodmap.com</a>
                </div>
            </div>
        </div>
        
        <!-- Data Management -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Data Management</h2>
            
            <div class="space-y-4">
                <button type="button" onclick="exportData()" 
                        class="w-full flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-medium text-slate-800">Export My Data</p>
                            <p class="text-sm text-slate-500">Download all your mood data</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <button type="button" onclick="clearLocalData()" 
                        class="w-full flex items-center justify-between p-4 rounded-xl hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-medium text-slate-800">Clear Local Data</p>
                            <p class="text-sm text-slate-500">Clear browser cache and stored preferences</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Save Button -->
        <button type="submit" 
                class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors">
            Save Settings
        </button>
        
    </form>
    
    <!-- Logout -->
    <div class="text-center">
        <a href="<?php echo BASE_URL; ?>/pages/auth/logout.php" 
           class="text-red-600 hover:underline font-medium">
            Log Out
        </a>
    </div>
    
</div>

<script>
function exportData() {
    window.location.href = '<?php echo BASE_URL; ?>/api/export.php';
}

function clearLocalData() {
    if (confirm('This will clear all locally stored data. Continue?')) {
        localStorage.clear();
        sessionStorage.clear();
        showToast('Local data cleared!', 'success');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
