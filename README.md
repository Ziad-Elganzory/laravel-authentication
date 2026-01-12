# Laravel JWT Authentication

A mini project demonstrating **JWT (JSON Web Token) authentication** implementation in Laravel. This project serves as an educational resource for understanding how JWT-based authentication works and how to integrate it into a Laravel application.

## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [API Endpoints](#api-endpoints)
- [How JWT Authentication Works](#how-jwt-authentication-works)
- [Project Structure](#project-structure)
- [Testing the API](#testing-the-api)
- [License](#license)

## 📖 About the Project

This project demonstrates a complete JWT authentication system built with Laravel. It includes user registration, login, token refresh, profile retrieval, and logout functionality. The implementation follows best practices and uses a service-based architecture for clean, maintainable code.

**Purpose:** Educational resource to understand and implement JWT authentication in Laravel applications.

## ✨ Features

- ✅ User Registration with validation
- ✅ User Login with JWT token generation
- ✅ Token-based authentication
- ✅ Get authenticated user profile
- ✅ Token refresh mechanism
- ✅ User logout (token invalidation)
- ✅ Service-based architecture
- ✅ Standardized API responses
- ✅ Interactive Swagger API documentation
- ✅ Comprehensive error handling

## 🛠 Technologies Used

- **Laravel 12** - PHP Framework
- **PHP 8.2+** - Programming Language
- **MySQL** - Database
- **JWT (php-open-source-saver/jwt-auth)** - JSON Web Token Authentication
- **L5-Swagger** - API Documentation
- **Sanctum** - API Token Authentication (Laravel default)

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-auth
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Generate JWT secret**
   ```bash
   php artisan jwt:secret
   ```

6. **Configure database**
   
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_auth
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Generate Swagger documentation**
   ```bash
   php artisan l5-swagger:generate
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

### Configuration

#### Environment Variables

Key environment variables for JWT authentication:

```env
# Application URL (used for Swagger documentation)
APP_URL=http://localhost:8000

# Swagger API Documentation Host
L5_SWAGGER_CONST_HOST="${APP_URL}/api"

# JWT Configuration (automatically added by jwt:secret command)
JWT_SECRET=your-secret-key
JWT_TTL=60  # Token lifetime in minutes
JWT_REFRESH_TTL=20160  # Refresh token lifetime in minutes
```

## 📚 API Documentation

Interactive API documentation is available via Swagger UI:

**Access:** [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

The Swagger interface allows you to:
- View all available endpoints
- See request/response schemas
- Test API endpoints directly from the browser
- Authenticate using JWT tokens

## 🔌 API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register a new user | No |
| POST | `/api/auth/login` | Login and get JWT token | No |
| GET | `/api/auth/me` | Get authenticated user profile | Yes |
| POST | `/api/auth/logout` | Logout (invalidate token) | Yes |
| POST | `/api/auth/refresh` | Refresh JWT token | Yes |

### Example Requests

#### 1. Register a New User

```bash
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-01-12T10:00:00.000000Z",
      "updated_at": "2026-01-12T10:00:00.000000Z"
    }
  }
}
```

#### 2. Login

```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### 3. Get User Profile

```bash
GET /api/auth/me
Authorization: Bearer <your-jwt-token>
```

**Response:**
```json
{
  "success": true,
  "message": "User profile retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-01-12T10:00:00.000000Z",
      "updated_at": "2026-01-12T10:00:00.000000Z"
    }
  }
}
```

#### 4. Refresh Token

```bash
POST /api/auth/refresh
Authorization: Bearer <your-jwt-token>
```

**Response:**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### 5. Logout

```bash
POST /api/auth/logout
Authorization: Bearer <your-jwt-token>
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": null
}
```

## 🔐 How JWT Authentication Works

### The JWT Flow

1. **Registration/Login:**
   - User provides credentials
   - Server validates credentials
   - Server generates a JWT token
   - Token is returned to the client

2. **Token Structure:**
   ```
   Header.Payload.Signature
   ```
   - **Header:** Token type and hashing algorithm
   - **Payload:** User data and claims (user ID, expiration, etc.)
   - **Signature:** Encrypted hash to verify token authenticity

3. **Protected Requests:**
   - Client includes token in Authorization header: `Bearer <token>`
   - Server validates token signature
   - Server extracts user information from token
   - Request is processed if token is valid

4. **Token Refresh:**
   - Before token expires, client requests new token
   - Server issues new token if current token is valid
   - Client uses new token for subsequent requests

5. **Logout:**
   - Token is invalidated on the server
   - Client removes token from storage

### Security Features

- Tokens are signed with a secret key
- Tokens have expiration times (TTL)
- Tokens can be invalidated server-side
- Refresh tokens extend session without re-login

## 📁 Project Structure

```
laravel-auth/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php    # Authentication endpoints
│   │   │   └── Controller.php        # Base controller with Swagger config
│   │   └── Requests/
│   │       └── Auth/
│   │           ├── LoginRequest.php     # Login validation
│   │           └── RegisterRequest.php  # Registration validation
│   ├── Models/
│   │   └── User.php                  # User model
│   └── Services/
│       ├── ApiResponseService.php    # Standardized API responses
│       └── AuthService.php           # JWT authentication logic
├── config/
│   ├── jwt.php                       # JWT configuration
│   └── l5-swagger.php                # Swagger configuration
├── database/
│   └── migrations/                   # Database migrations
├── routes/
│   └── api.php                       # API routes
└── storage/
    └── api-docs/                     # Generated Swagger documentation
```

### Key Components

#### AuthService
Handles all JWT authentication logic:
- User registration
- User login
- Token validation
- Token refresh
- User logout
- Retrieve authenticated user

#### ApiResponseService
Provides standardized JSON responses:
- Success responses
- Error responses
- Validation error responses
- Unauthorized responses

## 🧪 Testing the API

### Using cURL

```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Get Profile (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer TOKEN"
```

### Using Postman

1. Import the Swagger JSON from `storage/api-docs/api-docs.json`
2. Or manually create requests following the endpoint examples above
3. For protected routes, add the token in Authorization tab:
   - Type: Bearer Token
   - Token: `<your-jwt-token>`

### Using Swagger UI

1. Navigate to `http://localhost:8000/api/documentation`
2. Click on an endpoint to expand it
3. Click "Try it out"
4. Fill in the request body
5. Click "Execute"
6. For protected endpoints, click "Authorize" and enter: `Bearer <token>`

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---