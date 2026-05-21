<?php
/**
 * MoodMap - Places API
 * 
 * API endpoint for Google Places integration.
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = getCurrentUserId();

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'search') {
        $type = sanitize($_GET['type'] ?? 'restaurant');
        $lat = (float)($_GET['lat'] ?? 0);
        $lng = (float)($_GET['lng'] ?? 0);
        $radius = (int)($_GET['radius'] ?? 5000);
        
        if ($lat === 0 || $lng === 0) {
            echo json_encode(['success' => false, 'message' => 'Location required']);
            exit;
        }
        
        // Search using Google Places API
        $result = searchNearbyPlaces($lat, $lng, $type, $radius);
        echo json_encode($result);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    if ($action === 'save') {
        $placeId = sanitize($data['place_id'] ?? '');
        $placeName = sanitize($data['place_name'] ?? '');
        $placeType = sanitize($data['place_type'] ?? '');
        
        if (empty($placeId) || empty($placeName)) {
            echo json_encode(['success' => false, 'message' => 'Place ID and name required']);
            exit;
        }
        
        $result = savePlace($userId, $placeId, $placeName, $placeType);
        echo json_encode($result);
        exit;
    }
    
    if ($action === 'remove') {
        $placeId = (int)($data['place_id'] ?? 0);
        
        if ($placeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid place ID']);
            exit;
        }
        
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM saved_places WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$placeId, $userId]);
        
        echo json_encode(['success' => $result]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
