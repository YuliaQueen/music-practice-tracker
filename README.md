# 🎵 Music Practice Tracker

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
  <br>
  <strong>Your Personal Music Practice Assistant</strong>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 🎯 About Music Practice Tracker

Music Practice Tracker is a comprehensive web application designed to help musicians organize, track, and improve their practice sessions. Built with Laravel and Vue.js, it provides a structured approach to musical learning with timers, progress tracking, and session management.

### ✨ Key Features

- **🎼 Structured Practice Sessions** - Create organized practice sessions with multiple exercises
- **⏱️ Exercise Timers** - Set individual timers for each exercise to maintain focus
- **📊 Progress Tracking** - Monitor your practice time and improvement over time
- **📋 Exercise Templates** - Save and reuse exercise configurations
- **🌙 Dark/Light Theme** - Switch between themes for comfortable practice
- **🌍 Internationalization** - Support for Russian and English languages
- **📱 Responsive Design** - Works seamlessly on desktop and mobile devices
- **📈 Statistics Dashboard** - View detailed practice statistics and trends

### 🛠️ Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3 with TypeScript
- **Styling**: Tailwind CSS with dark mode support
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Breeze
- **Internationalization**: Vue I18n
- **Build Tool**: Vite

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/PostgreSQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/music-practice-tracker.git
   cd music-practice-tracker
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 📖 Usage Guide

### Creating Your First Practice Session

1. **Register** or **Login** to your account
2. **Navigate** to "Sessions" → "Create New Session"
3. **Add exercises** with individual timers
4. **Start practicing** and track your progress
5. **Review statistics** on the Dashboard

### Managing Exercises

- Create custom exercises with descriptions
- Set practice durations
- Organize exercises by type (scales, pieces, technique, etc.)
- Save exercise templates for reuse

### Tracking Progress

- View daily, weekly, and monthly practice statistics
- Monitor time spent on different exercise types
- Track improvement over time
- Export data for external analysis

## 🌍 Internationalization

The application supports multiple languages:

- **🇷🇺 Russian** (default)
- **🇺🇸 English**

Language can be switched using the language selector in the navigation bar. Your preference is saved in localStorage.

## 🎨 Themes

Switch between light and dark themes using the theme toggle in the navigation. The theme preference is automatically saved and restored on subsequent visits.

## 📁 Project Structure

```
music-practice-tracker/
├── app/
│   ├── Http/Controllers/     # API controllers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── resources/
│   ├── js/
│   │   ├── Components/      # Vue components
│   │   ├── Pages/          # Page components
│   │   ├── Layouts/        # Layout components
│   │   ├── composables/    # Vue composables
│   │   ├── locales/        # Translation files
│   │   └── app.ts          # Application entry point
│   └── views/              # Blade templates
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
└── tests/                 # Test files
```

## 🧪 Testing

Run the test suite:

```bash
# Run PHP tests
php artisan test

# Run JavaScript tests
npm run test
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use TypeScript for JavaScript code
- Write tests for new features
- Update documentation as needed
- Follow conventional commit messages

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) framework
- Frontend powered by [Vue.js](https://vuejs.org/)
- Styled with [Tailwind CSS](https://tailwindcss.com/)
- Icons by [Heroicons](https://heroicons.com/)

## 📞 Support

If you encounter any issues or have questions:

- Create an [issue](https://github.com/yourusername/music-practice-tracker/issues)
- Check the [documentation](https://github.com/yourusername/music-practice-tracker/wiki)
- Contact us at [your-email@example.com](mailto:your-email@example.com)

---

<p align="center">
  Made with ❤️ for musicians worldwide
</p>