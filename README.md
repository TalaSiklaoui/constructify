# Constructify - Construction Portfolio Website

## Project Overview

Constructify is a responsive construction portfolio website developed using PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

The website displays dynamic content retrieved from a MySQL database. It also includes a protected administration dashboard where an administrator can manage website content without modifying the source code.

## Technologies Used

- PHP 8.4
- MySQL
- HTML and CSS
- JavaScript
- Bootstrap
- Lucide Icons
- XAMPP

## Features

**Client Side**

- Responsive single-page design
- Dynamic Home, About, Services, Statistics, and Projects sections
- Project category filtering
- Project image galleries with fullscreen viewing
- Responsive navigation and Back to Top button

**Admin Side**

- Administrator login and logout
- Website settings management
- Editable sections content and image uploads

## Installation and Setup

1. Install XAMPP with PHP and MySQL/MariaDB support.
2. Copy the `constructify` folder into the XAMPP `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
5. Create a database named `constructify_db`.
6. Import the supplied `constructify_db.sql` file.
7. Open `config/database.php` and configure the database connection.

Example local configuration:

```php
$host = 'localhost';
$dbname = 'constructify_db';
$username = 'root';
$password = '';
```

The connection values may need to be adjusted depending on the local XAMPP configuration.

## Running the Website

Client side:

`http://localhost/constructify/`

Admin login:

`http://localhost/constructify/admin/login.php`

## Test Administrator Credentials

Username: **admin**
Password: **Constructify@2026**

These credentials are provided for testing the local admin dashboard.

## Database

The `constructify_db.sql` export contains the database structure and data required to run the project.

Website content is loaded dynamically from MySQL. Uploaded images are stored in the project directories, with their paths recorded in the database.

## Notes

- The website is intended to be run locally using XAMPP.
- Apache and MySQL must be running.
- The project requires PHP PDO with MySQL support and image-upload functionality.
- Internet access is required to load Bootstrap and Lucide Icons from their CDNs.
- Some navigation labels and visual elements are non-interactive by design, following the provided reference layout.
- The administrator credentials included in this README are provided for testing purposes only.
