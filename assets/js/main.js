/**
 * MoodMap - Main JavaScript File
 * 
 * This file contains all the client-side JavaScript functionality
 * including mood selection, form validation, API calls, and animations.
 */

// ==========================================
// GLOBAL VARIABLES
// ==========================================

// Mood data with emojis, colors, and info
const MOODS = {
    happy: {
        emoji: '😊',
        label: 'Happy',
        color: '#FCD34D',
        bgClass: 'mood-bg-happy',
        borderClass: 'mood-border-happy'
    },
    sad: {
        emoji: '😢',
        label: 'Sad',
        color: '#60A5FA',
        bgClass: 'mood-bg-sad',
        borderClass: 'mood-border-sad'
    },
    stressed: {
        emoji: '😰',
        label: 'Stressed',
        color: '#F87171',
        bgClass: 'mood-bg-stressed',
        borderClass: 'mood-border-stressed'
    },
    anxious: {
        emoji: '😟',
        label: 'Anxious',
        color: '#A78BFA',
        bgClass: 'mood-bg-anxious',
        borderClass: 'mood-border-anxious'
    },
    energetic: {
        emoji: '⚡',
        label: 'Energetic',
        color: '#FB923C',
        bgClass: 'mood-bg-energetic',
        borderClass: 'mood-border-energetic'
    },
    tired: {
        emoji: '😴',
        label: 'Tired',
        color: '#9CA3AF',
        bgClass: 'mood-bg-tired',
        borderClass: 'mood-border-tired'
    },
    angry: {
        emoji: '😠',
        label: 'Angry',
        color: '#EF4444',
        bgClass: 'mood-bg-angry',
        borderClass: 'mood-border-angry'
    },
    calm: {
        emoji: '😌',
        label: 'Calm',
        color: '#34D399',
        bgClass: 'mood-bg-calm',
        borderClass: 'mood-border-calm'
    }
};

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initMoodPicker();
    initFormValidation();
    initToastNotifications();
    initSmoothScroll();
    initAnimations();
});

// ==========================================
// MOOD PICKER
// ==========================================

function initMoodPicker() {
    const moodPicker = document.querySelector('.mood-picker');
    if (!moodPicker) return;

    const moodButtons = moodPicker.querySelectorAll('.mood-option');
    const selectedMoodInput = document.getElementById('selected_mood');
    const intensitySlider = document.getElementById('mood_intensity');

    moodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove selected class from all buttons
            moodButtons.forEach(btn => btn.classList.remove('selected', 'ring-4', 'ring-primary/30'));
            
            // Add selected class to clicked button
            this.classList.add('selected', 'ring-4', 'ring-primary/30');
            
            // Update hidden input
            const moodType = this.dataset.mood;
            if (selectedMoodInput) {
                selectedMoodInput.value = moodType;
            }

            // Add animation
            this.style.transform = 'scale(1.1)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);

            // Show intensity slider and notes area
            const moodDetails = document.getElementById('mood-details');
            if (moodDetails) {
                moodDetails.classList.remove('hidden');
                moodDetails.classList.add('fade-in');
            }
        });
    });

    // Intensity slider value display
    if (intensitySlider) {
        const intensityValue = document.getElementById('intensity-value');
        intensitySlider.addEventListener('input', function() {
            if (intensityValue) {
                intensityValue.textContent = this.value;
            }
        });
    }
}

// ==========================================
// FORM VALIDATION
// ==========================================

function initFormValidation() {
    // Signup form validation
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', validateSignupForm);
    }

    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    // Real-time email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });

    // Real-time password strength
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }

    // Password match validation
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }
}

function validateSignupForm(e) {
    let isValid = true;
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    // Clear previous errors
    clearAllErrors();

    // Name validation
    if (!name.value.trim() || name.value.length < 2) {
        showError(name, 'Please enter a valid name (at least 2 characters)');
        isValid = false;
    }

    // Email validation
    if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    }

    // Phone validation (10 digits)
    if (phone && phone.value && !/^\d{10}$/.test(phone.value.replace(/\D/g, ''))) {
        showError(phone, 'Please enter a valid 10-digit phone number');
        isValid = false;
    }

    // Password validation
    if (password.value.length < 6) {
        showError(password, 'Password must be at least 6 characters');
        isValid = false;
    }

    // Confirm password validation
    if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
}

function validateLoginForm(e) {
    let isValid = true;
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    clearAllErrors();

    if (!isValidEmail(email.value)) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    }

    if (!password.value) {
        showError(password, 'Please enter your password');
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
}

function validateEmail(input) {
    if (isValidEmail(input.value)) {
        input.classList.remove('error');
        input.classList.add('success');
        removeError(input);
    } else if (input.value) {
        input.classList.remove('success');
        input.classList.add('error');
        showError(input, 'Invalid email format');
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('password-strength');
    if (!strengthIndicator) return;

    let strength = 0;
    let message = '';
    let color = '';

    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    switch (strength) {
        case 0:
        case 1:
            message = 'Weak';
            color = '#EF4444';
            break;
        case 2:
        case 3:
            message = 'Medium';
            color = '#F59E0B';
            break;
        case 4:
        case 5:
            message = 'Strong';
            color = '#10B981';
            break;
    }

    strengthIndicator.textContent = password ? message : '';
    strengthIndicator.style.color = color;
}

function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (confirmPassword.value && password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match');
    } else {
        removeError(confirmPassword);
    }
}

