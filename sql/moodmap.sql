-- =====================================================
-- MoodMap Database Schema
-- Created for: College Exhibition Project
-- Database: MySQL
-- =====================================================

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS moodmap;
CREATE DATABASE moodmap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moodmap;

-- =====================================================
-- TABLE 1: users
-- Stores all user information and authentication data
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'User full name',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT 'User email (used for login)',
    phone VARCHAR(15) NULL COMMENT 'Optional phone number',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    profile_pic VARCHAR(255) DEFAULT NULL COMMENT 'Profile picture path',
    
    -- OTP Verification
    otp VARCHAR(6) DEFAULT NULL COMMENT '6-digit OTP for verification',
    otp_expires DATETIME DEFAULT NULL COMMENT 'OTP expiry timestamp',
    is_verified TINYINT(1) DEFAULT 0 COMMENT '1 = email verified, 0 = not verified',
    
    -- Password Reset
    reset_token VARCHAR(255) DEFAULT NULL COMMENT 'Password reset token',
    reset_token_expires DATETIME DEFAULT NULL COMMENT 'Reset token expiry',
    
    -- Location Settings
    location_lat DECIMAL(10, 8) DEFAULT NULL COMMENT 'Last known latitude',
    location_lng DECIMAL(11, 8) DEFAULT NULL COMMENT 'Last known longitude',
    preferred_radius INT DEFAULT 5000 COMMENT 'Preferred search radius in meters (default 5km)',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL COMMENT 'Last successful login time',
    
    INDEX idx_email (email),
    INDEX idx_is_verified (is_verified)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 2: moods
-- Stores all mood entries logged by users
-- =====================================================
CREATE TABLE moods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Foreign key to users table',
    
    -- Mood Data
    mood_type ENUM('happy', 'sad', 'stressed', 'anxious', 'energetic', 'tired', 'angry', 'calm') NOT NULL COMMENT 'Type of mood',
    mood_intensity TINYINT(1) NOT NULL DEFAULT 3 COMMENT 'Intensity from 1 (low) to 5 (high)',
    notes TEXT DEFAULT NULL COMMENT 'Optional notes about the mood',
    
    -- Timestamps
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When mood was logged',
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for better query performance
    INDEX idx_user_id (user_id),
    INDEX idx_mood_type (mood_type),
    INDEX idx_logged_at (logged_at),
    INDEX idx_user_date (user_id, logged_at)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 3: mood_activities
