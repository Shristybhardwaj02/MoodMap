<?php
/**
 * MoodMap - Logout
 */

require_once '../../includes/config.php';

logoutUser();
redirectWith(BASE_URL . '/pages/auth/login.php', 'You have been logged out successfully.', 'success');
