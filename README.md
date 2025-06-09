# EdTech Learning Assistant

A simple learning management system that helps students ask questions about lessons using AI-powered responses. Built with Laravel, Vue.js, Inertia.js, and Google Gemini AI.

## 🚀 Features

- **Admin Panel**: Teachers can create, edit, and manage lesson content
- **Student Interface**: Students can read lessons and ask questions
- **AI-Powered Q&A**: Intelligent responses to student questions using Google Gemini
- **Context-Aware**: AI responses are based on the specific lesson content
- **Role-Based Access**: Separate interfaces for admins and students

## 🛠️ Tech Stack

- **Backend**: Laravel 10.x
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL/SQLite
- **AI Service**: Google Gemini API
- **Styling**: Tailwind CSS (via Laravel Breeze/Jetstream)

## 📋 Prerequisites

Before running this application, make sure you have:

- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm
- MySQL or SQLite
- Google Gemini API key

## ⚡ Installation

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd edtech-learning-assistant
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Environment Variables
Edit your `.env` file with the following settings:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edtech_learning
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google Gemini API Configuration
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent
```

### 6. Database Setup
```bash
# Run migrations
php artisan migrate:fresh

# (Optional) Seed with sample data
php artisan db:seed
```

### 7. Build Frontend Assets
```bash
npm run build
# Or for development
npm run dev
```

## 🔑 Getting Your Gemini API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the generated API key
5. Add it to your `.env` file as `GEMINI_API_KEY`

## 🏃‍♂️ Running the Application

### Development Mode
```bash
# Start the Laravel development server
composer dev
```

### Production Mode
```bash
# Build assets for production
composer dev:ssr
# Configure your web server to point to the public directory
```

The application will be available at `http://localhost:8000`

## 👥 Default User Accounts

After running the seeder, you can use these accounts:

**Admin Accounts:**
- Email: `admin@example.com`
- Password: `password`
- Email: `test@example.com`
- Password: `password`

**Student Account:**
- You can create a student account by registering through the application.

## 🏗️ Project Structure

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/              # Authentication controllers
│   │   ├── LessonController.php
│   │   └── QuestionController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Lesson.php
│   │   └── Question.php
│   └── Services/
│       └── AIService.php      # Gemini API integration
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Admin/         # Admin dashboard pages
│   │   │   └── Student/       # Student interface pages
│   │   └── Components/        # Reusable Vue components
│   └── views/
└── routes/
    └── web.php
```

## 🔄 How It Works

### For Admins (Teachers):
1. **Login** to the admin dashboard
2. **Create Lessons** by providing a title and content
3. **Manage Lessons** through the admin interface
4. **View Student Questions** and AI responses

### For Students:
1. **Login** to access available lessons
2. **Browse Lessons** from the lesson list
3. **Read Lesson Content** on individual lesson pages
4. **Ask Questions** using the chat interface
5. **Receive AI Answers** based on the lesson content

### AI Integration:
- When a student asks a question, the system sends both the lesson content and the question to Google Gemini
- Gemini provides contextual answers based on the specific lesson material
- Responses are stored in the database for future reference

## 🗄️ Database Schema

### Users Table
- `id`, `name`, `email`, `password`
- `role` (admin/student)
- Standard Laravel timestamps

### Lessons Table
- `id`, `title`, `content`
- `created_by` (foreign key to users)
- Standard Laravel timestamps

### Questions Table
- `id`, `question`, `ai_response`
- `lesson_id` (foreign key to lessons)
- `user_id` (foreign key to users)
- Standard Laravel timestamps

## 🧪 Testing

Run the test suite:
```bash
# Run all tests
php artisan test

# Run specific test types
php artisan test --filter=Feature
php artisan test --filter=Unit
```

## 🚀 Deployment

### Using Laravel Forge/Vapor:
1. Connect your repository
2. Set environment variables
3. Configure database
4. Deploy

### Manual Deployment:
1. Upload files to your server
2. Configure web server (Apache/Nginx)
3. Set proper file permissions
4. Run production commands:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## 🔧 Configuration

### Gemini API Settings
The AI service can be configured in `config/services.php`:
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'api_url' => env('GEMINI_API_URL'),
    'max_tokens' => 1000,
    'temperature' => 0.7,
]
```

## 🐛 Troubleshooting

### Common Issues:

**Gemini API not working:**
- Verify your API key is correct
- Check your internet connection
- Ensure you have API quota remaining

**Database connection errors:**
- Verify database credentials in `.env`
- Ensure database server is running
- Check database permissions

**Frontend not loading:**
- Run `npm run build` or `npm run dev`
- Clear browser cache
- Check for JavaScript console errors

## 📝 API Endpoints

### Authentication
- `POST /login` - User login
- `POST /logout` - User logout

### Lessons (Admin)
- `GET /admin/lessons` - List all lessons
- `POST /admin/lessons` - Create new lesson
- `PUT /admin/lessons/{id}` - Update lesson
- `DELETE /admin/lessons/{id}` - Delete lesson

### Student Interface
- `GET /lessons` - List available lessons
- `GET /lessons/{id}` - View specific lesson
- `POST /lessons/{id}/questions` - Ask question about lesson

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request