-- Tracks what activities/places user did after logging mood
-- Used for learning what helps improve mood
-- =====================================================
CREATE TABLE mood_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mood_id INT NOT NULL COMMENT 'Foreign key to moods table',
    user_id INT NOT NULL COMMENT 'Foreign key to users table',
    
    -- Activity Data
    activity_type ENUM('place_visit', 'food', 'activity', 'wellness') NOT NULL COMMENT 'Type of activity',
    activity_name VARCHAR(255) NOT NULL COMMENT 'Name of activity or place',
    place_id VARCHAR(255) DEFAULT NULL COMMENT 'Google Place ID if applicable',
    
    -- Feedback
    helped_rating TINYINT(1) DEFAULT NULL COMMENT 'Did it help? 1-5 scale',
    feedback_notes TEXT DEFAULT NULL COMMENT 'Optional feedback',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (mood_id) REFERENCES moods(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_mood_id (mood_id),
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 4: saved_places
-- User's saved/favorite places
-- =====================================================
CREATE TABLE saved_places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Foreign key to users table',
    
    -- Place Data from Google Places API
    place_id VARCHAR(255) NOT NULL COMMENT 'Google Place ID',
    place_name VARCHAR(255) NOT NULL COMMENT 'Name of the place',
    place_type VARCHAR(100) DEFAULT NULL COMMENT 'Type (cafe, park, gym, etc.)',
    place_address TEXT DEFAULT NULL COMMENT 'Full address',
    place_rating DECIMAL(2,1) DEFAULT NULL COMMENT 'Google rating (0.0-5.0)',
    place_photo VARCHAR(500) DEFAULT NULL COMMENT 'Photo reference from Google',
    place_lat DECIMAL(10, 8) DEFAULT NULL COMMENT 'Latitude',
    place_lng DECIMAL(11, 8) DEFAULT NULL COMMENT 'Longitude',
    
    -- MoodMap Data
    mood_category VARCHAR(50) DEFAULT NULL COMMENT 'Which mood this place is good for',
    user_notes TEXT DEFAULT NULL COMMENT 'User personal notes',
    
    -- Timestamps
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Unique constraint - user can't save same place twice
    UNIQUE KEY unique_user_place (user_id, place_id),
    
    INDEX idx_user_id (user_id),
    INDEX idx_place_id (place_id),
    INDEX idx_mood_category (mood_category)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 5: user_preferences
-- Stores user settings and preferences
-- =====================================================
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE COMMENT 'Foreign key to users table',
    
    -- Place Preferences (stored as JSON)
    preferred_place_types JSON DEFAULT NULL COMMENT 'Array of preferred place types',
    avoided_place_types JSON DEFAULT NULL COMMENT 'Array of place types to avoid',
    
    -- Food Preferences
    dietary_restrictions JSON DEFAULT NULL COMMENT 'Array: vegetarian, vegan, halal, etc.',
    food_preferences JSON DEFAULT NULL COMMENT 'Favorite food types',
    
    -- Activity Preferences
    activity_preferences JSON DEFAULT NULL COMMENT 'Preferred activities',
    
    -- App Settings
    notification_enabled TINYINT(1) DEFAULT 1 COMMENT 'Push notifications on/off',
    email_notifications TINYINT(1) DEFAULT 1 COMMENT 'Email notifications on/off',
    dark_mode TINYINT(1) DEFAULT 0 COMMENT 'Dark mode on/off',
    language VARCHAR(10) DEFAULT 'en' COMMENT 'Preferred language',
    
    -- Privacy Settings
    share_mood_data TINYINT(1) DEFAULT 0 COMMENT 'Share anonymous mood data',
    show_on_leaderboard TINYINT(1) DEFAULT 1 COMMENT 'Show on mood streaks leaderboard',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 6: wellness_tips
-- Pre-loaded content: quotes, activities, food tips
-- =====================================================
CREATE TABLE wellness_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Categorization
    mood_type ENUM('happy', 'sad', 'stressed', 'anxious', 'energetic', 'tired', 'angry', 'calm', 'all') NOT NULL COMMENT 'Which mood this tip is for',
    tip_type ENUM('quote', 'breathing', 'activity', 'food', 'place', 'general') NOT NULL COMMENT 'Type of tip',
    
    -- Content
    title VARCHAR(255) DEFAULT NULL COMMENT 'Short title/heading',
    content TEXT NOT NULL COMMENT 'Main content of the tip',
    author VARCHAR(100) DEFAULT NULL COMMENT 'Quote author if applicable',
    image_url VARCHAR(500) DEFAULT NULL COMMENT 'Optional image',
    
    -- Priority and Status
    priority INT DEFAULT 0 COMMENT 'Higher = shown first',
    is_active TINYINT(1) DEFAULT 1 COMMENT '1 = active, 0 = hidden',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_mood_type (mood_type),
    INDEX idx_tip_type (tip_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 7: mood_insights
-- Stores generated insights/analytics for users
-- =====================================================
CREATE TABLE mood_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Foreign key to users table',
    
    -- Insight Data
    insight_type ENUM('weekly_summary', 'monthly_summary', 'pattern', 'trigger', 'improvement', 'streak') NOT NULL,
    insight_title VARCHAR(255) NOT NULL COMMENT 'Human readable title',
    insight_data JSON NOT NULL COMMENT 'Detailed insight data as JSON',
    
    -- Period
    period_start DATE DEFAULT NULL COMMENT 'Start of period for this insight',
    period_end DATE DEFAULT NULL COMMENT 'End of period for this insight',
    
    -- Status
    is_read TINYINT(1) DEFAULT 0 COMMENT 'Has user seen this insight',
    
    -- Timestamps
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_insight_type (insight_type),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 8: mood_streaks
-- Tracks user's mood logging streaks for gamification
-- =====================================================
CREATE TABLE mood_streaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE COMMENT 'Foreign key to users table',
    
    -- Streak Data
    current_streak INT DEFAULT 0 COMMENT 'Current consecutive days',
    longest_streak INT DEFAULT 0 COMMENT 'All-time longest streak',
    total_logs INT DEFAULT 0 COMMENT 'Total mood logs ever',
    
    -- Dates
    last_log_date DATE DEFAULT NULL COMMENT 'Last date user logged mood',
    streak_start_date DATE DEFAULT NULL COMMENT 'When current streak started',
    
    -- Timestamps
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABLE 9: place_recommendations
-- Cache for mood-based place type mappings
-- =====================================================
CREATE TABLE place_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    mood_type ENUM('happy', 'sad', 'stressed', 'anxious', 'energetic', 'tired', 'angry', 'calm') NOT NULL,
    place_type VARCHAR(100) NOT NULL COMMENT 'Google Places type',
    place_type_display VARCHAR(100) NOT NULL COMMENT 'Human readable name',
    priority INT DEFAULT 0 COMMENT 'Display order',
    
    UNIQUE KEY unique_mood_place (mood_type, place_type),
    INDEX idx_mood_type (mood_type)
) ENGINE=InnoDB;

-- =====================================================
-- SEED DATA: Place Recommendations by Mood
-- =====================================================

-- Happy Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('happy', 'cafe', 'Cafés', 1),
('happy', 'restaurant', 'Restaurants', 2),
('happy', 'shopping_mall', 'Shopping Malls', 3),
('happy', 'amusement_park', 'Adventure Parks', 4),
('happy', 'movie_theater', 'Movie Theaters', 5),
('happy', 'bowling_alley', 'Bowling Alleys', 6),
('happy', 'night_club', 'Night Clubs', 7),
('happy', 'bar', 'Bars & Pubs', 8);

-- Sad Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('sad', 'park', 'Peaceful Parks', 1),
('sad', 'cafe', 'Quiet Cafés', 2),
('sad', 'book_store', 'Bookstores', 3),
('sad', 'library', 'Libraries', 4),
('sad', 'spa', 'Spas', 5),
('sad', 'church', 'Places of Worship', 6),
('sad', 'art_gallery', 'Art Galleries', 7),
('sad', 'natural_feature', 'Nature Spots', 8);

-- Stressed Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('stressed', 'spa', 'Spas & Wellness', 1),
('stressed', 'gym', 'Gyms', 2),
('stressed', 'park', 'Parks & Gardens', 3),
('stressed', 'yoga', 'Yoga Studios', 4),
('stressed', 'library', 'Libraries', 5),
('stressed', 'cafe', 'Quiet Cafés', 6),
('stressed', 'natural_feature', 'Nature Retreats', 7),
('stressed', 'beauty_salon', 'Beauty Salons', 8);

-- Anxious Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('anxious', 'cafe', 'Quiet Cafés', 1),
('anxious', 'park', 'Peaceful Parks', 2),
('anxious', 'book_store', 'Bookstores', 3),
('anxious', 'library', 'Libraries', 4),
('anxious', 'spa', 'Relaxation Spas', 5),
('anxious', 'yoga', 'Meditation Centers', 6),
('anxious', 'art_gallery', 'Art Galleries', 7),
('anxious', 'natural_feature', 'Nature Trails', 8);

-- Energetic Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('energetic', 'gym', 'Gyms & Fitness', 1),
('energetic', 'stadium', 'Sports Venues', 2),
('energetic', 'amusement_park', 'Adventure Parks', 3),
('energetic', 'bowling_alley', 'Bowling Alleys', 4),
('energetic', 'park', 'Parks (for jogging)', 5),
('energetic', 'night_club', 'Dance Clubs', 6),
('energetic', 'shopping_mall', 'Shopping Malls', 7),
('energetic', 'tourist_attraction', 'Tourist Spots', 8);

-- Tired Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('tired', 'cafe', 'Cozy Cafés', 1),
('tired', 'spa', 'Relaxing Spas', 2),
('tired', 'restaurant', 'Comfortable Restaurants', 3),
('tired', 'park', 'Quiet Parks', 4),
('tired', 'library', 'Libraries', 5),
('tired', 'movie_theater', 'Movie Theaters', 6),
('tired', 'book_store', 'Bookstores', 7),
('tired', 'bakery', 'Bakeries', 8);

-- Angry Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('angry', 'gym', 'Gyms (workout it out)', 1),
('angry', 'park', 'Open Parks', 2),
('angry', 'stadium', 'Sports Facilities', 3),
('angry', 'bowling_alley', 'Bowling Alleys', 4),
('angry', 'spa', 'Calming Spas', 5),
('angry', 'natural_feature', 'Nature Spots', 6),
('angry', 'yoga', 'Yoga Studios', 7),
('angry', 'cafe', 'Quiet Cafés', 8);

-- Calm Mood Places
INSERT INTO place_recommendations (mood_type, place_type, place_type_display, priority) VALUES
('calm', 'art_gallery', 'Art Galleries', 1),
('calm', 'museum', 'Museums', 2),
('calm', 'park', 'Beautiful Gardens', 3),
('calm', 'library', 'Libraries', 4),
('calm', 'cafe', 'Lakeside Cafés', 5),
('calm', 'book_store', 'Bookstores', 6),
('calm', 'spa', 'Wellness Centers', 7),
('calm', 'church', 'Spiritual Places', 8);

-- =====================================================
-- SEED DATA: Wellness Tips - QUOTES
-- =====================================================

-- Happy Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('happy', 'quote', 'Happiness is not by chance, but by choice.', 'Jim Rohn', 10),
('happy', 'quote', 'The purpose of our lives is to be happy.', 'Dalai Lama', 9),
('happy', 'quote', 'Happiness depends upon ourselves.', 'Aristotle', 8),
('happy', 'quote', 'Be happy for this moment. This moment is your life.', 'Omar Khayyam', 7),
('happy', 'quote', 'The most important thing is to enjoy your life – to be happy – it''s all that matters.', 'Audrey Hepburn', 6),
('happy', 'quote', 'Happiness is a warm puppy.', 'Charles M. Schulz', 5),
('happy', 'quote', 'Count your age by friends, not years. Count your life by smiles, not tears.', 'John Lennon', 4),
('happy', 'quote', 'Happiness is not something ready made. It comes from your own actions.', 'Dalai Lama', 3),
('happy', 'quote', 'The happiness of your life depends upon the quality of your thoughts.', 'Marcus Aurelius', 2),
('happy', 'quote', 'Joy is not in things; it is in us.', 'Richard Wagner', 1);

-- Sad Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('sad', 'quote', 'It''s okay to not be okay. Take it one moment at a time.', 'Unknown', 10),
('sad', 'quote', 'Every storm runs out of rain.', 'Maya Angelou', 9),
('sad', 'quote', 'The sun will rise and we will try again.', 'Twenty One Pilots', 8),
('sad', 'quote', 'Tears are words that need to be written.', 'Paulo Coelho', 7),
('sad', 'quote', 'You are stronger than you know. More capable than you ever dreamed.', 'Unknown', 6),
('sad', 'quote', 'Sometimes the bad things that happen in our lives put us on the path to the best things.', 'Unknown', 5),
('sad', 'quote', 'This too shall pass.', 'Persian Proverb', 4),
('sad', 'quote', 'Stars can''t shine without darkness.', 'Unknown', 3),
('sad', 'quote', 'Crying is not a sign of weakness. It''s a sign you''ve been strong for too long.', 'Unknown', 2),
('sad', 'quote', 'The wound is the place where the light enters you.', 'Rumi', 1);

-- Stressed Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('stressed', 'quote', 'You don''t have to control your thoughts. You just have to stop letting them control you.', 'Dan Millman', 10),
('stressed', 'quote', 'Almost everything will work again if you unplug it for a few minutes, including you.', 'Anne Lamott', 9),
('stressed', 'quote', 'Stress is caused by being here but wanting to be there.', 'Eckhart Tolle', 8),
('stressed', 'quote', 'The greatest weapon against stress is our ability to choose one thought over another.', 'William James', 7),
('stressed', 'quote', 'Take a deep breath. It''s just a bad day, not a bad life.', 'Unknown', 6),
('stressed', 'quote', 'You are not your thoughts. You are the observer of your thoughts.', 'Unknown', 5),
('stressed', 'quote', 'Slow down. Calm down. Don''t worry. Don''t hurry. Trust the process.', 'Alexandra Stoddard', 4),
('stressed', 'quote', 'Rule number one: Don''t sweat the small stuff. Rule number two: It''s all small stuff.', 'Robert Eliot', 3),
('stressed', 'quote', 'The time to relax is when you don''t have time for it.', 'Sydney J. Harris', 2),
('stressed', 'quote', 'Breathe. Let go. And remind yourself that this very moment is the only one you know you have for sure.', 'Oprah Winfrey', 1);

-- Anxious Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('anxious', 'quote', 'Breathe. You''re going to be okay. You''ve survived every bad day so far.', 'Unknown', 10),
('anxious', 'quote', 'Anxiety is a thin stream of fear trickling through the mind. If encouraged, it cuts a channel.', 'Arthur Somers Roche', 9),
('anxious', 'quote', 'Nothing diminishes anxiety faster than action.', 'Walter Anderson', 8),
('anxious', 'quote', 'You don''t have to see the whole staircase, just take the first step.', 'Martin Luther King Jr.', 7),
('anxious', 'quote', 'Worry is like a rocking chair: it gives you something to do but never gets you anywhere.', 'Erma Bombeck', 6),
('anxious', 'quote', 'Present fears are less than horrible imaginings.', 'William Shakespeare', 5),
('anxious', 'quote', 'Anxiety does not empty tomorrow of its sorrows, but only empties today of its strength.', 'Charles Spurgeon', 4),
('anxious', 'quote', 'Life is ten percent what you experience and ninety percent how you respond to it.', 'Dorothy M. Neddermeyer', 3),
('anxious', 'quote', 'Do what you can, with what you have, where you are.', 'Theodore Roosevelt', 2),
('anxious', 'quote', 'Feelings are just visitors. Let them come and go.', 'Mooji', 1);

-- Energetic Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('energetic', 'quote', 'Energy and persistence conquer all things.', 'Benjamin Franklin', 10),
('energetic', 'quote', 'The energy of the mind is the essence of life.', 'Aristotle', 9),
('energetic', 'quote', 'Life is energy and, as such, it belongs to all, reaches all, and blesses all.', 'Donna Goddard', 8),
('energetic', 'quote', 'Your positive action combined with positive thinking results in success.', 'Shiv Khera', 7),
('energetic', 'quote', 'The higher your energy level, the more efficient your body.', 'Tony Robbins', 6),
('energetic', 'quote', 'When you have energy, you have everything.', 'Unknown', 5),
('energetic', 'quote', 'Do something today that your future self will thank you for.', 'Sean Patrick Flanery', 4),
('energetic', 'quote', 'The only way to do great work is to love what you do.', 'Steve Jobs', 3),
('energetic', 'quote', 'Passion is energy. Feel the power that comes from focusing on what excites you.', 'Oprah Winfrey', 2),
('energetic', 'quote', 'Live life to the fullest, and focus on the positive.', 'Matt Cameron', 1);

-- Tired Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('tired', 'quote', 'Rest when you''re weary. Refresh and renew yourself.', 'Ralph Marston', 10),
('tired', 'quote', 'Almost everything will work again if you unplug it for a few minutes, including you.', 'Anne Lamott', 9),
('tired', 'quote', 'Sometimes the most productive thing you can do is relax.', 'Mark Black', 8),
('tired', 'quote', 'Your calm mind is the ultimate weapon against your challenges.', 'Bryant McGill', 7),
('tired', 'quote', 'It''s okay to take a break. You don''t have to earn rest.', 'Unknown', 6),
('tired', 'quote', 'Self-care is not selfish. You cannot serve from an empty vessel.', 'Eleanor Brown', 5),
('tired', 'quote', 'Rest is not idleness.', 'John Lubbock', 4),
('tired', 'quote', 'Take rest; a field that has rested gives a beautiful crop.', 'Ovid', 3),
('tired', 'quote', 'Doing nothing is better than being busy doing nothing.', 'Lao Tzu', 2),
('tired', 'quote', 'The time to relax is when you don''t have time for it.', 'Sydney J. Harris', 1);

-- Angry Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('angry', 'quote', 'Speak when you are angry and you''ll make the best speech you''ll ever regret.', 'Ambrose Bierce', 10),
('angry', 'quote', 'For every minute you are angry you lose sixty seconds of happiness.', 'Ralph Waldo Emerson', 9),
('angry', 'quote', 'Anger is an acid that can do more harm to the vessel in which it is stored than to anything on which it is poured.', 'Mark Twain', 8),
('angry', 'quote', 'When anger rises, think of the consequences.', 'Confucius', 7),
('angry', 'quote', 'Holding on to anger is like drinking poison and expecting the other person to die.', 'Buddha', 6),
('angry', 'quote', 'The best fighter is never angry.', 'Lao Tzu', 5),
('angry', 'quote', 'Anybody can become angry — that is easy, but to be angry with the right person and at the right time is not easy.', 'Aristotle', 4),
('angry', 'quote', 'Let go of anger; it''s just hurting you.', 'Unknown', 3),
('angry', 'quote', 'Breathe. It''s just a moment, not a lifetime.', 'Unknown', 2),
('angry', 'quote', 'A moment of patience in a moment of anger saves you a hundred moments of regret.', 'Unknown', 1);

-- Calm Quotes
INSERT INTO wellness_tips (mood_type, tip_type, content, author, priority) VALUES
('calm', 'quote', 'Calmness is the cradle of power.', 'Josiah Gilbert Holland', 10),
('calm', 'quote', 'Peace begins with a smile.', 'Mother Teresa', 9),
('calm', 'quote', 'The greatest weapon against stress is our ability to choose one thought over another.', 'William James', 8),
('calm', 'quote', 'In the midst of movement and chaos, keep stillness inside of you.', 'Deepak Chopra', 7),
('calm', 'quote', 'Quiet the mind, and the soul will speak.', 'Ma Jaya Sati Bhagavati', 6),
('calm', 'quote', 'Within you there is a stillness and sanctuary to which you can retreat at any time.', 'Hermann Hesse', 5),
('calm', 'quote', 'Serenity is not freedom from the storm, but peace amid the storm.', 'Unknown', 4),
('calm', 'quote', 'Learn to be calm and you will always be happy.', 'Paramahansa Yogananda', 3),
('calm', 'quote', 'Nothing can bring you peace but yourself.', 'Ralph Waldo Emerson', 2),
('calm', 'quote', 'The mind is everything. What you think you become.', 'Buddha', 1);

-- =====================================================
-- SEED DATA: Wellness Tips - ACTIVITIES
-- =====================================================

-- Happy Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('happy', 'activity', 'Celebrate Small Wins', 'Take a moment to acknowledge something good you did today, no matter how small!', 10),
('happy', 'activity', 'Share Your Joy', 'Call a friend or family member to share your good mood. Happiness multiplies when shared!', 9),
('happy', 'activity', 'Try Something New', 'Your positive energy is perfect for learning something new. Take a class or try a new hobby!', 8),
('happy', 'activity', 'Capture the Moment', 'Take a photo or write about this moment. Future you will appreciate this happy memory!', 7),
('happy', 'activity', 'Random Act of Kindness', 'Spread your happiness! Pay for someone''s coffee or compliment a stranger.', 6);

-- Sad Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('sad', 'activity', 'Gentle Movement', 'Take a slow walk outside. Fresh air and gentle movement can help shift your energy.', 10),
('sad', 'activity', 'Journal Your Feelings', 'Write down what you''re feeling without judgment. Getting it out helps process emotions.', 9),
('sad', 'activity', 'Comfort Movie/Show', 'Watch something familiar and comforting. It''s okay to seek comfort in entertainment.', 8),
('sad', 'activity', 'Reach Out', 'Text or call someone you trust. You don''t have to go through this alone.', 7),
('sad', 'activity', 'Self-Compassion Practice', 'Treat yourself like you would treat your best friend. Be gentle with yourself.', 6);

-- Stressed Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('stressed', 'activity', 'Take a Break', 'Step away from what''s stressing you for at least 15 minutes. Your mind needs rest.', 10),
('stressed', 'activity', 'Physical Exercise', 'Do any physical activity - walk, stretch, or workout. Movement releases tension.', 9),
('stressed', 'activity', 'Prioritize Tasks', 'Write down everything stressing you, then pick just ONE thing to focus on first.', 8),
('stressed', 'activity', 'Progressive Muscle Relaxation', 'Tense and release each muscle group from toes to head. Feel the tension melt away.', 7),
('stressed', 'activity', 'Nature Time', 'Spend time in nature. Even 10 minutes in a park can lower stress hormones.', 6);

-- Anxious Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('anxious', 'activity', '5-4-3-2-1 Grounding', 'Notice 5 things you see, 4 you touch, 3 you hear, 2 you smell, 1 you taste.', 10),
('anxious', 'activity', 'Organize Something', 'Organize a drawer, your desk, or your phone. Small accomplishments help anxiety.', 9),
('anxious', 'activity', 'Limit Caffeine', 'Avoid coffee and energy drinks. Try herbal tea instead - it can help calm nerves.', 8),
('anxious', 'activity', 'Calming Music', 'Put on some slow, calming music or nature sounds. Let the rhythm slow your heartbeat.', 7),
('anxious', 'activity', 'Write Your Worries', 'Write down your anxious thoughts, then challenge each one. Are they really true?', 6);

-- Energetic Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('energetic', 'activity', 'Workout Session', 'Channel that energy into a great workout! Cardio, weights, or whatever gets you moving.', 10),
('energetic', 'activity', 'Tackle Big Tasks', 'Use this energy for tasks you''ve been putting off. You''ve got the power to do it!', 9),
('energetic', 'activity', 'Learn Something', 'Your brain is ready to absorb new information. Watch a tutorial or read an article.', 8),
('energetic', 'activity', 'Social Activities', 'Meet up with friends or attend an event. Your energy will make it fun for everyone!', 7),
('energetic', 'activity', 'Creative Projects', 'Start that creative project you''ve been thinking about. Now''s the perfect time!', 6);

-- Tired Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('tired', 'activity', 'Power Nap', 'Take a 15-20 minute nap. Set an alarm - short naps refresh without grogginess.', 10),
('tired', 'activity', 'Light Stretching', 'Gentle stretching increases blood flow and can help boost energy naturally.', 9),
('tired', 'activity', 'Hydrate', 'Drink a full glass of water. Dehydration is often mistaken for tiredness.', 8),
('tired', 'activity', 'Easy Tasks Only', 'Do simple, mindless tasks. Save complex work for when you have more energy.', 7),
('tired', 'activity', 'Fresh Air Break', 'Step outside for 5 minutes. Sunlight and fresh air can help reset your energy.', 6);

-- Angry Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('angry', 'activity', 'Physical Release', 'Go for a run, hit the gym, or do jumping jacks. Physical activity releases anger safely.', 10),
('angry', 'activity', 'Cool Down First', 'Before reacting, count to 10 slowly. Give yourself space to respond, not react.', 9),
('angry', 'activity', 'Write It Out', 'Write an angry letter (don''t send it!) or journal about what made you angry.', 8),
('angry', 'activity', 'Walk Away', 'Remove yourself from the situation. Take a walk around the block to cool down.', 7),
('angry', 'activity', 'Squeeze Something', 'Use a stress ball or squeeze a pillow. Physical release helps process anger.', 6);

-- Calm Activities
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('calm', 'activity', 'Mindful Reading', 'Read something enjoyable. Let yourself get lost in a good book or article.', 10),
('calm', 'activity', 'Creative Expression', 'Draw, paint, write, or craft. Use this peaceful state for creative activities.', 9),
('calm', 'activity', 'Meditation', 'Practice meditation to deepen your calm. Even 5 minutes enhances this state.', 8),
('calm', 'activity', 'Gratitude Practice', 'Write down 3 things you''re grateful for. Appreciate this peaceful moment.', 7),
('calm', 'activity', 'Plan & Organize', 'Your clear mind is perfect for planning. Set goals or organize your schedule.', 6);

-- =====================================================
-- SEED DATA: Wellness Tips - FOOD & DRINKS
-- =====================================================

-- Happy Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('happy', 'food', 'Celebrate with Treats', 'It''s okay to enjoy your favorite dessert or treat! You''re happy - celebrate it!', 10),
('happy', 'food', 'Dark Chocolate', 'Dark chocolate releases endorphins and can enhance your already great mood!', 9),
('happy', 'food', 'Share a Meal', 'Happy moments are better shared. Invite someone for a meal together.', 8);

-- Sad Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('sad', 'food', 'Warm Comfort Food', 'Warm soup, hot chocolate, or your favorite comfort food can provide emotional warmth.', 10),
('sad', 'food', 'Omega-3 Rich Foods', 'Salmon, walnuts, and flaxseeds contain omega-3s that may help improve mood.', 9),
('sad', 'food', 'Avoid Alcohol', 'While tempting, alcohol is a depressant and can make sadness worse.', 8);

-- Stressed Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('stressed', 'food', 'Green Tea', 'L-theanine in green tea promotes relaxation without drowsiness. Perfect for stress.', 10),
('stressed', 'food', 'Dark Chocolate', 'A small amount of dark chocolate can reduce stress hormones. Enjoy mindfully!', 9),
('stressed', 'food', 'Avoid Caffeine', 'Too much coffee can increase stress and anxiety. Switch to herbal tea.', 8),
('stressed', 'food', 'Complex Carbs', 'Oatmeal and whole grains boost serotonin, helping you feel calmer.', 7);

-- Anxious Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('anxious', 'food', 'Chamomile Tea', 'Chamomile has natural calming properties. Enjoy a warm cup for relaxation.', 10),
('anxious', 'food', 'Bananas', 'Rich in potassium and B vitamins, bananas can help regulate stress hormones.', 9),
('anxious', 'food', 'Nuts & Seeds', 'Almonds, walnuts, and pumpkin seeds contain magnesium, which helps with anxiety.', 8),
('anxious', 'food', 'Skip the Sugar', 'Sugar spikes can worsen anxiety. Opt for protein-rich snacks instead.', 7);

-- Energetic Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('energetic', 'food', 'Protein-Rich Snacks', 'Eggs, Greek yogurt, or protein bars help sustain your energy levels.', 10),
('energetic', 'food', 'Fresh Fruits', 'Natural sugars in fruits provide clean energy. Try berries or oranges!', 9),
('energetic', 'food', 'Stay Hydrated', 'Water is essential for maintaining energy. Keep sipping throughout the day!', 8);

-- Tired Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('tired', 'food', 'Moderate Coffee', 'One cup of coffee can help, but don''t overdo it or you''ll crash later.', 10),
('tired', 'food', 'Iron-Rich Foods', 'Spinach, lean red meat, and lentils combat fatigue caused by low iron.', 9),
('tired', 'food', 'Water First', 'Drink water before reaching for caffeine. Dehydration often causes tiredness.', 8),
('tired', 'food', 'Light, Frequent Meals', 'Small, balanced meals maintain energy better than heavy ones that cause crashes.', 7);

-- Angry Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('angry', 'food', 'Crunchy Foods', 'Carrots, celery, or apples. The act of crunching can help release tension.', 10),
('angry', 'food', 'Cold Water', 'Drink cold water. It helps lower body temperature and can calm you down.', 9),
('angry', 'food', 'Avoid Alcohol', 'Alcohol can intensify anger and lead to regrettable actions. Skip it.', 8);

-- Calm Food
INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('calm', 'food', 'Herbal Tea', 'Lavender, peppermint, or chamomile tea complements your peaceful state.', 10),
('calm', 'food', 'Light Salads', 'Fresh, light meals maintain your calm energy without making you sluggish.', 9),
('calm', 'food', 'Mindful Eating', 'Eat slowly and savor each bite. Practice being present with your food.', 8);

-- =====================================================
-- SEED DATA: Breathing Exercises (for all moods)
-- =====================================================

INSERT INTO wellness_tips (mood_type, tip_type, title, content, priority) VALUES
('all', 'breathing', '4-7-8 Breathing', 'Inhale for 4 seconds, hold for 7 seconds, exhale for 8 seconds. Repeat 4 times. This activates your parasympathetic nervous system.', 10),
('all', 'breathing', 'Box Breathing', 'Inhale 4 seconds, hold 4 seconds, exhale 4 seconds, hold 4 seconds. Used by Navy SEALs for stress management!', 9),
('all', 'breathing', 'Deep Belly Breathing', 'Place hand on belly. Breathe in deeply through nose letting belly rise. Exhale slowly through mouth. Repeat 5-10 times.', 8),
('all', 'breathing', '5-5-5 Breathing', 'Breathe in for 5 seconds, out for 5 seconds. Do this for 5 minutes. Simple but very effective!', 7),
('all', 'breathing', 'Alternate Nostril', 'Close right nostril, inhale left. Close left, exhale right. Inhale right, exhale left. Balances both brain hemispheres.', 6);

-- =====================================================
-- Create a test user (password: test123)
-- =====================================================
INSERT INTO users (name, email, phone, password, is_verified, created_at) VALUES
('Test User', 'test@moodmap.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

-- Create user preferences for test user
INSERT INTO user_preferences (user_id, notification_enabled, dark_mode) VALUES
(1, 1, 0);

-- Create mood streak for test user
INSERT INTO mood_streaks (user_id, current_streak, longest_streak, total_logs) VALUES
(1, 0, 0, 0);

-- =====================================================
-- Add some sample moods for the test user
-- =====================================================
INSERT INTO moods (user_id, mood_type, mood_intensity, notes, logged_at) VALUES
(1, 'happy', 4, 'Had a great morning coffee!', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 'stressed', 3, 'Work deadline approaching', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 'calm', 4, 'Meditation really helped', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'energetic', 5, 'Morning workout was amazing!', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 'anxious', 2, 'Presentation tomorrow', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'happy', 5, 'Presentation went great!', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'tired', 3, 'Long day but productive', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'calm', 4, 'Relaxing weekend', NOW());

-- =====================================================
-- VIEWS: Useful queries as views
-- =====================================================

-- View: User mood summary
CREATE VIEW user_mood_summary AS
SELECT 
    u.id AS user_id,
    u.name,
    COUNT(m.id) AS total_moods,
    (SELECT mood_type FROM moods WHERE user_id = u.id ORDER BY logged_at DESC LIMIT 1) AS last_mood,
    ms.current_streak,
    ms.longest_streak
FROM users u
LEFT JOIN moods m ON u.id = m.user_id
LEFT JOIN mood_streaks ms ON u.id = ms.user_id
GROUP BY u.id;

-- View: Daily mood counts (for charts)
CREATE VIEW daily_mood_counts AS
SELECT 
    user_id,
    DATE(logged_at) AS log_date,
    mood_type,
    COUNT(*) AS count,
    AVG(mood_intensity) AS avg_intensity
FROM moods
GROUP BY user_id, DATE(logged_at), mood_type;

-- =====================================================
-- END OF SCHEMA
-- =====================================================
