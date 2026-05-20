═══════════════════════════════════════════════════════════════════════════
                      RECRUITER DEMO CHECKLIST
                    Rate Limiting & CORS Implementation
═══════════════════════════════════════════════════════════════════════════

📅 Date: May 20, 2026
👨‍💼 Presenter: [Your Name]
🎯 Duration: 15-20 minutes
📊 Project: Sinar Roda Utama - Professional REST API

═══════════════════════════════════════════════════════════════════════════

✅ PRE-DEMO PREPARATION (15 minutes before)

□ Clone/prepare project directory
  $ cd C:\Users\bacht\sinarrodautama

□ Verify all dependencies installed
  $ composer install
  $ npm install

□ Start the servers
  Terminal 1: composer dev
  Terminal 2 (standby): npm run build

□ Have documentation ready
  • API_DOCUMENTATION.md - Browser tab or printed
  • QUICK_START.md - Reference guide
  • SECURITY.md - For Q&A

□ Test sample API calls (paste-ready)
  • User creation
  • Task listing
  • Rate limit test

□ Browser dev tools opened and Ready
  • Network tab (to show CORS headers)
  • Console (to show any CORS errors if blocking it)
  • Application tab (for cookies if needed)

═══════════════════════════════════════════════════════════════════════════

1️⃣ OPENING STATEMENT (1 minute)

SCRIPT:
"This is a professional REST API built with Laravel 13. I've implemented
two critical security features that are essential for production APIs:
CORS protection and rate limiting. Let me demonstrate how they work."

✓ Show project folder structure
✓ Highlight new files created


2️⃣ CORS DEMONSTRATION (4 minutes)

Goal: Show that CORS prevents unauthorized domains from accessing API

STEP 1: Show Configuration
─────────────────────────────────────
Cmd:  $ code config/cors.php
      (or: cat config/cors.php)

Point Out:
✓ Whitelist of allowed origins (localhost:3000, localhost:5173)
✓ Credentials support (allows cookies/tokens)
✓ All HTTP methods allowed
✓ 24-hour preflight cache for performance

Say:
"This whitelist means only our frontend can communicate with this API.
Any other domain trying to access it will be blocked by the browser."


STEP 2: Test CORS Headers
─────────────────────────────────────
Cmd:  $ curl -X OPTIONS http://localhost:8000/api/v1/tasks \
            -H "Origin: http://localhost:3000" \
            -H "Access-Control-Request-Method: GET" \
            -v

Show Response Headers:
✓ Access-Control-Allow-Origin: http://localhost:3000
✓ Access-Control-Allow-Methods: *
✓ Access-Control-Allow-Headers: *
✓ Access-Control-Max-Age: 86400

Say:
"The browser sees these headers and says 'OK, this origin is allowed.'
Without these headers, the browser blocks the request automatically."


STEP 3: Show Route with Middleware
─────────────────────────────────────
Cmd:  $ code routes/api.php
      (or: cat routes/api.php | grep -A 5 "prefix")

Point Out:
✓ throttle:api middleware applied to all routes
✓ RateLimiter configuration code
✓ Eloquent relationships loading with 'with()'

═══════════════════════════════════════════════════════════════════════════

3️⃣ RATE LIMITING DEMONSTRATION (5 minutes)

Goal: Show how rate limiting protects API from abuse/DDoS attacks

STEP 1: Show Configuration
─────────────────────────────────────
Cmd:  $ code routes/api.php
      (or: Show the RateLimiter::for('api', ...) block)

Explain:
"Rate limiting has two tiers:
• Authenticated users: 60 requests per minute
  - Identified by their user ID from the auth token
  - Allows legitimate users to make many requests

• Anonymous users: 5 requests per minute
  - Identified by their IP address
  - Prevents brute force and DDoS attacks"

Visual:
┌─────────────────────────────────────┐
│ AUTHENTICATED USER                  │
│ 60 requests per minute (by user_id) │
├─────────────────────────────────────┤
│ ANONYMOUS USER                      │
│ 5 requests per minute (by IP)       │
└─────────────────────────────────────┘


STEP 2: Live Test - Anonymous User
─────────────────────────────────────
Cmd:  $ for i in {1..6}; do
        echo "Request $i:"
        curl http://localhost:8000/api/v1/tasks -I
        sleep 1
      done

Show Results:
Request 1: HTTP/1.1 200 OK ✓
Request 2: HTTP/1.1 200 OK ✓
Request 3: HTTP/1.1 200 OK ✓
Request 4: HTTP/1.1 200 OK ✓
Request 5: HTTP/1.1 200 OK ✓
Request 6: HTTP/1.1 429 Too Many Requests ❌

Response Headers for 429:
X-RateLimit-Limit: 5
X-RateLimit-Remaining: -1
Retry-After: 45

Say:
"After 5 requests, the API returns 429 Too Many Requests.
The Retry-After header tells the client when to retry.
This prevents brute force attacks and DDoS attempts."


STEP 3: Explain Smart Limits
─────────────────────────────────────
Say:
"Notice how the limit is smarter than just blocking all requests.
If a user authenticates, they get 60 requests per minute instead of 5.
This is fair - legitimate users aren't throttled as harshly."

Code Reference:
Limit::perMinute(60)->by($request->user()->id)    // Auth: 60/min
Limit::perMinute(5)->by($request->ip())           // Anon: 5/min


STEP 4: Show Response Format
─────────────────────────────────────
When Limit Exceeded:
{
    "message": "Too Many Requests",
    "errors": {
        "api": ["Too many attempts, please retry after 45 seconds"]
    }
}

Say:
"The API returns a proper JSON error response with a clear message.
Frontend developers can use this to show users a friendly error message
instead of a cryptic error."

═══════════════════════════════════════════════════════════════════════════

