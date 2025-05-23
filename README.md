# Bullshit Bingo Generator

A web-based Bullshit Bingo game with real-time updates, QR code invitations, and customizable word lists.

## Features

- Create custom Bingo events with your own word lists
- Generate QR codes for easy game joining
- Real-time updates for winners
- Multiple rounds support
- Responsive design for all devices

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB
- Web server (Apache/Nginx)
- WebSocket server (for real-time updates)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/bullshit-bingo-app.git
cd bullshit-bingo-app
```

2. Create the database:
```sql
CREATE DATABASE bullshit_bingo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import the database schema:
```bash
mysql -u your_username -p bullshit_bingo < database/schema.sql
```

4. Configure the application:
   - Copy `config/config.example.php` to `config/local.php`
   - Adjust the settings in `config/local.php` to match your environment
   - Make sure the database credentials are correct

## Configuration

### Environment Setup

The application uses environment-specific configuration files. To set up a new environment:

1. Copy `config/config.example.php` to a new file named after your environment (e.g., `config/local.php` for local development)
2. Adjust the settings according to your environment

### Configuration Options

#### Basic Settings
```php
'debug' => true,           // Set to false in production
'timezone' => 'Europe/Berlin',
'app_url' => 'http://localhost:8000',
'app_name' => 'Bullshit Bingo',
```

#### Database Configuration
```php
'db' => [
    'host' => 'localhost',
    'database' => 'bullshit_bingo',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

#### WebSocket Configuration
```php
'websocket' => [
    'host' => 'localhost',
    'port' => 8080,
],
```

#### Security Settings
```php
'security' => [
    'allowed_origins' => ['http://localhost:8000'],
    'cors_headers' => [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
    ],
],
```

### Production Configuration

For production environments:

1. Create `config/production.php` based on `config/config.example.php`
2. Set `debug` to `false`
3. Update `app_url` to your production domain
4. Configure secure database credentials
5. Set appropriate CORS and security settings
6. Enable secure session settings:
```php
'session' => [
    'secure' => true,
    'domain' => 'your-domain.com',
],
```

## Development

### Local Development

1. Set up your local web server to point to the `public` directory
2. Configure your database in `config/local.php`
3. Start the WebSocket server:
```bash
php bin/websocket-server.php
```

### Running Tests

```bash
php vendor/bin/phpunit
```

## Deployment

1. Set up your production environment configuration
2. Configure your web server (Apache/Nginx)
3. Set up SSL certificates
4. Configure the WebSocket server
5. Set up database backups

## Security Considerations

- Never commit sensitive configuration files
- Use strong passwords for database access
- Enable SSL in production
- Configure proper CORS settings
- Set appropriate file permissions

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Directory Structure

```
public_html/ (or www/ or htdocs/)
├── css/
│   └── style.css
├── .htaccess
└── index.php
src/
├── controllers/
├── models/
└── views/
    ├── home.php
    ├── create-event.php
    ├── join-event.php
    └── 404.php
config/
├── config.example.php
├── local.php (not included in Git repository)
└── database.php
``` 