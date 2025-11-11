# VoteHubPH Backend API

Laravel 11 REST API backend for VoteHubPH - A comprehensive voting platform for the Philippines.

## 🚀 Tech Stack

- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Database**: PostgreSQL (Neon)
- **Authentication**: NextAuth.js Session Integration
- **Image Storage**: Cloudinary
- **Email**: SMTP (Gmail)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL client libraries
- Node.js (for asset compilation)

## 🛠️ Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/NOTMORSE-PROG/VoteHubPH_Backend.git
cd VoteHubPH_Backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure your `.env` file with your database, Cloudinary, and mail credentials. See `SETUP.md` for detailed instructions.

### 4. Database Setup

Run migrations:

```bash
php artisan migrate
```

Seed location data:

```bash
php artisan db:seed --class=PhilippineLocationsSeeder
```

### 5. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 📚 API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/send-otp` - Send OTP for email verification
- `POST /api/auth/verify-otp` - Verify OTP and create account
- `POST /api/auth/callback/google` - Google OAuth callback

### User Management
- `GET /api/user/profile` - Get authenticated user profile
- `PUT /api/user/update` - Update user profile (name, image)

### Posts (Candidates)
- `GET /api/posts/approved` - Get all approved posts
- `GET /api/posts/{id}` - Get post details
- `GET /api/posts/my-posts` - Get current user's posts
- `POST /api/posts` - Create new post
- `PUT /api/posts/{id}` - Update post

### Interactions
- `POST /api/posts/{id}/vote` - Vote for a candidate
- `POST /api/posts/{id}/comments` - Create comment
- `POST /api/comments/{id}/like` - Like a comment

### Locations
- `GET /api/locations/regions` - Get all regions
- `GET /api/locations/cities` - Get cities (optional: ?region_id={id})
- `GET /api/locations/districts` - Get districts (optional: ?city_id={id})
- `GET /api/locations/barangays` - Get barangays (optional: ?city_id={id}&district_id={id})

### Party Lists
- `GET /api/admin/partylists` - Get all party lists
- `POST /api/admin/partylists` - Create party list
- `POST /api/admin/partylists/{id}/members` - Add member to party list

### Admin
- `GET /api/admin/posts` - Get all posts for moderation
- `POST /api/admin/posts/{id}/approve` - Approve post
- `POST /api/admin/posts/{id}/reject` - Reject post

## 🔐 Authentication

The backend uses NextAuth.js session tokens for authentication. The middleware `NextAuthSession` validates sessions from the Next.js frontend.

For API requests, include:
- Session cookie: `next-auth.session-token`
- Or header: `X-User-Id` (fallback for JWT sessions)

## 🗄️ Database Schema

### Key Tables
- `User` - User accounts (NextAuth schema)
- `Account` - OAuth provider accounts
- `Session` - User sessions
- `posts` - Candidate posts
- `votes` - User votes
- `comments` - User comments
- `comment_likes` - Comment likes
- `party_lists` - Party list organizations
- `party_list_members` - Party list members
- `regions`, `cities`, `districts`, `barangays` - Location data
- `otps` - OTP codes for email verification

## 📦 Deployment

### Railway

1. Connect your GitHub repository
2. Add environment variables in Railway dashboard
3. Set build command: `composer install --optimize-autoloader --no-dev`
4. Set start command: `php artisan serve --host=0.0.0.0 --port=$PORT`

### Environment Variables Required

- `APP_KEY` - Laravel application key
- `APP_URL` - Your production URL
- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`
- `MAIL_*` - SMTP configuration

## 📖 Documentation

For detailed setup instructions, see [SETUP.md](./SETUP.md)

## 📝 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
