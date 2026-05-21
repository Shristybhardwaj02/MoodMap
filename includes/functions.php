<?php
/**
 * MoodMap - Functions File
 * 
 * This file contains all the backend logic functions for MoodMap.
 * Includes user management, mood tracking, places API, and more.
 * 
 * All functions use prepared statements for security.
 */

// Ensure config is loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/config.php';
}

// ==========================================
// USER AUTHENTICATION FUNCTIONS
// ==========================================

/**
 * Register a new user
 * 
 * @param string $name User's full name
 * @param string $email User's email
 * @param string $phone User's phone (optional)
 * @param string $password Plain text password (will be hashed)
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function registerUser($name, $email, $phone, $password) {
    global $pdo;
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered. Please login.'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate OTP
        $otp = generateOTP();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        
        // Insert user (AUTO-VERIFIED for demo - no email required)
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, phone, password, otp, otp_expires, is_verified, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$name, $email, $phone, $hashedPassword, $otp, $otpExpires]);
        
        $userId = $pdo->lastInsertId();
        
        // Create default user preferences
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // Create mood streak record
        $stmt = $pdo->prepare("INSERT INTO mood_streaks (user_id) VALUES (?)");
        $stmt->execute([$userId]);
        
        // NOTE: Email disabled for local demo. Enable sendOTPEmail() for production.
        // sendOTPEmail($email, $name, $otp);
        
        return [
            'success' => true, 
            'message' => 'Registration successful! You can now login.',
            'user_id' => $userId,
            'auto_verified' => true  // Skip OTP for demo
        ];
        
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 * 
 * @param string $email User's email
 * @param string $password Plain text password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        
        if (!$user['is_verified']) {
            // Resend OTP
            $otp = generateOTP();
            $otpExpires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
            
            $stmt = $pdo->prepare("UPDATE users SET otp = ?, otp_expires = ? WHERE id = ?");
            $stmt->execute([$otp, $otpExpires, $user['id']]);
            
            sendOTPEmail($email, $user['name'], $otp);
            
            return [
                'success' => false, 
                'message' => 'Please verify your email first. New OTP sent.',
                'needs_verification' => true,
                'email' => $email
            ];
        }
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        return [
            'success' => true, 
            'message' => 'Login successful!',
            'user' => $user
        ];
        
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Verify OTP
 * 
 * @param string $email User's email
 * @param string $otp OTP to verify
 * @return array ['success' => bool, 'message' => string]
 */
function verifyOTP($email, $otp) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE email = ? AND otp = ? AND otp_expires > NOW()
        ");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.'];
        }
        
        // Update user as verified
        $stmt = $pdo->prepare("
            UPDATE users 
            SET is_verified = 1, otp = NULL, otp_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        return ['success' => true, 'message' => 'Email verified successfully!'];
        
    } catch (PDOException $e) {
        error_log("OTP Verification Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Verification failed. Please try again.'];
    }
}

/**
 * Generate OTP
 * 
 * @return string 6-digit OTP
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
}

/**
 * Send OTP Email
 * 
 * @param string $email Recipient email
 * @param string $name Recipient name
 * @param string $otp The OTP code
 * @return bool Success status
 */
function sendOTPEmail($email, $name, $otp) {
    $subject = "Your MoodMap Verification Code: " . $otp;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f8fafc; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 16px; padding: 30px; }
            .logo { text-align: center; margin-bottom: 20px; }
            .otp-box { background: linear-gradient(135deg, #6366F1, #8B5CF6); color: white; 
                       font-size: 32px; letter-spacing: 8px; text-align: center; 
                       padding: 20px; border-radius: 12px; margin: 20px 0; }
            .footer { text-align: center; color: #64748b; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='logo'>
                <h1 style='color: #6366F1;'>MoodMap</h1>
            </div>
            <p>Hello <strong>{$name}</strong>,</p>
            <p>Your verification code for MoodMap is:</p>
            <div class='otp-box'>{$otp}</div>
            <p>This code will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
            <div class='footer'>
                <p>© " . date('Y') . " MoodMap. Your Mood & Lifestyle Companion.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    
    // Note: For local development, this might not work without proper mail configuration
    // In production, use PHPMailer or a service like SendGrid
    return @mail($email, $subject, $message, $headers);
}

/**
 * Request Password Reset
 * 
 * @param string $email User's email
 * @return array ['success' => bool, 'message' => string]
 */
function requestPasswordReset($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Don't reveal if email exists
            return ['success' => true, 'message' => 'If your email is registered, you will receive a reset link.'];
        }
        
        $token = generateRandomString(64);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // Send reset email
        sendPasswordResetEmail($email, $user['name'], $token);
        
        return ['success' => true, 'message' => 'If your email is registered, you will receive a reset link.'];
        
    } catch (PDOException $e) {
        error_log("Password Reset Request Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Request failed. Please try again.'];
    }
}

