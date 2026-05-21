<?php
/**
 * MoodMap - Export Data API
 * 
 * Export user's mood data as JSON.
 */

require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit;
}

$userId = getCurrentUserId();
$user = getCurrentUser();

global $pdo;

// Get all user moods
$stmt = $pdo->prepare("SELECT mood_type, mood_intensity, notes, logged_at FROM moods WHERE user_id = ? ORDER BY logged_at DESC");
$stmt->execute([$userId]);
$moods = $stmt->fetchAll();

// Get saved places
$stmt = $pdo->prepare("SELECT place_name, place_type, google_place_id, saved_at FROM saved_places WHERE user_id = ?");
$stmt->execute([$userId]);
$places = $stmt->fetchAll();

// Get preferences
$stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$userId]);
$preferences = $stmt->fetch();

// Build export data
$exportData = [
    'exported_at' => date('Y-m-d H:i:s'),
    'user' => [
        'name' => $user['name'],
        'email' => $user['email'],
        'member_since' => $user['created_at']
    ],
    'moods' => $moods,
    'saved_places' => $places,
    'preferences' => $preferences,
    'statistics' => [
        'total_moods_logged' => count($moods),
        'total_places_saved' => count($places)
    ]
];

// Set headers for download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="moodmap-export-' . date('Y-m-d') . '.json"');

echo json_encode($exportData, JSON_PRETTY_PRINT);
