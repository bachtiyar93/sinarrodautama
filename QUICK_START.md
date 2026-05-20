# Quick Start Guide

## Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+ & npm
- SQLite (bundled with PHP)

---

## 🚀 Installation & Setup

### 1. Install PHP Dependencies
```bash
cd C:\Users\bacht\sinarrodautama
composer install
```

### 2. Setup Environment
```bash
# Copy .env configuration
copy .env.example .env

# Generate app key
php artisan key:generate
```

### 3. Setup Database
```bash
# Run migrations
php artisan migrate

# (Optional) Seed dummy data
php artisan db:seed
```

### 4. Install Frontend Dependencies
```bash
npm install
```

---

## 🎯 Running the Application

### Development Mode
```bash
composer dev
```
This starts:
- Laravel development server (http://localhost:8000)
- Queue listener for jobs
- Pail for logging
- Vite dev server for frontend (http://localhost:5173)

### Or Run Separately in Different Terminals

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```
Running Laravel on [http://localhost:8000](http://localhost:8000)

**Terminal 2 - Frontend (Vite):**
```bash
npm run dev
```
Running Vite on [http://localhost:5173](http://localhost:5173)

**Terminal 3 - Queue (Optional):**
```bash
php artisan queue:listen
```

**Terminal 4 - Logs (Optional):**
```bash
php artisan pail
```

---

## 📡 API Testing

### Using cURL

**Test CORS - GET Tasks:**
```bash
curl -X GET http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json"
```

**Create User:**
```bash
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Create Task:**
```bash
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Build API",
    "description": "Create REST API with CORS and rate limiting",
    "assigned_to": 1,
    "start_date": "2026-05-20",
    "end_date": "2026-05-25"
  }'
```

**Update Task:**
```bash
curl -X PUT http://localhost:8000/api/v1/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Build API (Updated)",
    "is_completed": true
  }'
```

**Delete Task:**
```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/1
```

### Using Postman/Insomnia
1. Import the routes from `routes/api.php`
2. Set base URL to `http://localhost:8000/api/v1`
3. Add headers:
   - `Content-Type: application/json`
   - `Accept: application/json`

---

## 🔒 Test Rate Limiting

### Anonymous User (5 requests/minute)
```bash
for i in {1..6}; do
  curl http://localhost:8000/api/v1/tasks
  echo "Request $i - $(date)"
  sleep 1
done
# Request 6 should return: 429 Too Many Requests
```

### Test CORS Preflight
```bash
curl -X OPTIONS http://localhost:8000/api/v1/tasks \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v
```

Expected response headers:
```
HTTP/1.1 200 OK
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: *
Access-Control-Allow-Headers: *
Access-Control-Max-Age: 86400
```

---

## 📊 Database Structure

### Tables
- **users** - User accounts
  - id (primary key)
  - name (varchar)
  - email (varchar, unique)
  - password (hashed)
  - email_verified_at (nullable)
  - created_at, updated_at

- **tasks** - Tasks/To-do items
  - id (primary key)
  - title (varchar)
  - description (text, nullable)
  - is_completed (boolean)
  - start_date (date, nullable)
  - end_date (date, nullable)
  - assigned_to (foreign key → users.id)
  - created_at, updated_at

- **cache** - Rate limiting & caching
- **job_batches** - Queue management
- **jobs** - Background jobs
- **personal_access_tokens** - Sanctum authentication

---

## 🛠️ Common Commands

### Database
```bash
# Create tables
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Seed test data
php artisan db:seed
```

### Cache
```bash
# Clear all cache
php artisan cache:clear

# Clear specific cache
php artisan cache:forget rate-limit:*
```

### Development
```bash
# Run tests
php artisan test

# Code quality check
./vendor/bin/pint check

# Clear config cache
php artisan config:clear
```

### Production
```bash
# Compile assets
npm run build

# Build Docker image (if using containers)
docker build -t sinar-roda-api .
```

---

## 📁 Project Structure

```
sinarrodautama/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/v1/       # API Controllers
│   │   ├── Requests/Api/v1/          # Request validation
│   │   └── Resources/Api/v1/         # API response formatting
│   └── Models/
│       ├── Task.php                  # Task model
│       └── User.php                  # User model
├── bootstrap/
│   └── app.php                       # App bootstrap & middleware
├── config/
│   ├── cors.php                      # CORS configuration
│   └── database.php                  # Database configuration
├── database/
│   ├── migrations/                   # Database migrations
│   └── factories/                    # Model factories
├── routes/
│   ├── api.php                       # API routes with rate limiting
│   └── web.php                       # Web routes
├── storage/
│   └── logs/                         # Application logs
└── tests/
    ├── Feature/                      # Feature tests
    └── Unit/                         # Unit tests
```

---

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Find process using port 8000
netstat -ano | findstr :8000

# Kill process
taskkill /PID <PID> /F

# Or use different port
php artisan serve --port=8001
```

### CORS Errors in Browser
```
Access to XMLHttpRequest... blocked by CORS policy
```
**Solution:** Ensure frontend origin is in `config/cors.php`

### Rate Limit Not Working
```bash
# Clear cache
php artisan cache:clear

# Check cache driver in .env
# Should be: CACHE_DRIVER=database
```

### Database Lock (SQLite)
```bash
# Remove lock file
rm -f database/database.sqlite-shm
rm -f database/database.sqlite-wal

# Then retry migrations
php artisan migrate:fresh
```

---

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [API Documentation](./API_DOCUMENTATION.md)
- [Security Features](./SECURITY.md)
- [Migrations](./database/migrations/)

---

## ✅ Verification Checklist

Before presenting to recruiter:

- [ ] `composer install` runs without errors
- [ ] `php artisan migrate` creates database
- [ ] `npm install` installs dependencies
- [ ] `composer dev` starts all services
- [ ] API endpoints respond with proper JSON
- [ ] CORS headers present in response
- [ ] Rate limiting returns 429 after limit
- [ ] All database constraints work
- [ ] Readme displays correctly on GitHub

---

**Last Updated:** May 20, 2026  
**Version:** 1.0  
**Status:** Ready for Demo ✅

