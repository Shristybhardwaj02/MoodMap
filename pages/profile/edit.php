<?php
/**
 * MoodMap - Edit Profile
 * 
 * Update user profile information.
 */

$pageTitle = 'Edit Profile';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate name
    if (empty($name) || strlen($name) < 2) {
        $error = 'Please enter a valid name (at least 2 characters)';
    } else {
        // Update name
        $result = updateUserProfile($userId, ['name' => $name]);
        
        if (!$result['success']) {
            $error = $result['message'];
        } else {
            // Check if password change requested
            if (!empty($currentPassword) || !empty($newPassword)) {
                if (empty($currentPassword)) {
                    $error = 'Please enter your current password';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match';
                } else {
                    $passResult = changePassword($userId, $currentPassword, $newPassword);
                    if (!$passResult['success']) {
                        $error = $passResult['message'];
                    } else {
                        $success = 'Profile and password updated successfully!';
                    }
                }
            } else {
                $success = 'Profile updated successfully!';
            }
        }
        
        // Refresh user data
        $user = getCurrentUser();
    }
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
        <h1 class="text-2xl font-bold text-slate-800">Edit Profile</h1>
    </div>
    
    <?php if ($error): ?>
    <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-xl text-sm">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-6">
        
        <!-- Profile Picture -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Profile Picture</h2>
            
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-3xl text-white font-bold">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Your profile picture is generated from your name's initial.</p>
                </div>
            </div>
        </div>
        
        <!-- Basic Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Basic Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="<?php echo htmlspecialchars($user['name']); ?>"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           required>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                    <input type="email" 
                           id="email"
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-slate-50 cursor-not-allowed"
                           disabled>
                    <p class="text-xs text-slate-400 mt-1">Email cannot be changed</p>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Change Password</h2>
            <p class="text-sm text-slate-500 mb-4">Leave blank if you don't want to change your password</p>
            
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 mb-2">Current Password</label>
                    <input type="password" 
                           name="current_password" 
                           id="current_password"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="Enter current password">
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                    <input type="password" 
                           name="new_password" 
                           id="new_password"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="Enter new password (min 6 characters)">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary focus:ring-0 transition-colors"
                           placeholder="Confirm new password">
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit" 
                    class="flex-1 bg-primary text-white py-3 rounded-xl font-semibold hover:bg-primary/90 transition-colors">
                Save Changes
            </button>
            <a href="index.php" 
               class="px-6 py-3 border-2 border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </a>
        </div>
        
    </form>
    
</div>

<?php include '../../includes/footer.php'; ?>
