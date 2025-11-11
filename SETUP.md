# VoteHubPH Backend Setup Guide

Complete setup guide for VoteHubPH backend API.

## 📋 Prerequisites

### Required Software
- **PHP** 8.2+ ([Download](https://www.php.net/downloads))
- **Composer** ([Download](https://getcomposer.org/))
- **PostgreSQL Client Libraries**
- **Git** ([Download](https://git-scm.com/))

### Required Accounts
- **Neon** - PostgreSQL database ([Sign up](https://neon.tech))
- **Cloudinary** - Image storage ([Sign up](https://cloudinary.com))
- **Gmail** - SMTP email (with App Password)

## 🗄️ Database Setup (Neon)

1. Create a new project on [Neon](https://neon.tech)
2. Copy your connection string (it looks like: `postgresql://user:password@host/database?sslmode=require`)
3. Save this for later use in environment variables

## ☁️ Cloudinary Setup

1. Sign up at [Cloudinary](https://cloudinary.com)
2. Go to Dashboard → Settings
3. Copy:
   - Cloud Name
   - API Key
   - API Secret
4. Create an upload preset (Settings → Upload → Upload presets)
   - Name: `votehubph_unsigned`
   - Signing mode: `Unsigned`
   - Folder: `votehubph`

## 📧 Gmail SMTP Setup

1. Go to your Google Account settings
2. Security → 2-Step Verification (enable if not enabled)
3. App passwords → Generate app password
4. Select "Mail" and "Other (Custom name)"
5. Name it "VoteHubPH"
6. Copy the 16-character password

## 🔧 Installation Steps

### 1. Clone and Install

```bash
git clone https://github.com/NOTMORSE-PROG/VoteHubPH_Backend.git
cd VoteHubPH_Backend
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME=VoteHubPH
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=your-neon-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="VoteHubPH"
```

### 3. Run Migrations

```bash
php artisan migrate
php artisan db:seed --class=PhilippineLocationsSeeder
```

### 4. Start Server

```bash
php artisan serve
```

Backend will run at `http://localhost:8000`

## 🚀 Deployment (Railway)

1. Connect GitHub repo to Railway
2. Add all environment variables
3. Set build command: `composer install --optimize-autoloader --no-dev`
4. Set start command: `php artisan serve --host=0.0.0.0 --port=$PORT`

## ✅ Verification

After setup, verify:

1. ✅ Backend API responds at `/api/posts/approved`
2. ✅ Can register new user
3. ✅ Can login with email/password
4. ✅ Images upload to Cloudinary

## 🆘 Troubleshooting

**PostgreSQL connection error:**
- Verify database credentials in `.env`
- Check if Neon database is active
- Ensure IP is whitelisted (if required)

**Cloudinary upload fails:**
- Verify API credentials
- Check upload preset is set to "Unsigned"

**Email not sending:**
- Verify Gmail app password (not regular password)
- Check 2-Step Verification is enabled
- Ensure `MAIL_FROM_ADDRESS` matches Gmail account

## 📞 Support

For issues or questions, please open an issue on GitHub.

