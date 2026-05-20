# API Documentation - Sinar Roda Utama

## Overview
Robust REST API built with Laravel 13 featuring CORS support and intelligent rate limiting.

---

## 🔒 Security Features

### 1. **CORS (Cross-Origin Resource Sharing)**
Properly configured CORS middleware allows safe cross-origin requests from whitelisted origins.

**Configuration:** `config/cors.php`
- **Allowed Origins:**
  - `http://localhost:3000` (Development frontend)
  - `http://localhost:5173` (Vite dev server)
  - Pattern-based: `http://localhost.*` and `http://127.0.0.1.*`

- **Allowed Methods:** All HTTP methods (GET, POST, PUT, PATCH, DELETE)
- **Exposed Headers:** Content-Range, X-Content-Range
- **Cache:** 86400 seconds (24 hours) for preflight requests
- **Credentials:** Allowed (statefulApi enabled)

**Technical Implementation:**
```php
// Middleware automatically applied via trustProxies
$middleware->statefulApi();  // Handles CORS for SPA
$middleware->trustProxies(at: '*');  // For production servers
```

---

### 2. **Rate Limiting**
Intelligent rate limiting prevents abuse and DDoS attacks.

**Configuration:** `routes/api.php`

#### Limits:
- **Authenticated Users:** 60 requests per minute
  - Identified by user ID from auth:sanctum token
  - Allows higher volume for legitimate users

- **Anonymous Requests:** 5 requests per minute
  - Identified by IP address
  - Prevents brute force attacks

**Implementation:**
```php
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(60)->by($request->user()->id)
        : Limit::perMinute(5)->by($request->ip());
});
```

**Response Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: <unix-timestamp>
```

**When Limit Exceeded (429 Too Many Requests):**
```json
{
    "message": "Too Many Requests",
    "errors": {
        "api": ["Too many attempts, please retry after 45 seconds"]
    }
}
```

---

## 📊 API Endpoints

All endpoints follow RESTful conventions and are versioned under `/api/v1`.

### Tasks API

| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|-----------|
| GET | `/api/v1/tasks` | List all tasks | 60 req/min |
| POST | `/api/v1/tasks` | Create new task | 60 req/min |
| GET | `/api/v1/tasks/{id}` | Get task details | 60 req/min |
| PUT | `/api/v1/tasks/{id}` | Update task | 60 req/min |
| PATCH | `/api/v1/tasks/{id}` | Partial update | 60 req/min |
| DELETE | `/api/v1/tasks/{id}` | Delete task | 60 req/min |

### Users API

| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|-----------|
| GET | `/api/v1/users` | List all users | 60 req/min |
| POST | `/api/v1/users` | Create new user | 5 req/min (anon) |
| GET | `/api/v1/users/{id}` | Get user details | 60 req/min |
| PUT | `/api/v1/users/{id}` | Update user | 60 req/min |
| PATCH | `/api/v1/users/{id}` | Partial update | 60 req/min |
| DELETE | `/api/v1/users/{id}` | Delete user | 60 req/min |

---

## 🔑 Example Usage

### CORS Request from Frontend (Vite):
```javascript
// Automatically respects CORS configuration
fetch('http://localhost:8000/api/v1/tasks', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + sanctumToken
    },
    credentials: 'include'  // Sends cookies with request
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => {
    if (error.status === 429) {
        console.error('Rate limit exceeded. Please try again later.');
    }
});
```

### Handling Rate Limit Errors:
```javascript
async function apiCall(endpoint) {
    const response = await fetch(endpoint);
    
    if (response.status === 429) {
        const retryAfter = response.headers.get('Retry-After');
        console.log(`Retry after ${retryAfter} seconds`);
        // Implement exponential backoff
    }
    
    return response.json();
}
```

---

## 🛠️ Configuration Details

### Environment Variables
```env
APP_DEBUG=true
APP_URL=http://localhost
CACHE_DRIVER=database  # Rate limiting uses cache
SESSION_DRIVER=database
```

### Cache Configuration
Rate limiting uses the cache driver specified in `config/cache.php`. For development, database cache is used to persist rate limit counters.

---

## 📈 Monitoring & Logs

### Rate Limit Logs Location
```
storage/logs/laravel.log
```

### View Active Rate Limits
```bash
php artisan tinker
# Or query cache directly
Cache::get('rate-limit:api:user-{id}')
```

---

## 🚀 Production Considerations

For production deployment:

1. **Update CORS Origins** in `config/cors.php`
   ```php
   'allowed_origins' => [
       'https://yourdomain.com',
       'https://app.yourdomain.com',
   ],
   ```

2. **Adjust Rate Limits** based on expected traffic
   ```php
   Limit::perMinute(100)->by($request->user()->id)  // Higher for prod
   ```

3. **Use Redis Cache** for distributed rate limiting
   ```env
   CACHE_DRIVER=redis
   ```

4. **Enable HTTPS** and add HSTS headers
   ```php
   Route::middleware('https.only')->group(...)
   ```

5. **Monitor Rate Limit Hits**
   ```bash
   php artisan logs:view
   ```

---

## ✅ Testing Rate Limits

### Test with curl:
```bash
# First 5 requests from anon IP succeed
for i in {1..5}; do
    curl -i http://localhost:8000/api/v1/users
done

# 6th request returns 429
curl -i http://localhost:8000/api/v1/users
# Response: 429 Too Many Requests
```

### Test with auth token:
```bash
curl -i -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/tasks
# Allows 60 requests per minute
```

---

## 📝 Migration Notes

- CORS configuration automatically loaded from `config/cors.php`
- Rate limiting is global for all `/api/v1/*` routes
- Stateful API enables cookie-based authentication support
- Changes to rate limits require no database migration

---

**Last Updated:** May 20, 2026  
**Version:** 1.0  
**Framework:** Laravel 13.11

