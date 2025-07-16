# Development Setup Guide

This guide will help you set up the Olympic Application API for local development.

## Prerequisites

### System Requirements
- **PHP**: 8.1 or later
- **Composer**: Latest version
- **Node.js**: 18.x or later
- **Database**: PostgreSQL 13+ (recommended) or MySQL 8+
- **Cache**: Redis (optional for development)
- **Git**: Latest version

### Development Tools (Recommended)
- **IDE**: VS Code, PhpStorm, or similar
- **API Testing**: Postman, Insomnia, or similar
- **Database GUI**: pgAdmin, TablePlus, or similar
- **Version Control**: Git with GUI client (optional)

## Installation

### 1. Clone the Repository
```bash
# Clone the repository
git clone https://github.com/your-username/olimpiapp-api.git
cd olimpiapp-api

# Create a new branch for your work
git checkout -b feature/your-feature-name
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Install development dependencies
composer install --dev
```

### 3. Environment Configuration
```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

#### PostgreSQL (Recommended)
```bash
# Create database
createdb olimpiapp_db

# Or using psql
psql -U postgres
CREATE DATABASE olimpiapp_db;
\q
```

#### MySQL (Alternative)
```bash
# Create database
mysql -u root -p
CREATE DATABASE olimpiapp_db;
exit
```

### 5. Configure Environment Variables
Edit the `.env` file with your local settings:

```env
APP_NAME="Olympic Application API"
APP_ENV=local
APP_KEY=base64:your_generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=olimpiapp_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@localhost"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 6. Database Migration and Seeding
```bash
# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Or run migrations with seeding in one command
php artisan migrate --seed
```

### 7. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set proper permissions (Linux/Mac)
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 8. Start Development Server
```bash
# Start the Laravel development server
php artisan serve

# Or specify a different port
php artisan serve --port=8080
```

Your API should now be accessible at `http://localhost:8000`

## Development Tools Setup

### VS Code Configuration
Create `.vscode/settings.json`:

```json
{
    "php.validate.executablePath": "/usr/bin/php",
    "php.suggest.basic": false,
    "editor.formatOnSave": true,
    "editor.codeActionsOnSave": {
        "source.fixAll": true
    },
    "files.associations": {
        "*.blade.php": "blade"
    },
    "emmet.includeLanguages": {
        "blade": "html"
    }
}
```

### Recommended VS Code Extensions
- PHP Intelephense
- Laravel Blade Snippets
- GitLens
- REST Client
- Thunder Client
- PHP DocBlocker

### PhpStorm Configuration
1. Set PHP interpreter to PHP 8.1+
2. Enable Laravel plugin
3. Configure code style to PSR-12
4. Set up debugging with Xdebug

## Testing Setup

### PHPUnit Configuration
The project comes with PHPUnit configured. Run tests:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthControllerTest.php

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### Test Database Setup
Create a separate test database:

```bash
# PostgreSQL
createdb olimpiapp_test_db

# MySQL
mysql -u root -p
CREATE DATABASE olimpiapp_test_db;
```

Add to your `.env.testing`:
```env
DB_CONNECTION=pgsql
DB_DATABASE=olimpiapp_test_db
```

### Frontend Testing
```bash
# Run JavaScript tests
npm test

# Run tests in watch mode
npm run test:watch
```

## API Documentation

### Generate API Documentation
```bash
# Install Scribe for API documentation
composer require knuckleswtf/scribe --dev

# Generate documentation
php artisan scribe:generate
```

### View API Documentation
Access the generated documentation at `http://localhost:8000/docs`

## Queue Workers (Development)

### Start Queue Worker
```bash
# Start queue worker in development
php artisan queue:work

# With specific options
php artisan queue:work --tries=3 --timeout=60
```

### Monitor Queue Jobs
```bash
# Check queue status
php artisan queue:monitor

# Clear failed jobs
php artisan queue:clear
```

## Database Management

### Migrations
```bash
# Create a new migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Refresh database (rollback + migrate)
php artisan migrate:refresh

# Refresh with seeding
php artisan migrate:refresh --seed
```

### Seeders
```bash
# Create a seeder
php artisan make:seeder ExampleSeeder

# Run specific seeder
php artisan db:seed --class=ExampleSeeder
```

