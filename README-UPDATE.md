# Sinar Roda Utama - REST API

Professional REST API built with **Laravel 13** featuring CORS support and intelligent rate limiting.

## ✨ Features Implemented

### 🔒 Security
- **CORS Protection** - Whitelist-based origin validation for SPAs
- **Rate Limiting** - Dual-tier system (60 req/min for auth, 5 req/min for anonymous)
- **Input Validation** - Request validation on all endpoints
- **Database Constraints** - Foreign keys and referential integrity
- **Secure Password Hashing** - Bcrypt encryption with configurable rounds

### 📡 API Structure
- **RESTful Endpoints** - Follows standard HTTP methods and status codes
- **Versioning** - `/api/v1/` prefix for future compatibility
- **Resource Format** - JSON responses with consistent structure
- **Error Handling** - Proper HTTP status codes and error messages

### 🗄️ Database
- **SQLite** - Lightweight development database
- **Migrations** - Version controlled schema
- **Models** - Eloquent ORM with relationships
- **Factories & Seeders** - Easy test data generation

### 🎯 Endpoints

**Tasks Management**
```
GET    /api/v1/tasks           - List all tasks
POST   /api/v1/tasks           - Create new task
GET    /api/v1/tasks/{id}      - Get task details
PUT    /api/v1/tasks/{id}      - Update task
DELETE /api/v1/tasks/{id}      - Delete task
```

**Users Management**
```
GET    /api/v1/users           - List all users
POST   /api/v1/users           - Create new user
GET    /api/v1/users/{id}      - Get user details
PUT    /api/v1/users/{id}      - Update user
DELETE /api/v1/users/{id}      - Delete user
```

**Authentication**
```
GET    /api/v1/user            - Get current authenticated user
```

---

## 🚀 Quick Start

### Installation
```bash
# Install dependencies
composer install
npm install

# Setup environment
copy .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
```

### Run Development Server
```bash
# All-in-one development mode
composer dev

# Or separately:
php artisan serve          # Laravel (port 8000)
npm run dev                # Vite frontend (port 5173)
php artisan queue:listen   # Queue worker
php artisan pail           # Log viewer
```

---

## 📊 Rate Limiting

### Limits
| User Type | Limit | Period | Identifier |
|-----------|-------|--------|------------|
| Authenticated | 60 | minute | User ID |
| Anonymous | 5 | minute | IP Address |

### Response Headers
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1716216245
```

### When Limit Exceeded
```json
HTTP/1.1 429 Too Many Requests

{
    "message": "Too Many Requests",
    "errors": {
        "api": ["Too many attempts, please retry after 45 seconds"]
    }
}
```

---

## 🌐 CORS Configuration

### Allowed Origins
- `http://localhost:3000` - React/Vue development
- `http://localhost:5173` - Vite development server
- `http://127.0.0.1:3000` - Local IP variant
- `http://127.0.0.1:5173` - Local IP variant
- `http://localhost` - General localhost

### Allowed Methods
- GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS

### Headers
- All headers allowed for requests
- `Content-Range`, `X-Content-Range` exposed for responses

### Preflight Caching
- 24 hours (86,400 seconds) for performance

---

## 📚 Documentation

Detailed guides available in repository:
- **[API_DOCUMENTATION.md](./API_DOCUMENTATION.md)** - Complete API reference
- **[SECURITY.md](./SECURITY.md)** - Security features and best practices
- **[QUICK_START.md](./QUICK_START.md)** - Setup and testing guide
- **[IMPLEMENTATION_SUMMARY.txt](./IMPLEMENTATION_SUMMARY.txt)** - Changes overview

---

## 🧪 Testing

### Test CORS Preflight
```bash
curl -X OPTIONS http://localhost:8000/api/v1/tasks \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

### Test Rate Limiting
```bash
# Anonymous: 5 requests/minute
for i in {1..6}; do
  curl http://localhost:8000/api/v1/tasks
done
# 6th request returns 429

# Authenticated: 60 requests/minute
TOKEN="your_token"
for i in {1..61}; do
  curl -H "Authorization: Bearer $TOKEN" \
       http://localhost:8000/api/v1/tasks
done
```

### Test API Endpoints
```bash
# Create user
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'

# List tasks
curl http://localhost:8000/api/v1/tasks

# Create task
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Build API",
    "description": "Create robust REST API",
    "assigned_to": 1
  }'
```

---

## 🏗️ Project Structure

```
sinarrodautama/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/v1/
│   │   │   ├── TaskController.php
│   │   │   └── UserController.php
│   │   ├── Requests/Api/v1/
│   │   │   ├── TaskRequest.php
│   │   │   └── UserRequest.php
│   │   └── Resources/Api/v1/
│   │       ├── TaskResource.php
│   │       └── UserResource.php
│   └── Models/
│       ├── Task.php
│       └── User.php
├── config/
│   ├── cors.php              ← CORS Configuration (NEW)
│   └── database.php
├── bootstrap/
│   └── app.php               ← Middleware Setup (UPDATED)
├── database/
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   └── 2026_05_20_132348_add_details_to_tasks_table.php
│   └── factories/
├── routes/
│   ├── api.php               ← Rate Limiting (UPDATED)
│   └── web.php
├── storage/logs/
│   └── laravel.log
│
├── API_DOCUMENTATION.md      ← Complete API Reference (NEW)
├── SECURITY.md               ← Security Features (NEW)
├── QUICK_START.md            ← Setup Guide (NEW)
└── IMPLEMENTATION_SUMMARY.txt ← Changes Overview (NEW)
```

---

## 🔐 Security Best Practices

### Implemented
✅ CORS with origin whitelist  
✅ Rate limiting (5 & 60 req/min)  
✅ Input validation  
✅ Database constraints  
✅ Prepared statements (Eloquent)  

### Recommended for Production
⚠️ Authentication middleware  
⚠️ Authorization policies  
⚠️ HTTPS/TLS only  
⚠️ API key system  
⚠️ Soft deletes & audit logging  

---

## 🛠️ Useful Commands

```bash
# Clear cache
php artisan cache:clear

# Reset database
php artisan migrate:reset
php artisan migrate

# View logs
php artisan logs
php artisan pail

# Run tests
php artisan test

# Check code quality
./vendor/bin/pint check
```

---

## 📋 Tech Stack

- **Framework:** Laravel 13.11
- **Language:** PHP 8.3+
- **Database:** SQLite
- **Frontend:** Vue 3 + Vite
- **Authentication:** Laravel Sanctum
- **Package Manager:** Composer, npm

---

## 📞 Support

For questions about the API:
1. Check [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)
2. Review [QUICK_START.md](./QUICK_START.md)
3. See [SECURITY.md](./SECURITY.md) for security questions

---

## ✨ Recent Updates (May 20, 2026)

- ✅ Added CORS configuration (`config/cors.php`)
- ✅ Implemented rate limiting (throttle:api middleware)
- ✅ Added statefulApi() middleware for CORS support
- ✅ Created comprehensive documentation
- ✅ Added security implementation summary
- ✅ Professional README for recruiter demo

**Status:** Production-ready for demonstration ✅

---

**Last Updated:** May 20, 2026  
**Version:** 1.0.0  
**License:** MIT