function showError(input, message) {
    input.classList.add('error');
    
    // Remove existing error message if any
    const existingError = input.parentElement.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }

    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    input.parentElement.appendChild(errorDiv);
}

function removeError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentElement.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function clearAllErrors() {
    document.querySelectorAll('.form-error').forEach(el => el.remove());
    document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
}

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================

function initToastNotifications() {
    // Check for flash messages from PHP
    const flashMessage = document.querySelector('[data-flash-message]');
    if (flashMessage) {
        const type = flashMessage.dataset.flashType || 'info';
        const message = flashMessage.dataset.flashMessage;
        showToast(message, type);
    }
}

function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ==========================================
// SMOOTH SCROLL
// ==========================================

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ==========================================
// ANIMATIONS
// ==========================================

function initAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements with animate class
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// ==========================================
// GEOLOCATION
// ==========================================

function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error('Geolocation is not supported by your browser'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            },
            (error) => {
                let message = 'Unable to get your location';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        message = 'Please allow location access to find nearby places';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message = 'Location information unavailable';
                        break;
                    case error.TIMEOUT:
                        message = 'Location request timed out';
                        break;
                }
                reject(new Error(message));
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes cache
            }
        );
    });
}

// ==========================================
// PLACES API INTEGRATION
// ==========================================

async function searchNearbyPlaces(mood, lat, lng, radius = 3000) {
    try {
        const response = await fetch('api/places.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'search_nearby',
                mood: mood,
                lat: lat,
                lng: lng,
                radius: radius
            })
        });

        const data = await response.json();
        
        if (data.success) {
            return data.places;
        } else {
            throw new Error(data.message || 'Failed to fetch places');
        }
    } catch (error) {
        console.error('Error fetching places:', error);
        throw error;
    }
}

function openGoogleMaps(lat, lng, placeName) {
    const url = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&destination_place_id=${encodeURIComponent(placeName)}`;
    window.open(url, '_blank');
}

// ==========================================
// SAVE/UNSAVE PLACE
// ==========================================

async function toggleSavePlace(placeId, button) {
    try {
        const isSaved = button.classList.contains('saved');
        const action = isSaved ? 'unsave' : 'save';

        const response = await fetch('api/places.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action + '_place',
                place_id: placeId
            })
        });

        const data = await response.json();

        if (data.success) {
            button.classList.toggle('saved');
            const icon = button.querySelector('svg');
            if (icon) {
                icon.setAttribute('fill', isSaved ? 'none' : 'currentColor');
            }
            showToast(isSaved ? 'Place removed from saved' : 'Place saved!', 'success');
        } else {
            showToast(data.message || 'Failed to save place', 'error');
        }
    } catch (error) {
        console.error('Error toggling save:', error);
        showToast('Something went wrong', 'error');
    }
}

// ==========================================
// MOOD LOGGING
// ==========================================

async function logMood(moodType, intensity, notes = '') {
    try {
        const response = await fetch('api/mood.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'log_mood',
                mood_type: moodType,
                intensity: intensity,
                notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Mood logged successfully!', 'success');
            return data;
        } else {
            throw new Error(data.message || 'Failed to log mood');
        }
    } catch (error) {
        console.error('Error logging mood:', error);
        showToast('Failed to log mood', 'error');
        throw error;
    }
}

// ==========================================
// BREATHING EXERCISE
// ==========================================

let breathingInterval = null;

function startBreathingExercise() {
    const circle = document.querySelector('.breathing-circle');
    const instruction = document.getElementById('breathing-instruction');
    if (!circle || !instruction) return;

    let phase = 'inhale';
    
    function updatePhase() {
        if (phase === 'inhale') {
            circle.classList.remove('exhale');
            circle.classList.add('inhale');
            instruction.textContent = 'Breathe In...';
            phase = 'hold-in';
        } else if (phase === 'hold-in') {
            circle.classList.remove('inhale');
            instruction.textContent = 'Hold...';
            phase = 'exhale';
        } else if (phase === 'exhale') {
            circle.classList.add('exhale');
            instruction.textContent = 'Breathe Out...';
            phase = 'hold-out';
        } else {
            circle.classList.remove('exhale');
            instruction.textContent = 'Hold...';
            phase = 'inhale';
        }
    }

    updatePhase();
    breathingInterval = setInterval(updatePhase, 4000);
}

function stopBreathingExercise() {
    if (breathingInterval) {
        clearInterval(breathingInterval);
        breathingInterval = null;
    }
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatTime(dateString) {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleTimeString('en-US', options);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of Earth in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    return distance.toFixed(1);
}

// Export functions for use in other scripts
window.MoodMap = {
    MOODS,
    showToast,
    getCurrentLocation,
    searchNearbyPlaces,
    openGoogleMaps,
    toggleSavePlace,
    logMood,
    startBreathingExercise,
    stopBreathingExercise,
    formatDate,
    formatTime,
    calculateDistance
};
