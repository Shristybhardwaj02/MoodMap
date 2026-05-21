# MoodMap - Your Personal Mood & Wellness Tracker

**Track Your Mood, Elevate Your Mind**

MoodMap is a comprehensive mood tracking and wellness application that helps users monitor their emotional well-being while discovering personalized wellness activities, nutrition tips, and nearby therapeutic locations.

## Features

- **Mood Tracking** - Log your daily moods and track emotional patterns
- **Analytics Dashboard** - Visualize mood trends with interactive charts and insights
- **Mood History** - Review past mood entries and identify patterns
- **Wellness Activities** - Discover personalized wellness activities tailored to your mood
- **Nutrition Tips** - Get healthy eating recommendations for better mental health
- **Nearby Places** - Find peaceful and therapeutic locations in your area
- **User Authentication** - Secure login, signup, and password management with OTP verification
- **User Profile** - Customize your profile and preferences
- **Data Export** - Export your mood data for personal records

## Tech Stack

- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript
- **Database:** MySQL
- **Server:** Apache (XAMPP)

## Project Structure

```
MoodMap/
├── api/                    # API endpoints
│   ├── export.php         # Export mood data
│   └── places.php         # Nearby places API
├── assets/
│   ├── css/               # Stylesheets
│   ├── images/            # Images and logos
│   └── js/                # JavaScript files
├── includes/              # Shared components
│   ├── config.php         # Database configuration
│   ├── header.php         # Header component
│   ├── footer.php         # Footer component
│   └── functions.php      # Utility functions
├── pages/                 # Main pages
│   ├── auth/              # Authentication pages
│   ├── dashboard/         # Dashboard
│   ├── mood/              # Mood tracking pages
│   ├── places/            # Places features
│   ├── profile/           # User profile
│   └── wellness/          # Wellness content
├── sql/                   # Database schema
├── index.php              # Home page
├── about.php              # About page
└── PROJECT_DESCRIPTION.html
```

## Installation

### Prerequisites
- XAMPP (or similar local server)
- PHP 7.4+
- MySQL

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/moodmap.git
   cd moodmap
   ```

2. **Configure Database**
   - Copy `sql/moodmap.sql`
   - Import into MySQL via phpMyAdmin or MySQL CLI
   - Update database credentials in `includes/config.php`

3. **Configure Local Server**
   - Place project in `htdocs` folder (XAMPP)
   - Start Apache and MySQL services
   - Access via `http://localhost/moodmap`

4. **Create Config File** (if not exists)
   - Update `includes/config.php` with your database credentials

## Usage

1. **Create Account** - Sign up with your email
2. **Verify OTP** - Enter OTP sent to your email
3. **Log Mood** - Daily mood entries from the dashboard
4. **View Analytics** - Check mood patterns and trends
5. **Explore Wellness** - Discover activities, nutrition tips, and nearby places
6. **Manage Profile** - Update your profile and preferences

## Key Pages

- **Dashboard** - Central hub for all features
- **Mood Log** - Add new mood entries
- **Analytics** - View mood statistics and trends
- **History** - Browse previous mood entries
- **Wellness Activities** - Personalized wellness recommendations
- **Food Tips** - Nutrition and diet guidance
- **Nearby Places** - Find therapeutic locations
- **Profile** - User settings and information

## Security

- Password hashing for user accounts
- OTP verification for email authenticity
- Secure session management
- Protected API endpoints
- XSS and SQL injection protection

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

Created for better mental health and wellness

## Contact & Support

For questions, suggestions, or bug reports, please open an issue on GitHub.

---

**Where Emotions Meet Wellness**