/**
 * Send Password Reset Email
 */
function sendPasswordResetEmail($email, $name, $token) {
    $resetLink = APP_URL . "/pages/auth/reset-password.php?token=" . $token;
    
    $subject = "Reset Your MoodMap Password";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f8fafc; padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 16px; padding: 30px; }
            .btn { display: inline-block; background: linear-gradient(135deg, #6366F1, #8B5CF6); 
                   color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1 style='color: #6366F1;'>MoodMap</h1>
            <p>Hello <strong>{$name}</strong>,</p>
            <p>We received a request to reset your password. Click the button below:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' class='btn'>Reset Password</a>
            </p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    
    return @mail($email, $subject, $message, $headers);
}

/**
 * Reset Password
 * 
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array ['success' => bool, 'message' => string]
 */
function resetPassword($token, $newPassword) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE reset_token = ? AND reset_token_expires > NOW()
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired reset link.'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_token_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        return ['success' => true, 'message' => 'Password reset successful! Please login.'];
        
    } catch (PDOException $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Reset failed. Please try again.'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    session_destroy();
}

// ==========================================
// MOOD TRACKING FUNCTIONS
// ==========================================

/**
 * Log a mood entry
 * 
 * @param int $userId User ID
 * @param string $moodType Mood type
 * @param int $intensity Intensity 1-5
 * @param string|null $notes Optional notes
 * @return array ['success' => bool, 'message' => string, 'mood_id' => int|null]
 */
function logMood($userId, $moodType, $intensity, $notes = null) {
    global $pdo;
    
    try {
        // Validate mood type
        if (!in_array($moodType, MOOD_TYPES)) {
            return ['success' => false, 'message' => 'Invalid mood type.'];
        }
        
        // Validate intensity
        $intensity = max(1, min(5, (int)$intensity));
        
        // Insert mood
        $stmt = $pdo->prepare("
            INSERT INTO moods (user_id, mood_type, mood_intensity, notes, logged_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $moodType, $intensity, $notes]);
        
        $moodId = $pdo->lastInsertId();
        
        // Update streak
        updateMoodStreak($userId);
        
        return [
            'success' => true, 
            'message' => 'Mood logged successfully!',
            'mood_id' => $moodId
        ];
        
    } catch (PDOException $e) {
        error_log("Log Mood Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to log mood. Please try again.'];
    }
}

/**
 * Update mood streak for a user
 */
function updateMoodStreak($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mood_streaks WHERE user_id = ?");
        $stmt->execute([$userId]);
        $streak = $stmt->fetch();
        
        $today = date('Y-m-d');
        $lastLogDate = $streak['last_log_date'];
        
        if ($lastLogDate === $today) {
            // Already logged today, just increment total
            $stmt = $pdo->prepare("
                UPDATE mood_streaks 
                SET total_logs = total_logs + 1, updated_at = NOW() 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        } elseif ($lastLogDate === date('Y-m-d', strtotime('-1 day'))) {
            // Consecutive day
            $newStreak = $streak['current_streak'] + 1;
            $longestStreak = max($newStreak, $streak['longest_streak']);
            
            $stmt = $pdo->prepare("
                UPDATE mood_streaks 
                SET current_streak = ?, longest_streak = ?, total_logs = total_logs + 1,
                    last_log_date = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$newStreak, $longestStreak, $today, $userId]);
        } else {
            // Streak broken, start new
            $stmt = $pdo->prepare("
                UPDATE mood_streaks 
                SET current_streak = 1, total_logs = total_logs + 1,
                    last_log_date = ?, streak_start_date = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$today, $today, $userId]);
        }
        
    } catch (PDOException $e) {
        error_log("Update Streak Error: " . $e->getMessage());
    }
}

