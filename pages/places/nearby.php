<?php
/**
 * MoodMap - Nearby Places
 * Simple, no animations, auto-show places
 */

$pageTitle = 'Places';
require_once '../../includes/config.php';
requireLogin();

$userId = getCurrentUserId();
$todayMood = getTodayMood($userId);
$currentMood = $todayMood ? $todayMood['mood_type'] : 'calm';

// Mood to recommended place types
$moodRecommendations = [
    'happy' => ['cafe', 'restaurant', 'mall', 'cinema', 'club', 'gaming', 'ice_cream'],
    'sad' => ['park', 'garden', 'temple', 'cafe', 'ice_cream', 'library'],
    'anxious' => ['park', 'garden', 'temple', 'library', 'spa', 'cafe'],
    'calm' => ['library', 'museum', 'garden', 'cafe', 'park', 'temple'],
    'energetic' => ['gym', 'sports', 'club', 'gaming', 'mall', 'cinema']
];

// Pre-loaded places data for all cities
$placesData = [
    'Bangalore' => [
        'Koramangala' => [
            ['name' => 'Third Wave Coffee', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '8AM - 11PM', 'desc' => 'Artisanal coffee with cozy vibes', 'lat' => '12.9352', 'lng' => '77.6245'],
            ['name' => 'Truffles', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.6', 'hours' => '11AM - 11PM', 'desc' => 'Famous for burgers and steaks', 'lat' => '12.9347', 'lng' => '77.6205'],
            ['name' => 'Forum Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Popular mall with stores and food court', 'lat' => '12.9340', 'lng' => '77.6106'],
            ['name' => 'Cult Fit', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '6AM - 10PM', 'desc' => 'Modern gym with group classes', 'lat' => '12.9355', 'lng' => '77.6148'],
            ['name' => 'PVR Forum', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Premium multiplex experience', 'lat' => '12.9342', 'lng' => '77.6110'],
            ['name' => 'ISKCON Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.7', 'hours' => '4AM - 9PM', 'desc' => 'Peaceful spiritual retreat', 'lat' => '13.0104', 'lng' => '77.5510'],
            ['name' => 'Koramangala Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.2', 'hours' => '5AM - 9PM', 'desc' => 'Nice park for morning walks', 'lat' => '12.9350', 'lng' => '77.6200'],
            ['name' => 'Corner House', 'type' => 'ice_cream', 'emoji' => '🍦', 'rating' => '4.6', 'hours' => '11AM - 11PM', 'desc' => 'Legendary Death by Chocolate', 'lat' => '12.9345', 'lng' => '77.6180'],
        ],
        'Indiranagar' => [
            ['name' => 'Toit', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '12PM - 1AM', 'desc' => 'Craft brewery with great food', 'lat' => '12.9784', 'lng' => '77.6408'],
            ['name' => 'Dyu Art Cafe', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Art gallery meets coffee shop', 'lat' => '12.9789', 'lng' => '77.6390'],
            ['name' => 'The Humming Tree', 'type' => 'club', 'emoji' => '🎵', 'rating' => '4.4', 'hours' => '6PM - 1AM', 'desc' => 'Live music venue and bar', 'lat' => '12.9792', 'lng' => '77.6402'],
            ['name' => 'Smaaash', 'type' => 'gaming', 'emoji' => '🎮', 'rating' => '4.2', 'hours' => '11AM - 11PM', 'desc' => 'Gaming arcade and bowling', 'lat' => '12.9780', 'lng' => '77.6395'],
            ['name' => 'Corner House', 'type' => 'ice_cream', 'emoji' => '🍦', 'rating' => '4.6', 'hours' => '11AM - 11PM', 'desc' => 'Legendary Death by Chocolate', 'lat' => '12.9795', 'lng' => '77.6415'],
            ['name' => 'Indiranagar Club', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '5AM - 10PM', 'desc' => 'Sports club with pool', 'lat' => '12.9800', 'lng' => '77.6400'],
            ['name' => 'CMH Road Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.1', 'hours' => '5AM - 9PM', 'desc' => 'Quiet neighborhood park', 'lat' => '12.9790', 'lng' => '77.6380'],
        ],
        'MG Road' => [
            ['name' => 'Starbucks', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '8AM - 11PM', 'desc' => 'Classic coffee chain vibes', 'lat' => '12.9757', 'lng' => '77.6068'],
            ['name' => 'Cubbon Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.6', 'hours' => '6AM - 6PM', 'desc' => 'Lush green urban oasis', 'lat' => '12.9763', 'lng' => '77.5929'],
            ['name' => 'Government Museum', 'type' => 'museum', 'emoji' => '🏛️', 'rating' => '4.2', 'hours' => '10AM - 5PM', 'desc' => 'Rich history and artifacts', 'lat' => '12.9706', 'lng' => '77.5946'],
            ['name' => 'UB City Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '11AM - 10PM', 'desc' => 'Luxury shopping destination', 'lat' => '12.9716', 'lng' => '77.5956'],
            ['name' => 'Koshy\'s', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '9AM - 11PM', 'desc' => 'Iconic heritage restaurant', 'lat' => '12.9754', 'lng' => '77.6077'],
            ['name' => 'State Library', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.1', 'hours' => '8AM - 8PM', 'desc' => 'Peaceful reading space', 'lat' => '12.9718', 'lng' => '77.5950'],
            ['name' => 'PVR Cinemas', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.4', 'hours' => '9AM - 12AM', 'desc' => 'Latest movies experience', 'lat' => '12.9720', 'lng' => '77.5960'],
        ],
        'Whitefield' => [
            ['name' => 'Phoenix Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.6', 'hours' => '10AM - 10PM', 'desc' => 'Mega mall with everything', 'lat' => '12.9975', 'lng' => '77.7242'],
            ['name' => 'Blue Tokai', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '8AM - 10PM', 'desc' => 'Premium Indian coffee', 'lat' => '12.9960', 'lng' => '77.7230'],
            ['name' => 'Windmills Craftworks', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '12PM - 12AM', 'desc' => 'Brewery and live music', 'lat' => '12.9968', 'lng' => '77.7250'],
            ['name' => 'KIADB Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.2', 'hours' => '5AM - 7PM', 'desc' => 'Nice jogging park', 'lat' => '12.9970', 'lng' => '77.7200'],
            ['name' => 'Gold\'s Gym', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '5AM - 11PM', 'desc' => 'International gym chain', 'lat' => '12.9965', 'lng' => '77.7235'],
            ['name' => 'Spa Ceylon', 'type' => 'spa', 'emoji' => '🧘', 'rating' => '4.5', 'hours' => '10AM - 9PM', 'desc' => 'Ayurvedic spa treatments', 'lat' => '12.9972', 'lng' => '77.7245'],
            ['name' => 'INOX Cinemas', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Premium movie experience', 'lat' => '12.9973', 'lng' => '77.7240'],
        ],
        'HSR Layout' => [
            ['name' => 'Cafe Coffee Day', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.1', 'hours' => '8AM - 11PM', 'desc' => 'Popular Indian coffee chain', 'lat' => '12.9116', 'lng' => '77.6389'],
            ['name' => 'HSR BDA Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.4', 'hours' => '5AM - 9PM', 'desc' => 'Large park with playground', 'lat' => '12.9120', 'lng' => '77.6400'],
            ['name' => 'Meghana Foods', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '11AM - 11PM', 'desc' => 'Famous biryani spot', 'lat' => '12.9125', 'lng' => '77.6395'],
            ['name' => 'Decathlon', 'type' => 'sports', 'emoji' => '⚽', 'rating' => '4.4', 'hours' => '10AM - 9PM', 'desc' => 'Sports equipment store', 'lat' => '12.9130', 'lng' => '77.6380'],
            ['name' => 'Anytime Fitness', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '24 Hours', 'desc' => '24/7 fitness center', 'lat' => '12.9118', 'lng' => '77.6385'],
            ['name' => 'Baskin Robbins', 'type' => 'ice_cream', 'emoji' => '🍦', 'rating' => '4.3', 'hours' => '11AM - 11PM', 'desc' => 'Classic ice cream flavors', 'lat' => '12.9122', 'lng' => '77.6392'],
        ],
    ],
    'Mumbai' => [
        'Bandra' => [
            ['name' => 'Leopold Cafe', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '7AM - 12AM', 'desc' => 'Iconic heritage cafe', 'lat' => '18.9220', 'lng' => '72.8312'],
            ['name' => 'Bandstand', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.5', 'hours' => '24 Hours', 'desc' => 'Sea-facing promenade', 'lat' => '19.0447', 'lng' => '72.8206'],
            ['name' => 'Linking Road', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Famous shopping street', 'lat' => '19.0595', 'lng' => '72.8345'],
            ['name' => 'Bastian', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.6', 'hours' => '12PM - 1AM', 'desc' => 'Celebrity hotspot restaurant', 'lat' => '19.0540', 'lng' => '72.8270'],
            ['name' => 'Mount Mary Church', 'type' => 'temple', 'emoji' => '⛪', 'rating' => '4.6', 'hours' => '6AM - 9PM', 'desc' => 'Historic basilica', 'lat' => '19.0425', 'lng' => '72.8186'],
            ['name' => 'Toto\'s Garage', 'type' => 'club', 'emoji' => '🎉', 'rating' => '4.3', 'hours' => '7PM - 1:30AM', 'desc' => 'Popular pub with live music', 'lat' => '19.0550', 'lng' => '72.8280'],
            ['name' => 'Naturals Ice Cream', 'type' => 'ice_cream', 'emoji' => '🍦', 'rating' => '4.5', 'hours' => '11AM - 11PM', 'desc' => 'Fresh fruit ice cream', 'lat' => '19.0560', 'lng' => '72.8290'],
        ],
        'Juhu' => [
            ['name' => 'Juhu Beach', 'type' => 'park', 'emoji' => '🏖️', 'rating' => '4.3', 'hours' => '24 Hours', 'desc' => 'Famous Mumbai beach', 'lat' => '19.0988', 'lng' => '72.8267'],
            ['name' => 'ISKCON Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.7', 'hours' => '4AM - 9PM', 'desc' => 'Beautiful Hare Krishna temple', 'lat' => '19.1111', 'lng' => '72.8295'],
            ['name' => 'JW Marriott', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.7', 'hours' => '24 Hours', 'desc' => 'Luxury dining experience', 'lat' => '19.0990', 'lng' => '72.8268'],
            ['name' => 'Prithvi Theatre', 'type' => 'cinema', 'emoji' => '🎭', 'rating' => '4.6', 'hours' => '10AM - 10PM', 'desc' => 'Legendary theatre space', 'lat' => '19.1056', 'lng' => '72.8297'],
            ['name' => 'Otters Club', 'type' => 'gym', 'emoji' => '🏊', 'rating' => '4.4', 'hours' => '6AM - 10PM', 'desc' => 'Swimming and sports club', 'lat' => '19.0995', 'lng' => '72.8275'],
            ['name' => 'Starbucks Reserve', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '8AM - 11PM', 'desc' => 'Premium coffee experience', 'lat' => '19.1000', 'lng' => '72.8280'],
        ],
        'Powai' => [
            ['name' => 'Powai Lake', 'type' => 'park', 'emoji' => '🏞️', 'rating' => '4.4', 'hours' => '24 Hours', 'desc' => 'Scenic lake with crocodiles', 'lat' => '19.1255', 'lng' => '72.9078'],
            ['name' => 'Hiranandani Gardens', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.5', 'hours' => '6AM - 10PM', 'desc' => 'Beautiful planned township', 'lat' => '19.1178', 'lng' => '72.9075'],
            ['name' => 'R City Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Huge shopping mall', 'lat' => '19.0992', 'lng' => '72.9332'],
            ['name' => 'Starbucks', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '8AM - 11PM', 'desc' => 'Coffee with lake view', 'lat' => '19.1185', 'lng' => '72.9085'],
            ['name' => 'Social', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.3', 'hours' => '9AM - 1AM', 'desc' => 'Trendy cafe and workspace', 'lat' => '19.1180', 'lng' => '72.9080'],
            ['name' => 'Talwalkar\'s Gym', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.1', 'hours' => '6AM - 10PM', 'desc' => 'Popular fitness chain', 'lat' => '19.1175', 'lng' => '72.9070'],
            ['name' => 'PVR Cinemas', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.4', 'hours' => '9AM - 12AM', 'desc' => 'Modern multiplex', 'lat' => '19.0995', 'lng' => '72.9335'],
        ],
        'Andheri' => [
            ['name' => 'Infiniti Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Premium shopping mall', 'lat' => '19.1365', 'lng' => '72.8296'],
            ['name' => 'Social', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '9AM - 1AM', 'desc' => 'Cafe meets coworking space', 'lat' => '19.1370', 'lng' => '72.8290'],
            ['name' => 'Fun Republic', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.3', 'hours' => '9AM - 12AM', 'desc' => 'Entertainment complex', 'lat' => '19.1350', 'lng' => '72.8285'],
            ['name' => 'Gilbert Hill', 'type' => 'park', 'emoji' => '⛰️', 'rating' => '4.2', 'hours' => '8AM - 6PM', 'desc' => 'Unique rock formation', 'lat' => '19.1398', 'lng' => '72.8411'],
            ['name' => 'Mamagoto', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '12PM - 11PM', 'desc' => 'Asian cuisine hotspot', 'lat' => '19.1360', 'lng' => '72.8300'],
            ['name' => 'Crossword', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.3', 'hours' => '10AM - 9PM', 'desc' => 'Book store with cafe', 'lat' => '19.1375', 'lng' => '72.8295'],
        ],
    ],
    'Delhi' => [
        'Connaught Place' => [
            ['name' => 'Wenger\'s', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '10AM - 8PM', 'desc' => 'Historic bakery since 1926', 'lat' => '28.6328', 'lng' => '77.2197'],
            ['name' => 'Central Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.2', 'hours' => '5AM - 9PM', 'desc' => 'Green space in heart of CP', 'lat' => '28.6328', 'lng' => '77.2190'],
            ['name' => 'Odeon Cinema', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.3', 'hours' => '10AM - 11PM', 'desc' => 'Classic single screen theatre', 'lat' => '28.6315', 'lng' => '77.2205'],
            ['name' => 'Farzi Cafe', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '12PM - 1AM', 'desc' => 'Modern Indian cuisine', 'lat' => '28.6320', 'lng' => '77.2200'],
            ['name' => 'Janpath Market', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.1', 'hours' => '10AM - 9PM', 'desc' => 'Street shopping paradise', 'lat' => '28.6275', 'lng' => '77.2135'],
            ['name' => 'Hanuman Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.5', 'hours' => '5AM - 11PM', 'desc' => 'Ancient temple at CP', 'lat' => '28.6332', 'lng' => '77.2188'],
            ['name' => 'Keventers', 'type' => 'ice_cream', 'emoji' => '🥤', 'rating' => '4.4', 'hours' => '10AM - 11PM', 'desc' => 'Famous milkshakes since 1925', 'lat' => '28.6325', 'lng' => '77.2195'],
        ],
        'Hauz Khas' => [
            ['name' => 'Hauz Khas Fort', 'type' => 'museum', 'emoji' => '🏰', 'rating' => '4.5', 'hours' => '10AM - 6PM', 'desc' => 'Historic fort with lake', 'lat' => '28.5494', 'lng' => '77.1940'],
            ['name' => 'Deer Park', 'type' => 'park', 'emoji' => '🦌', 'rating' => '4.6', 'hours' => '5AM - 8PM', 'desc' => 'Park with actual deer', 'lat' => '28.5520', 'lng' => '77.1965'],
            ['name' => 'Social', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '10AM - 1AM', 'desc' => 'Popular hangout spot', 'lat' => '28.5510', 'lng' => '77.1930'],
            ['name' => 'DLF Place', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Premium shopping mall', 'lat' => '28.5350', 'lng' => '77.2057'],
            ['name' => 'Yeti', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '11AM - 11PM', 'desc' => 'Himalayan cuisine', 'lat' => '28.5495', 'lng' => '77.1935'],
            ['name' => 'Imperfecto', 'type' => 'club', 'emoji' => '🎉', 'rating' => '4.2', 'hours' => '5PM - 1AM', 'desc' => 'Rustic rooftop bar', 'lat' => '28.5498', 'lng' => '77.1932'],
        ],
        'Saket' => [
            ['name' => 'Select City Walk', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.6', 'hours' => '10AM - 10PM', 'desc' => 'Best mall in Delhi', 'lat' => '28.5289', 'lng' => '77.2190'],
            ['name' => 'PVR Director\'s Cut', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.7', 'hours' => '10AM - 12AM', 'desc' => 'Luxury cinema experience', 'lat' => '28.5291', 'lng' => '77.2185'],
            ['name' => 'Garden of Five Senses', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.4', 'hours' => '9AM - 6PM', 'desc' => 'Beautiful themed garden', 'lat' => '28.5132', 'lng' => '77.1926'],
            ['name' => 'Starbucks Reserve', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '9AM - 11PM', 'desc' => 'Premium Starbucks outlet', 'lat' => '28.5288', 'lng' => '77.2188'],
            ['name' => 'Qutub Minar', 'type' => 'museum', 'emoji' => '🗼', 'rating' => '4.6', 'hours' => '7AM - 5PM', 'desc' => 'UNESCO World Heritage', 'lat' => '28.5245', 'lng' => '77.1855'],
            ['name' => 'Gold\'s Gym', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '6AM - 10PM', 'desc' => 'Premium fitness center', 'lat' => '28.5285', 'lng' => '77.2180'],
        ],
        'Khan Market' => [
            ['name' => 'Big Chill', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '12PM - 11PM', 'desc' => 'Famous for pastas', 'lat' => '28.6003', 'lng' => '77.2272'],
            ['name' => 'Bahrisons Books', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.6', 'hours' => '10AM - 8PM', 'desc' => 'Iconic bookstore', 'lat' => '28.6005', 'lng' => '77.2275'],
            ['name' => 'Khan Market', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 9PM', 'desc' => 'Premium shopping area', 'lat' => '28.6002', 'lng' => '77.2270'],
            ['name' => 'Perch', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '9AM - 10PM', 'desc' => 'Upscale coffee house', 'lat' => '28.6000', 'lng' => '77.2268'],
            ['name' => 'Lodhi Garden', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.7', 'hours' => '6AM - 8PM', 'desc' => 'Historic garden with tombs', 'lat' => '28.5931', 'lng' => '77.2197'],
            ['name' => 'O2 Spa', 'type' => 'spa', 'emoji' => '🧘', 'rating' => '4.4', 'hours' => '10AM - 9PM', 'desc' => 'Relaxing spa treatments', 'lat' => '28.6008', 'lng' => '77.2280'],
        ],
    ],
    'Pune' => [
        'Koregaon Park' => [
            ['name' => 'German Bakery', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '7AM - 11PM', 'desc' => 'Famous relaxed cafe', 'lat' => '18.5362', 'lng' => '73.8939'],
            ['name' => 'Osho Ashram', 'type' => 'spa', 'emoji' => '🧘', 'rating' => '4.5', 'hours' => '6AM - 9PM', 'desc' => 'Meditation resort', 'lat' => '18.5350', 'lng' => '73.8950'],
            ['name' => 'ABC Farms', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.3', 'hours' => '11AM - 11PM', 'desc' => 'Garden restaurant', 'lat' => '18.5400', 'lng' => '73.8960'],
            ['name' => 'Aga Khan Palace', 'type' => 'museum', 'emoji' => '🏰', 'rating' => '4.5', 'hours' => '9AM - 5PM', 'desc' => 'Historic palace museum', 'lat' => '18.5521', 'lng' => '73.9014'],
            ['name' => 'Phoenix Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Massive shopping mall', 'lat' => '18.5625', 'lng' => '73.9165'],
            ['name' => 'Swig Bar', 'type' => 'club', 'emoji' => '🎉', 'rating' => '4.2', 'hours' => '6PM - 1AM', 'desc' => 'Popular nightlife spot', 'lat' => '18.5365', 'lng' => '73.8935'],
            ['name' => 'Naturals Ice Cream', 'type' => 'ice_cream', 'emoji' => '🍦', 'rating' => '4.5', 'hours' => '11AM - 11PM', 'desc' => 'Fruit-based ice cream', 'lat' => '18.5370', 'lng' => '73.8940'],
        ],
        'FC Road' => [
            ['name' => 'Vaishali', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '7AM - 11PM', 'desc' => 'Legendary South Indian', 'lat' => '18.5196', 'lng' => '73.8413'],
            ['name' => 'Ferguson College', 'type' => 'garden', 'emoji' => '🏛️', 'rating' => '4.4', 'hours' => '8AM - 5PM', 'desc' => 'Beautiful campus', 'lat' => '18.5188', 'lng' => '73.8400'],
            ['name' => 'Goodluck Cafe', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '7AM - 11PM', 'desc' => 'Famous bun maska', 'lat' => '18.5180', 'lng' => '73.8410'],
            ['name' => 'Crossword', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.2', 'hours' => '10AM - 9PM', 'desc' => 'Bookstore and cafe', 'lat' => '18.5190', 'lng' => '73.8415'],
            ['name' => 'E-Square', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.2', 'hours' => '9AM - 12AM', 'desc' => 'Multiplex with gaming', 'lat' => '18.5182', 'lng' => '73.8405'],
            ['name' => 'Cult Fit', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.4', 'hours' => '6AM - 10PM', 'desc' => 'Modern fitness studio', 'lat' => '18.5175', 'lng' => '73.8420'],
        ],
        'Viman Nagar' => [
            ['name' => 'Phoenix Marketcity', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Huge shopping complex', 'lat' => '18.5625', 'lng' => '73.9165'],
            ['name' => 'Starbucks', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '8AM - 11PM', 'desc' => 'Premium coffee chain', 'lat' => '18.5630', 'lng' => '73.9170'],
            ['name' => 'PVR Cinemas', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Modern multiplex', 'lat' => '18.5620', 'lng' => '73.9160'],
            ['name' => 'Viman Nagar Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.2', 'hours' => '5AM - 9PM', 'desc' => 'Neighborhood park', 'lat' => '18.5640', 'lng' => '73.9180'],
            ['name' => 'Barbecue Nation', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '12PM - 11PM', 'desc' => 'Live grill experience', 'lat' => '18.5635', 'lng' => '73.9175'],
            ['name' => 'Anytime Fitness', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '24 Hours', 'desc' => '24/7 fitness center', 'lat' => '18.5628', 'lng' => '73.9168'],
        ],
    ],
    'Hyderabad' => [
        'Banjara Hills' => [
            ['name' => 'GVK One Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Premium shopping destination', 'lat' => '17.4239', 'lng' => '78.4474'],
            ['name' => 'Olive Bistro', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '12PM - 12AM', 'desc' => 'Rooftop Mediterranean', 'lat' => '17.4115', 'lng' => '78.4470'],
            ['name' => 'KBR Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.6', 'hours' => '5AM - 10AM, 4PM - 7PM', 'desc' => 'Nature park for jogging', 'lat' => '17.4205', 'lng' => '78.4330'],
            ['name' => 'Cult Fit', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.4', 'hours' => '6AM - 10PM', 'desc' => 'Modern fitness center', 'lat' => '17.4250', 'lng' => '78.4485'],
            ['name' => 'Blue Fox', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '9AM - 11PM', 'desc' => 'Cozy cafe experience', 'lat' => '17.4245', 'lng' => '78.4480'],
            ['name' => 'Birla Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.7', 'hours' => '7AM - 12PM, 3PM - 9PM', 'desc' => 'Beautiful hilltop temple', 'lat' => '17.4060', 'lng' => '78.4691'],
        ],
        'Jubilee Hills' => [
            ['name' => 'Peddamma Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.6', 'hours' => '6AM - 9PM', 'desc' => 'Famous local temple', 'lat' => '17.4300', 'lng' => '78.4100'],
            ['name' => 'Concu', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '11AM - 11PM', 'desc' => 'Trendy cafe and bistro', 'lat' => '17.4320', 'lng' => '78.4115'],
            ['name' => 'Prasads IMAX', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Largest IMAX in India', 'lat' => '17.4121', 'lng' => '78.4557'],
            ['name' => 'City Center Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Popular family mall', 'lat' => '17.4310', 'lng' => '78.4110'],
            ['name' => 'Heart Cup Coffee', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Instagrammable cafe', 'lat' => '17.4330', 'lng' => '78.4120'],
            ['name' => 'F45 Training', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.4', 'hours' => '5AM - 9PM', 'desc' => 'Functional fitness studio', 'lat' => '17.4325', 'lng' => '78.4125'],
        ],
        'Hitech City' => [
            ['name' => 'Inorbit Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'IT hub shopping center', 'lat' => '17.4370', 'lng' => '78.3830'],
            ['name' => 'Shilparamam', 'type' => 'museum', 'emoji' => '🎨', 'rating' => '4.3', 'hours' => '10AM - 8PM', 'desc' => 'Arts and crafts village', 'lat' => '17.4528', 'lng' => '78.3814'],
            ['name' => 'Mindspace Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.2', 'hours' => '6AM - 8PM', 'desc' => 'Green space in IT hub', 'lat' => '17.4380', 'lng' => '78.3850'],
            ['name' => 'Ohri\'s Jiva', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.3', 'hours' => '11AM - 11PM', 'desc' => 'Multi-cuisine restaurant', 'lat' => '17.4390', 'lng' => '78.3840'],
            ['name' => 'Starbucks', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '8AM - 11PM', 'desc' => 'Coffee break spot', 'lat' => '17.4375', 'lng' => '78.3835'],
            ['name' => 'PVR', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.4', 'hours' => '9AM - 12AM', 'desc' => 'Modern multiplex', 'lat' => '17.4372', 'lng' => '78.3832'],
        ],
    ],
    'Chennai' => [
        'Anna Nagar' => [
            ['name' => 'VR Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Modern shopping complex', 'lat' => '13.0850', 'lng' => '80.2101'],
            ['name' => 'Anna Tower Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.3', 'hours' => '5AM - 9PM', 'desc' => 'Popular morning walk spot', 'lat' => '13.0870', 'lng' => '80.2090'],
            ['name' => 'Saravana Bhavan', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '6AM - 11PM', 'desc' => 'Iconic South Indian chain', 'lat' => '13.0855', 'lng' => '80.2095'],
            ['name' => 'Cafe Coffee Day', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.1', 'hours' => '9AM - 11PM', 'desc' => 'Popular coffee chain', 'lat' => '13.0860', 'lng' => '80.2100'],
            ['name' => 'Sathyam Cinemas', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.5', 'hours' => '9AM - 12AM', 'desc' => 'Best multiplex in city', 'lat' => '13.0569', 'lng' => '80.2499'],
            ['name' => 'Talwalkar\'s', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.2', 'hours' => '5AM - 10PM', 'desc' => 'Established gym chain', 'lat' => '13.0865', 'lng' => '80.2105'],
        ],
        'T Nagar' => [
            ['name' => 'Saravana Stores', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '9AM - 10PM', 'desc' => 'Massive department store', 'lat' => '13.0418', 'lng' => '80.2341'],
            ['name' => 'Pondy Bazaar', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.2', 'hours' => '10AM - 10PM', 'desc' => 'Famous shopping street', 'lat' => '13.0448', 'lng' => '80.2379'],
            ['name' => 'Murugan Idli', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '7AM - 10PM', 'desc' => 'Best idlis in Chennai', 'lat' => '13.0425', 'lng' => '80.2350'],
            ['name' => 'Kapaleeshwarar Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.7', 'hours' => '6AM - 12PM, 4PM - 9PM', 'desc' => 'Ancient Shiva temple', 'lat' => '13.0337', 'lng' => '80.2694'],
            ['name' => 'Amethyst', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '10AM - 11PM', 'desc' => 'Beautiful garden cafe', 'lat' => '13.0605', 'lng' => '80.2665'],
            ['name' => 'Higginbothams', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.4', 'hours' => '9AM - 8PM', 'desc' => 'Historic bookstore', 'lat' => '13.0655', 'lng' => '80.2728'],
        ],
    ],
    'Kolkata' => [
        'Park Street' => [
            ['name' => 'Flury\'s', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '7AM - 10PM', 'desc' => 'Legendary tea room since 1927', 'lat' => '22.5530', 'lng' => '88.3520'],
            ['name' => 'Peter Cat', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '12PM - 11PM', 'desc' => 'Famous for Chelo Kebab', 'lat' => '22.5535', 'lng' => '88.3515'],
            ['name' => 'Park Street Cemetery', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.2', 'hours' => '10AM - 5PM', 'desc' => 'Historic colonial cemetery', 'lat' => '22.5515', 'lng' => '88.3548'],
            ['name' => 'Quest Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Premium shopping mall', 'lat' => '22.5395', 'lng' => '88.3510'],
            ['name' => 'Someplace Else', 'type' => 'club', 'emoji' => '🎉', 'rating' => '4.4', 'hours' => '7PM - 1AM', 'desc' => 'Iconic live music venue', 'lat' => '22.5540', 'lng' => '88.3502'],
            ['name' => 'Oxford Bookstore', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.5', 'hours' => '10AM - 9PM', 'desc' => 'Historic bookstore with cafe', 'lat' => '22.5532', 'lng' => '88.3512'],
        ],
        'Salt Lake' => [
            ['name' => 'City Centre Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Massive shopping complex', 'lat' => '22.5758', 'lng' => '88.4256'],
            ['name' => 'Central Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.5', 'hours' => '5AM - 8PM', 'desc' => 'Beautiful urban park', 'lat' => '22.5790', 'lng' => '88.4180'],
            ['name' => 'Cafe Ekante', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Lakeside cafe experience', 'lat' => '22.5765', 'lng' => '88.4200'],
            ['name' => 'Nicco Park', 'type' => 'gaming', 'emoji' => '🎢', 'rating' => '4.4', 'hours' => '10AM - 8PM', 'desc' => 'Amusement park fun', 'lat' => '22.6015', 'lng' => '88.4008'],
            ['name' => 'Science City', 'type' => 'museum', 'emoji' => '🏛️', 'rating' => '4.5', 'hours' => '9AM - 8PM', 'desc' => 'Science museum and planetarium', 'lat' => '22.5402', 'lng' => '88.3969'],
            ['name' => 'Birla Mandir', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.6', 'hours' => '5AM - 11AM, 4PM - 9PM', 'desc' => 'Beautiful marble temple', 'lat' => '22.5181', 'lng' => '88.3655'],
        ],
    ],
    'Ahmedabad' => [
        'CG Road' => [
            ['name' => 'Honest Restaurant', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '11AM - 11PM', 'desc' => 'Famous Gujarati food', 'lat' => '23.0300', 'lng' => '72.5560'],
            ['name' => 'Iscon Mega Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Popular shopping mall', 'lat' => '23.0285', 'lng' => '72.5070'],
            ['name' => 'Cafe Coffee Day', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.1', 'hours' => '9AM - 11PM', 'desc' => 'Coffee chain outlet', 'lat' => '23.0310', 'lng' => '72.5565'],
            ['name' => 'Law Garden', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.5', 'hours' => '4PM - 11PM', 'desc' => 'Night market and garden', 'lat' => '23.0245', 'lng' => '72.5615'],
            ['name' => 'PVR', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.3', 'hours' => '9AM - 12AM', 'desc' => 'Modern multiplex', 'lat' => '23.0295', 'lng' => '72.5555'],
            ['name' => 'Gold\'s Gym', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.2', 'hours' => '6AM - 10PM', 'desc' => 'Premium fitness center', 'lat' => '23.0305', 'lng' => '72.5570'],
        ],
        'SG Highway' => [
            ['name' => 'Ahmedabad One Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Luxurious shopping mall', 'lat' => '23.0440', 'lng' => '72.5120'],
            ['name' => 'Starbucks', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.3', 'hours' => '8AM - 11PM', 'desc' => 'Premium coffee experience', 'lat' => '23.0445', 'lng' => '72.5125'],
            ['name' => 'Club O7', 'type' => 'club', 'emoji' => '🎉', 'rating' => '4.2', 'hours' => '7PM - 2AM', 'desc' => 'Popular resort and club', 'lat' => '23.0750', 'lng' => '72.4950'],
            ['name' => 'ISKCON Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.7', 'hours' => '4AM - 9PM', 'desc' => 'Peaceful spiritual retreat', 'lat' => '23.0490', 'lng' => '72.5145'],
            ['name' => 'Riverside Garden', 'type' => 'garden', 'emoji' => '🌺', 'rating' => '4.4', 'hours' => '6AM - 9PM', 'desc' => 'Beautiful riverfront park', 'lat' => '23.0420', 'lng' => '72.5110'],
            ['name' => 'Barbecue Nation', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.4', 'hours' => '12PM - 11PM', 'desc' => 'Famous for live grill', 'lat' => '23.0450', 'lng' => '72.5130'],
        ],
    ],
    'Jaipur' => [
        'MI Road' => [
            ['name' => 'Laxmi Misthan Bhandar', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.6', 'hours' => '8AM - 11PM', 'desc' => 'Famous for ghewar sweets', 'lat' => '26.9157', 'lng' => '75.8000'],
            ['name' => 'Raj Mandir Cinema', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.7', 'hours' => '9AM - 11PM', 'desc' => 'Iconic meringue-shaped theatre', 'lat' => '26.9063', 'lng' => '75.8060'],
            ['name' => 'Rajasthan Library', 'type' => 'library', 'emoji' => '📚', 'rating' => '4.2', 'hours' => '10AM - 6PM', 'desc' => 'Historic public library', 'lat' => '26.9165', 'lng' => '75.8010'],
            ['name' => 'Central Park', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.4', 'hours' => '5AM - 9PM', 'desc' => 'Large park with running track', 'lat' => '26.9115', 'lng' => '75.7928'],
            ['name' => 'Tapri Central', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.5', 'hours' => '8AM - 11PM', 'desc' => 'Rooftop chai experience', 'lat' => '26.9170', 'lng' => '75.8015'],
            ['name' => 'Metro Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.2', 'hours' => '10AM - 10PM', 'desc' => 'Central shopping destination', 'lat' => '26.9158', 'lng' => '75.8005'],
        ],
        'C Scheme' => [
            ['name' => 'Chokhi Dhani', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.5', 'hours' => '5PM - 11PM', 'desc' => 'Rajasthani village theme', 'lat' => '26.8190', 'lng' => '75.7390'],
            ['name' => 'Crystal Palm Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.3', 'hours' => '10AM - 10PM', 'desc' => 'Modern shopping complex', 'lat' => '26.9060', 'lng' => '75.7965'],
            ['name' => 'Statue Circle', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.1', 'hours' => '24 Hours', 'desc' => 'Historic landmark area', 'lat' => '26.9008', 'lng' => '75.8020'],
            ['name' => 'Cafe Lazy Mojo', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '10AM - 11PM', 'desc' => 'Quirky themed cafe', 'lat' => '26.9050', 'lng' => '75.7970'],
            ['name' => 'Birla Temple', 'type' => 'temple', 'emoji' => '🛕', 'rating' => '4.6', 'hours' => '8AM - 12PM, 3PM - 9PM', 'desc' => 'Beautiful marble temple', 'lat' => '26.8926', 'lng' => '75.8145'],
            ['name' => 'Gold\'s Gym', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '6AM - 10PM', 'desc' => 'Popular fitness chain', 'lat' => '26.9055', 'lng' => '75.7975'],
        ],
    ],
    'Lucknow' => [
        'Hazratganj' => [
            ['name' => 'Royal Cafe', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '10AM - 11PM', 'desc' => 'Famous basket chaat', 'lat' => '26.8520', 'lng' => '80.9470'],
            ['name' => 'Tunday Kababi', 'type' => 'restaurant', 'emoji' => '🍽️', 'rating' => '4.7', 'hours' => '12PM - 11PM', 'desc' => 'Legendary kebabs since 1905', 'lat' => '26.8600', 'lng' => '80.9150'],
            ['name' => 'Sahara Ganj Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.4', 'hours' => '10AM - 10PM', 'desc' => 'Central mall in Hazratganj', 'lat' => '26.8515', 'lng' => '80.9465'],
            ['name' => 'Residency', 'type' => 'museum', 'emoji' => '🏛️', 'rating' => '4.3', 'hours' => '10AM - 5PM', 'desc' => 'Historic British ruins', 'lat' => '26.8580', 'lng' => '80.9300'],
            ['name' => 'Hazratganj Market', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.2', 'hours' => '10AM - 10PM', 'desc' => 'Famous shopping street', 'lat' => '26.8525', 'lng' => '80.9475'],
            ['name' => 'PVR', 'type' => 'cinema', 'emoji' => '🎬', 'rating' => '4.3', 'hours' => '9AM - 12AM', 'desc' => 'Modern multiplex', 'lat' => '26.8530', 'lng' => '80.9480'],
        ],
        'Gomti Nagar' => [
            ['name' => 'Lulu Mall', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.6', 'hours' => '10AM - 10PM', 'desc' => 'Largest mall in UP', 'lat' => '26.8580', 'lng' => '81.0050'],
            ['name' => 'Gomti Riverfront', 'type' => 'park', 'emoji' => '🌳', 'rating' => '4.5', 'hours' => '5AM - 10PM', 'desc' => 'Beautiful riverfront park', 'lat' => '26.8450', 'lng' => '80.9850'],
            ['name' => 'Theobroma', 'type' => 'cafe', 'emoji' => '☕', 'rating' => '4.4', 'hours' => '10AM - 11PM', 'desc' => 'Famous brownies and desserts', 'lat' => '26.8590', 'lng' => '81.0055'],
            ['name' => 'Phoenix Palassio', 'type' => 'mall', 'emoji' => '🛍️', 'rating' => '4.5', 'hours' => '10AM - 10PM', 'desc' => 'Premium shopping experience', 'lat' => '26.8550', 'lng' => '80.9980'],
            ['name' => 'Fun Republic', 'type' => 'gaming', 'emoji' => '🎮', 'rating' => '4.2', 'hours' => '11AM - 11PM', 'desc' => 'Entertainment complex', 'lat' => '26.8560', 'lng' => '80.9960'],
            ['name' => 'Anytime Fitness', 'type' => 'gym', 'emoji' => '💪', 'rating' => '4.3', 'hours' => '24 Hours', 'desc' => '24/7 fitness center', 'lat' => '26.8585', 'lng' => '81.0060'],
        ],
    ],
];

include '../../includes/header.php';
?>

<style>
    .place-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .place-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .mood-chip.active {
        transform: scale(1.02);
    }
</style>

<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="text-center text-white py-4">
        <h1 class="text-2xl font-black mb-2">📍 Discover Places</h1>
        <p class="text-white/80 font-medium">
            Recommendations for 
            <?php echo getMoodConfig($currentMood)['emoji']; ?> 
            <span class="capitalize"><?php echo $currentMood; ?></span> mood
        </p>
    </div>
    
    <!-- Location Selection Card -->
    <div class="glass-card rounded-3xl p-6 space-y-5">
        <h2 class="font-black text-slate-800 text-lg flex items-center gap-2">
            🗺️ Choose Location
        </h2>
        
        <!-- City Selection -->
        <div>
            <label class="font-bold text-slate-600 text-sm mb-2 block">City</label>
            <select id="city" class="w-full border-2 border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-primary/20 focus:border-primary outline-none font-medium text-slate-700" onchange="updateAreas()">
                <option value="">Select your city...</option>
                <?php foreach (array_keys($placesData) as $city): ?>
                <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Area Selection -->
        <div id="areaContainer" class="hidden">
            <label class="font-bold text-slate-600 text-sm mb-2 block">Area / Locality</label>
            <select id="area" class="w-full border-2 border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-primary/20 focus:border-primary outline-none font-medium text-slate-700" onchange="showPlaces()">
                <option value="">Select area...</option>
            </select>
        </div>
    </div>
    
    <!-- Mood Filter -->
    <div class="glass-card rounded-3xl p-5">
        <p class="font-bold text-slate-600 text-sm mb-3">Your Mood</p>
        <div class="flex gap-2 flex-wrap">
            <?php foreach (MOOD_CONFIG as $mood => $config): ?>
            <button onclick="changeMood('<?php echo $mood; ?>')" 
                    class="mood-chip flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-bold transition-all <?php echo $mood === $currentMood ? 'active bg-gradient-to-r from-primary to-secondary text-white' : 'bg-white text-slate-600 border-2 border-slate-200'; ?>" data-mood="<?php echo $mood; ?>">
                <span class="text-lg"><?php echo $config['emoji']; ?></span>
                <span class="capitalize"><?php echo $mood; ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Recommended Places Section -->
    <div id="recommendedSection" class="hidden">
        <h2 class="font-black text-white text-lg mb-3 flex items-center gap-2">
            ✨ Recommended for You
        </h2>
        <div id="recommendedPlaces" class="space-y-3">
            <!-- Recommended places will appear here -->
        </div>
    </div>
    
    <!-- All Places Section -->
    <div id="allPlacesSection" class="hidden">
        <h2 class="font-black text-white text-lg mb-3 flex items-center gap-2">
            📍 All Places in <span id="areaName"></span>
        </h2>
        <div id="allPlaces" class="space-y-3">
            <!-- All places will appear here -->
        </div>
    </div>

</div>
</main>

<script>
// All data from PHP
const placesData = <?php echo json_encode($placesData); ?>;
const moodRecommendations = <?php echo json_encode($moodRecommendations); ?>;
let currentMood = '<?php echo $currentMood; ?>';

// Update areas dropdown when city changes
function updateAreas() {
    const city = document.getElementById('city').value;
    const areaContainer = document.getElementById('areaContainer');
    const areaSelect = document.getElementById('area');
    
    // Hide results when city changes
    document.getElementById('recommendedSection').classList.add('hidden');
    document.getElementById('allPlacesSection').classList.add('hidden');
    
    if (city && placesData[city]) {
        areaContainer.classList.remove('hidden');
        areaSelect.innerHTML = '<option value="">Select area...</option>';
        
        Object.keys(placesData[city]).forEach(area => {
            areaSelect.innerHTML += `<option value="${area}">${area}</option>`;
        });
    } else {
        areaContainer.classList.add('hidden');
    }
}

// Show places when area is selected - AUTO triggered
function showPlaces() {
    const city = document.getElementById('city').value;
    const area = document.getElementById('area').value;
    
    if (!city || !area || !placesData[city] || !placesData[city][area]) {
        document.getElementById('recommendedSection').classList.add('hidden');
        document.getElementById('allPlacesSection').classList.add('hidden');
        return;
    }
    
    const allPlaces = placesData[city][area] || [];
    const moodTypes = moodRecommendations[currentMood] || [];
    
    // Filter recommended places based on mood
    const recommended = allPlaces.filter(p => moodTypes.includes(p.type));
    
    // Show recommended section
    const recSection = document.getElementById('recommendedSection');
    const recContainer = document.getElementById('recommendedPlaces');
    
    if (recommended.length > 0) {
        recSection.classList.remove('hidden');
        recContainer.innerHTML = recommended.map(p => createPlaceCard(p, true)).join('');
    } else {
        recSection.classList.add('hidden');
    }
    
    // Show all places
    const allSection = document.getElementById('allPlacesSection');
    const allContainer = document.getElementById('allPlaces');
    document.getElementById('areaName').textContent = area;
    
    allSection.classList.remove('hidden');
    allContainer.innerHTML = allPlaces.map(p => createPlaceCard(p, false)).join('');
}

// Create place card HTML - simple button, no animation
function createPlaceCard(place, isRecommended) {
    const gradients = {
        cafe: 'from-amber-400 to-orange-500',
        restaurant: 'from-red-400 to-pink-500',
        mall: 'from-purple-400 to-indigo-500',
        park: 'from-green-400 to-teal-500',
        garden: 'from-lime-400 to-green-500',
        temple: 'from-yellow-400 to-amber-500',
        gym: 'from-red-500 to-orange-500',
        cinema: 'from-blue-400 to-purple-500',
        club: 'from-pink-500 to-red-500',
        gaming: 'from-cyan-400 to-blue-500',
        library: 'from-emerald-400 to-teal-500',
        museum: 'from-slate-400 to-slate-600',
        ice_cream: 'from-pink-300 to-purple-400',
        spa: 'from-purple-300 to-pink-400',
        sports: 'from-orange-400 to-red-500',
    };
    
    const gradient = gradients[place.type] || 'from-primary to-secondary';
    const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${place.lat},${place.lng}`;
    
    return `
        <div class="place-card glass-card rounded-2xl p-5">
            <!-- Header -->
            <div class="flex items-start gap-4 mb-3">
                <div class="w-14 h-14 bg-gradient-to-br ${gradient} rounded-xl flex items-center justify-center text-2xl text-white shadow-lg flex-shrink-0">
                    ${place.emoji}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-black text-slate-800">${place.name}</p>
                        ${isRecommended ? '<span class="bg-gradient-to-r from-primary to-purple-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">FOR YOU ✨</span>' : ''}
                    </div>
                    <p class="text-sm text-slate-500 capitalize">${place.type.replace('_', ' ')}</p>
                </div>
                <div class="flex items-center gap-1 bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg text-sm font-bold flex-shrink-0">
                    ⭐ ${place.rating}
                </div>
            </div>
            
            <!-- Description -->
            <p class="text-sm text-slate-600 mb-3">"${place.desc}"</p>
            
            <!-- Hours + Map Button Row -->
            <div class="flex items-center justify-between">
                <span class="bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full text-xs font-bold">
                    🕐 ${place.hours}
                </span>
                
                <!-- Simple Minimalist Map Button -->
                <a href="${directionsUrl}" target="_blank" rel="noopener noreferrer" 
                   class="w-9 h-9 flex items-center justify-center rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors" title="Directions">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </a>
            </div>
        </div>
    `;
}

// Change mood and refresh places
function changeMood(mood) {
    currentMood = mood;
    
    // Update mood chips UI
    document.querySelectorAll('.mood-chip').forEach(btn => {
        if (btn.dataset.mood === mood) {
            btn.className = 'mood-chip active flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-bold transition-all bg-gradient-to-r from-primary to-secondary text-white';
        } else {
            btn.className = 'mood-chip flex items-center gap-2 px-4 py-2.5 rounded-full text-sm font-bold transition-all bg-white text-slate-600 border-2 border-slate-200';
        }
    });
    
    // Update background
    const bg = document.getElementById('moodBg');
    const illustration = document.getElementById('moodIllustration');
    if (bg) bg.className = 'mood-bg bg-' + mood;
    
    const illustrations = { happy: '🌞', sad: '🌧️', anxious: '🌪️', calm: '🌿', energetic: '⚡' };
    if (illustration) illustration.textContent = illustrations[mood] || '🌈';
    
    // Refresh places if area is selected
    const area = document.getElementById('area').value;
    if (area) {
        showPlaces();
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