### Model Factories
```bash
# Create a factory
php artisan make:factory ExampleFactory

# Use factory in tinker
php artisan tinker
User::factory(10)->create();
```

## Debugging

### Xdebug Setup
Install Xdebug and add to `php.ini`:
```ini
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=localhost
```

### Laravel Debugbar
```bash
# Install Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

# Publish config
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

### Telescope (Optional)
```bash
# Install Telescope
composer require laravel/telescope --dev

# Install Telescope
php artisan telescope:install

# Migrate
php artisan migrate
```

## Code Quality Tools

### PHP CS Fixer
```bash
# Install PHP CS Fixer
composer require friendsofphp/php-cs-fixer --dev

# Create configuration
# .php-cs-fixer.dist.php file should be created

# Run fixer
vendor/bin/php-cs-fixer fix
```

### PHPStan
```bash
# Install PHPStan
composer require phpstan/phpstan --dev

# Run analysis
vendor/bin/phpstan analyse
```

### Larastan
```bash
# Install Larastan (Laravel-specific PHPStan)
composer require nunomaduro/larastan:^2.0 --dev

# Run analysis
vendor/bin/phpstan analyse
```

## Git Workflow

### Branch Naming Convention
- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Critical fixes
- `chore/description` - Maintenance tasks

### Commit Message Convention
Follow conventional commits:
```
type(scope): description

feat(auth): add JWT token refresh endpoint
fix(api): resolve validation error in contest creation
docs(readme): update installation instructions
test(unit): add user model tests
```

### Pre-commit Hooks
Install pre-commit hooks:
```bash
# Install pre-commit
composer require --dev brianium/paratest

# Set up git hooks
cp .git/hooks/pre-commit.sample .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

## Common Development Tasks

### Creating New API Endpoints
```bash
# 1. Create controller
php artisan make:controller Api/ExampleController

# 2. Create request validation
php artisan make:request ExampleRequest

# 3. Add routes to routes/api.php
# 4. Create tests
php artisan make:test ExampleControllerTest

# 5. Create model (if needed)
php artisan make:model Example -m
```

### Adding Middleware
```bash
# Create middleware
php artisan make:middleware ExampleMiddleware

# Register in bootstrap/app.php
# Add to route groups
```

### Database Operations
```bash
# Create model with migration and factory
php artisan make:model Example -mf

# Create pivot table
php artisan make:migration create_user_role_table --create=user_roles
```

## Environment-Specific Configuration

### Local Development
```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Testing
```env
APP_ENV=testing
APP_DEBUG=true
DB_DATABASE=olimpiapp_test_db
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
```

### Staging
```env
APP_ENV=staging
APP_DEBUG=false
LOG_LEVEL=info
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Troubleshooting

### Common Issues

**Permission Errors:**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

**Database Connection Issues:**
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

**Cache Issues:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Composer Issues:**
```bash
# Clear composer cache
composer clear-cache

# Update dependencies
composer update
```

### Performance in Development

**Optimize for Development:**
```bash
# Disable route caching
php artisan route:clear

# Disable config caching
php artisan config:clear

# Enable query logging
DB::enableQueryLog();
```

## Additional Resources

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [PHP Documentation](https://www.php.net/docs.php)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [API Design Best Practices](https://restfulapi.net/)

### Learning Resources
- [Laravel Casts](https://laracasts.com/)
- [Laravel News](https://laravel-news.com/)
- [PHP The Right Way](https://phptherightway.com/)

### Tools
- [Postman](https://www.postman.com/)
- [Insomnia](https://insomnia.rest/)
- [pgAdmin](https://www.pgadmin.org/)
- [TablePlus](https://tableplus.com/)

## Getting Help

### Internal Resources
1. Check this documentation
2. Review existing code for patterns
3. Check the issue tracker
4. Ask team members

### External Resources
1. Laravel official documentation
2. Stack Overflow
3. Laravel community forums
4. GitHub issues

## Next Steps

After completing the setup:
1. Review the [Coding Standards](./standards.md)
2. Read the [Testing Guidelines](./testing.md)
3. Understand the [Pull Request Process](./pull-requests.md)
4. Explore the [API Documentation](../api/README.md)

---

*Happy coding! 🚀*