/**
 * Get user's mood history
 * 
 * @param int $userId User ID
 * @param int $limit Number of records to fetch
 * @param int $offset Offset for pagination
 * @return array Array of mood records
 */
function getMoodHistory($userId, $limit = 30, $offset = 0) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM moods 
            WHERE user_id = ? 
            ORDER BY logged_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get Mood History Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get mood analytics for a period
 * 
 * @param int $userId User ID
 * @param string $period 'week', 'month', or 'year'
 * @return array Analytics data
 */
function getMoodAnalytics($userId, $period = 'week') {
    global $pdo;
    
    try {
        // Determine date range
        switch ($period) {
            case 'month':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'year':
                $startDate = date('Y-m-d', strtotime('-1 year'));
                break;
            default: // week
                $startDate = date('Y-m-d', strtotime('-7 days'));
        }
        
        // Get mood distribution
        $stmt = $pdo->prepare("
            SELECT mood_type, COUNT(*) as count, AVG(mood_intensity) as avg_intensity
            FROM moods 
            WHERE user_id = ? AND logged_at >= ?
            GROUP BY mood_type
            ORDER BY count DESC
        ");
        $stmt->execute([$userId, $startDate]);
        $distribution = $stmt->fetchAll();
        
        // Get daily moods for chart
        $stmt = $pdo->prepare("
            SELECT DATE(logged_at) as date, mood_type, mood_intensity
            FROM moods 
            WHERE user_id = ? AND logged_at >= ?
            ORDER BY logged_at ASC
        ");
        $stmt->execute([$userId, $startDate]);
        $daily = $stmt->fetchAll();
        
        // Get total count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM moods 
            WHERE user_id = ? AND logged_at >= ?
        ");
        $stmt->execute([$userId, $startDate]);
        $total = $stmt->fetch()['total'];
        
        // Get most common mood
        $mostCommon = !empty($distribution) ? $distribution[0]['mood_type'] : null;
        
        return [
            'period' => $period,
            'start_date' => $startDate,
            'total_logs' => $total,
            'distribution' => $distribution,
            'daily' => $daily,
            'most_common_mood' => $mostCommon
        ];
        
    } catch (PDOException $e) {
        error_log("Get Analytics Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get today's mood for user
 */
function getTodayMood($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM moods 
            WHERE user_id = ? AND DATE(logged_at) = CURDATE()
            ORDER BY logged_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Get Today Mood Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user's mood streak info
 */
function getMoodStreak($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM mood_streaks WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Get Streak Error: " . $e->getMessage());
        return null;
    }
}

// ==========================================
// PLACES API FUNCTIONS
// ==========================================

/**
 * Search nearby places based on mood
 * 
 * @param string $moodType Mood type
 * @param float $lat Latitude
 * @param float $lng Longitude
 * @param int $radius Search radius in meters
 * @return array Array of places
 */
function searchNearbyPlaces($moodType, $lat, $lng, $radius = 5000) {
    global $pdo;
    
    // Get place types for this mood
    $stmt = $pdo->prepare("
        SELECT place_type FROM place_recommendations 
        WHERE mood_type = ? 
        ORDER BY priority ASC
    ");
    $stmt->execute([$moodType]);
    $placeTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($placeTypes)) {
        $placeTypes = ['cafe', 'restaurant', 'park']; // Default
    }
    
    $allPlaces = [];
    
    foreach ($placeTypes as $placeType) {
        $places = googlePlacesSearch($lat, $lng, $radius, $placeType);
        if (!empty($places)) {
            $allPlaces = array_merge($allPlaces, $places);
        }
        
        // Limit total places
        if (count($allPlaces) >= DEFAULT_PLACES_LIMIT) {
            break;
        }
    }
    
    // Sort by rating
    usort($allPlaces, function($a, $b) {
        return ($b['rating'] ?? 0) - ($a['rating'] ?? 0);
    });
    
    return array_slice($allPlaces, 0, DEFAULT_PLACES_LIMIT);
}

/**
 * Call Google Places API
 */
function googlePlacesSearch($lat, $lng, $radius, $type) {
    $url = GOOGLE_PLACES_API . "/nearbysearch/json";
    $params = [
        'location' => "$lat,$lng",
        'radius' => $radius,
        'type' => $type,
        'key' => GOOGLE_API_KEY
    ];
    
    $fullUrl = $url . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['results'])) {
        return array_map(function($place) use ($type) {
            return [
                'place_id' => $place['place_id'] ?? '',
                'name' => $place['name'] ?? '',
                'address' => $place['vicinity'] ?? '',
                'rating' => $place['rating'] ?? 0,
                'user_ratings_total' => $place['user_ratings_total'] ?? 0,
                'lat' => $place['geometry']['location']['lat'] ?? 0,
                'lng' => $place['geometry']['location']['lng'] ?? 0,
                'photo_reference' => $place['photos'][0]['photo_reference'] ?? null,
                'open_now' => $place['opening_hours']['open_now'] ?? null,
                'type' => $type,
                'types' => $place['types'] ?? []
            ];
        }, $data['results']);
    }
    
    return [];
}

/**
 * Get place photo URL
 */
function getPlacePhotoUrl($photoReference, $maxWidth = 400) {
    if (empty($photoReference)) {
        return ASSETS_URL . '/images/place-placeholder.jpg';
    }
    
    return GOOGLE_PLACES_API . "/photo?maxwidth={$maxWidth}&photo_reference={$photoReference}&key=" . GOOGLE_API_KEY;
}

/**
 * Get place details from Google
 */
function getPlaceDetails($placeId) {
    $url = GOOGLE_PLACES_API . "/details/json";
    $params = [
        'place_id' => $placeId,
        'fields' => 'name,rating,formatted_phone_number,formatted_address,opening_hours,photos,reviews,website,url,geometry',
        'key' => GOOGLE_API_KEY
    ];
    
    $fullUrl = $url . '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    return $data['result'] ?? null;
}

/**
 * Save a place for user
 */
function savePlace($userId, $placeData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO saved_places 
            (user_id, place_id, place_name, place_type, place_address, place_rating, place_photo, place_lat, place_lng, mood_category)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE saved_at = NOW()
        ");
        
        $stmt->execute([
            $userId,
            $placeData['place_id'],
            $placeData['name'],
            $placeData['type'] ?? null,
            $placeData['address'] ?? null,
            $placeData['rating'] ?? null,
            $placeData['photo_reference'] ?? null,
            $placeData['lat'] ?? null,
            $placeData['lng'] ?? null,
            $placeData['mood_category'] ?? null
        ]);
        
        return ['success' => true, 'message' => 'Place saved!'];
        
    } catch (PDOException $e) {
        error_log("Save Place Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save place.'];
    }
}

/**
 * Get user's saved places
 */
function getSavedPlaces($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM saved_places 
            WHERE user_id = ? 
            ORDER BY saved_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get Saved Places Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Remove saved place
 */
function unsavePlace($userId, $placeId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM saved_places WHERE user_id = ? AND place_id = ?");
        $stmt->execute([$userId, $placeId]);
        
        return ['success' => true, 'message' => 'Place removed from saved.'];
        
    } catch (PDOException $e) {
        error_log("Unsave Place Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to remove place.'];
    }
}

/**
 * Check if place is saved by user
 */
function isPlaceSaved($userId, $placeId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM saved_places WHERE user_id = ? AND place_id = ?");
        $stmt->execute([$userId, $placeId]);
        return $stmt->fetch() !== false;
        
    } catch (PDOException $e) {
        return false;
    }
}

// ==========================================
// WELLNESS TIPS FUNCTIONS
// ==========================================

/**
 * Get wellness tips for a mood
 * 
 * @param string $moodType Mood type or 'all'
 * @param string|null $tipType Filter by tip type
 * @param int $limit Number of tips to fetch
 * @return array Array of tips
 */
function getWellnessTips($moodType, $tipType = null, $limit = 10) {
    global $pdo;
    
    try {
        $query = "
            SELECT * FROM wellness_tips 
            WHERE (mood_type = ? OR mood_type = 'all') AND is_active = 1
        ";
        $params = [$moodType];
        
        if ($tipType) {
            $query .= " AND tip_type = ?";
            $params[] = $tipType;
        }
        
        $query .= " ORDER BY priority DESC, RAND() LIMIT ?";
        $params[] = $limit;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get Wellness Tips Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a random quote for mood
 */
function getRandomQuote($moodType) {
    $tips = getWellnessTips($moodType, 'quote', 1);
    return $tips[0] ?? null;
}

/**
 * Get activities for mood
 */
function getActivities($moodType, $limit = 5) {
    return getWellnessTips($moodType, 'activity', $limit);
}

/**
 * Get food suggestions for mood
 */
function getFoodSuggestions($moodType, $limit = 5) {
    return getWellnessTips($moodType, 'food', $limit);
}

/**
 * Get breathing exercises
 */
function getBreathingExercises($limit = 3) {
    return getWellnessTips('all', 'breathing', $limit);
}

// ==========================================
// USER PROFILE FUNCTIONS
// ==========================================

/**
 * Update user profile
 */
function updateUserProfile($userId, $data) {
    global $pdo;
    
    try {
        $allowedFields = ['name', 'phone', 'location_lat', 'location_lng', 'preferred_radius'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No data to update.'];
        }
        
        $params[] = $userId;
        
        $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?");
        $stmt->execute($params);
        
        // Update session name if changed
        if (isset($data['name'])) {
            $_SESSION['user_name'] = $data['name'];
        }
        
        return ['success' => true, 'message' => 'Profile updated successfully!'];
        
    } catch (PDOException $e) {
        error_log("Update Profile Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update profile.'];
    }
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    global $pdo;
    
    try {
        // Get current password hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        
        // Hash new password and update
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        
        return ['success' => true, 'message' => 'Password changed successfully!'];
        
    } catch (PDOException $e) {
        error_log("Change Password Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to change password.'];
    }
}

/**
 * Update profile picture
 */
function updateProfilePicture($userId, $file) {
    global $pdo;
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Please upload JPG, PNG, GIF, or WebP.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }
    
    try {
        // Create upload directory if needed
        $uploadDir = UPLOADS_PATH . '/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Delete old profile pic
        $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $oldPic = $stmt->fetch()['profile_pic'];
        
        if ($oldPic && file_exists(UPLOADS_PATH . '/profiles/' . $oldPic)) {
            unlink(UPLOADS_PATH . '/profiles/' . $oldPic);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$filename, $userId]);
            
            return ['success' => true, 'message' => 'Profile picture updated!', 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to upload file.'];
        
    } catch (Exception $e) {
        error_log("Update Profile Pic Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update profile picture.'];
    }
}

/**
 * Get user preferences
 */
function getUserPreferences($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
        
    } catch (PDOException $e) {
        error_log("Get Preferences Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Update user preferences
 */
function updateUserPreferences($userId, $preferences) {
    global $pdo;
    
    try {
        $allowedFields = [
            'preferred_place_types', 'avoided_place_types', 'dietary_restrictions',
            'food_preferences', 'activity_preferences', 'notification_enabled',
            'email_notifications', 'dark_mode', 'language'
        ];
        
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($preferences[$field])) {
                $updates[] = "$field = ?";
                $value = $preferences[$field];
                
                // JSON encode arrays
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No data to update.'];
        }
        
        $params[] = $userId;
        
        $stmt = $pdo->prepare("UPDATE user_preferences SET " . implode(', ', $updates) . " WHERE user_id = ?");
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Preferences saved!'];
        
    } catch (PDOException $e) {
        error_log("Update Preferences Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save preferences.'];
    }
}

// ==========================================
// ACTIVITY LOGGING FUNCTIONS
// ==========================================

/**
 * Log an activity after mood
 */
function logMoodActivity($moodId, $userId, $activityType, $activityName, $placeId = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO mood_activities (mood_id, user_id, activity_type, activity_name, place_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$moodId, $userId, $activityType, $activityName, $placeId]);
        
        return ['success' => true, 'message' => 'Activity logged!'];
        
    } catch (PDOException $e) {
        error_log("Log Activity Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to log activity.'];
    }
}

/**
 * Rate how much an activity helped
 */
function rateActivityHelped($activityId, $rating, $notes = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE mood_activities 
            SET helped_rating = ?, feedback_notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$rating, $notes, $activityId]);
        
        return ['success' => true, 'message' => 'Rating saved!'];
        
    } catch (PDOException $e) {
        error_log("Rate Activity Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to save rating.'];
    }
}

// ==========================================
// PLACE RECOMMENDATIONS BY MOOD
// ==========================================

/**
 * Get place types recommended for a mood
 */
function getRecommendedPlaceTypes($moodType) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT place_type, place_type_display 
            FROM place_recommendations 
            WHERE mood_type = ?
            ORDER BY priority ASC
        ");
        $stmt->execute([$moodType]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Get Place Types Error: " . $e->getMessage());
        return [];
    }
}