4️⃣ SHOW DOCUMENTATION (3 minutes)

Goal: Demonstrate thinking about maintainability and teamwork

SHOW FILES:
─────────────────────────────────────
1. API_DOCUMENTATION.md
   ✓ Complete API reference
   ✓ Example curl commands
   ✓ All endpoints documented
   ✓ Production deployment guide

2. SECURITY.md
   ✓ Security features checklist
   ✓ Testing procedures
   ✓ Compliance checklist
   ✓ Production recommendations

3. QUICK_START.md
   ✓ Installation steps
   ✓ Running the application
   ✓ API testing guide
   ✓ Troubleshooting

Say:
"I believe in writing code that other developers can understand.
These documents make it easy for a team member to jump in and
contribute without needing to ask me questions."

═══════════════════════════════════════════════════════════════════════════

5️⃣ CODE QUALITY POINTS (2 minutes)

Highlight These Best Practices:

☑ PSR-4 Autoloading
  "Follow PSR-4 standards for clean code structure"

☑ Dependency Injection
  "Controllers receive dependencies, not hardcoded"

☑ Resource Classes
  "API responses are consistent and centralized"

☑ Request Validation
  "Input validated before business logic"

☑ Model Relationships
  "Database relationships clearly defined in models"

☑ Configuration Management
  "Configuration separate from code (config/cors.php)"


CODE EXAMPLE:
─────────────────────────────────────
Show TaskController and explain:
✓ Type hints on function parameters
✓ Return type declarations
✓ Proper HTTP methods usage
✓ Clean controller thin (business logic elsewhere)

═══════════════════════════════════════════════════════════════════════════

6️⃣ QUESTIONS & ANSWERS PREPARATION

Q: "Why did you implement CORS?"
A: "CORS enables secure communication between frontend (at localhost:3000)
   and backend API. The whitelist approach means only authorized origins
   can access the API, preventing CSRF attacks."

Q: "How does rate limiting help?"
A: "It prevents abuse - someone can't spam requests to crack passwords
   or overwhelm the server. Legitimate users get higher limits based on
   their identity (60/min), while anonymous requests are limited to 5/min."

Q: "What happens in production?"
A: "In production, we'd:
   1. Update CORS to allow production domains only
   2. Increase limits if needed (default is conservative)
   3. Use Redis for distributed rate limiting
   4. Add authentication middleware to protect ALL endpoints
   5. Implement authorization policies"

Q: "How do you test rate limiting between deploys?"
A: "The tests are simple - just send 6 requests and verify the 6th
   returns 429. Automated tests would verify response headers too."

Q: "What about database security?"
A: "Using Eloquent ORM prevents SQL injection. We use prepared statements
   automatically. Bcrypt hashes passwords, and foreign keys enforce
   referential integrity."

═══════════════════════════════════════════════════════════════════════════

7️⃣ CLOSING STATEMENT (1 minute)

SCRIPT:
"I've implemented professional-grade security features that would be
expected in a production API. The rate limiting prevents abuse, CORS
protects against unauthorized access, and the documentation shows I
care about code maintainability.

Future improvements would include:
• Authentication middleware on all endpoints
• Authorization policies (users can only see their own tasks)
• Audit logging to track changes
• Soft deletes for data retention

But the foundation is solid and ready for a real application."

═══════════════════════════════════════════════════════════════════════════

TALKING POINTS TO EMPHASIZE

1. "CORS is not optional - it's a critical security feature"
2. "Rate limiting at 5 req/min for anonymous, 60 for auth shows strategic thinking"
3. "Documentation is as important as code"
4. "Used Laravel best practices throughout"
5. "Thought about production deployment"
6. "Can explain EVERY decision I made"

═══════════════════════════════════════════════════════════════════════════

THINGS TO AVOID SAYING

✗ "I just copied some code from Stack Overflow"
✗ "I'm not sure how CORS works"
✗ "I didn't have time to document it"
✗ "It probably has security vulnerabilities"
✗ "I don't remember why I did it this way"

INSTEAD SAY:
✓ "I implemented CORS following Laravel and W3C standards"
✓ "CORS works by validating the Origin header against a whitelist"
✓ "I documented the API for maintainability"
✓ "Security was a primary concern in my design"
✓ "Here's exactly why I made this decision..."

═══════════════════════════════════════════════════════════════════════════

EMERGENCY COMMANDS (If something breaks)

Restart servers:
$ composer dev

Clear cache:
$ php artisan cache:clear
$ php artisan config:clear

Reset database:
$ php artisan migrate:reset
$ php artisan migrate

View logs:
$ php artisan logs

═══════════════════════════════════════════════════════════════════════════

QUICK DEMO SCRIPT (Read from here if nervous)

"Let me show you how CORS and rate limiting work together to provide
a secure API.

First, here's the CORS configuration. It whitelists specific origins
like localhost:3000 where our frontend runs. Only those origins can
access this API.

Next, rate limiting prevents abuse. Authenticated users get 60 requests
per minute, anonymous users get 5. Let me demonstrate..."

[Show curl test]

"As you can see, after 5 requests, the 6th returns 429. The browser
gets a retry-after header telling it when to try again. This is a
professional implementation you'd see in production APIs."

═══════════════════════════════════════════════════════════════════════════

SUCCESS CRITERIA

You succeeded if recruiter says:
✓ "This shows good security thinking"
✓ "You clearly understand the concepts"
✓ "The code is well-organized"
✓ "I like your documentation approach"
✓ "When can you start?" 😄

═══════════════════════════════════════════════════════════════════════════

GOOD LUCK! 🚀

Remember:
- Speak confidently
- Show, don't tell
- Answer questions directly
- Ask for feedback at the end
- Follow up with your GitHub link

═══════════════════════════════════════════════════════════════════════════

