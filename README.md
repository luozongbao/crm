# Personal CRM System

A simple yet powerful Customer Relationship Management (CRM) system built with PHP and MariaDB, designed for small businesses to manage customer relationships, track interactions, and monitor follow-ups.

## Features

- **User Management**
  - Secure authentication system
  - Role-based access control (Admin/User)
  - User profile management

- **Customer Management**
  - Comprehensive customer profiles
  - Status tracking (Prospect, Qualified, Active, etc.)
  - Contact information management
  - Location and company type tracking

- **Contact Person Management**
  - Multiple contacts per customer
  - Primary contact designation
  - Contact details and role tracking

- **Action History**
  - Track all customer interactions
  - Record responses and next steps
  - Schedule follow-ups
  - Automatic last contact updating

- **Dashboard**
  - Customer status statistics
  - Recent activities overview
  - Upcoming follow-ups
  - Export functionality for activities and follow-ups

- **Reporting & Analytics**
  - Export activities to CSV
  - Export follow-ups to CSV
  - Date range filtering
  - Customer status distribution
- Dashboard with analytics
- Export functionality
- Settings management

## Requirements

- PHP 7.4 or higher
- MariaDB 10.4 or higher
- Nginx or Apache web server
- Modern web browser
- PHP Extensions:
  - mysqli
  - session
  - json
  - mbstring

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/crm.git
cd crm
```

2. Create and configure the database:
```bash
mysql -u your_username -p < database/database.sql
```

3. Configure the application:
```bash
cp includes/config.example.php includes/config.php
```

4. Edit `includes/config.php` with your database credentials and site URL:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'crm_db');
define('SITE_URL', 'http://your-domain.com/crm');
```

5. Set proper permissions:
```bash
chmod 755 -R /path/to/crm
chmod 777 -R /path/to/crm/assets/uploads  # If you plan to handle file uploads
```

6. Configure your web server:

For Nginx, add to your server block:
```nginx
location /crm {
    try_files $uri $uri/ /crm/index.php?$query_string;
}
```

For Apache, the included .htaccess file should work out of the box.

7. Access the application and log in with default credentials:
   - Username: admin
   - Password: admin123
   - **Important**: Change the default password immediately after first login!

## Directory Structure

```
crm/
├── actions/              # Action history management
│   ├── add.php
│   └── edit.php
├── assets/              # Static assets
│   ├── css/
│   └── js/
├── contacts/            # Contact person management
│   ├── add.php
│   └── edit.php
├── customers/           # Customer management
│   ├── add.php
│   ├── edit.php
│   ├── index.php
│   └── view.php
├── database/           # Database schema
│   └── database.sql
├── includes/           # Core PHP includes
│   ├── config.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── dashboard.php       # Main dashboard
├── index.php          # Application entry point
├── login.php          # Authentication
├── logout.php         # Session termination
└── settings.php       # System settings
```

## Security Considerations

1. Always change the default admin password
2. Keep PHP and MariaDB updated
3. Use HTTPS in production
4. Regularly backup your database
5. Monitor error logs
6. Implement rate limiting for login attempts

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team.

## Authors

- Your Name - Initial work - [YourGitHub](https://github.com/yourusername)

## Acknowledgments

- PHP Community
- MariaDB Documentation
- Bootstrap Framework