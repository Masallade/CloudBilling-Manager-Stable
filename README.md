# Cloud Billing Manager (Geo POS)

A comprehensive **Accounting, Invoicing, and CRM Software** built on CodeIgniter framework. This all-in-one cloud billing solution helps businesses manage expenses, boost sales, and streamline invoices efficiently.

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [Version Information](#version-information)
- [Support](#support)
- [License](#license)

## 🎯 About

**Cloud Billing Manager** (formerly Geo POS) is a powerful business management system that combines Point of Sale (POS), accounting, invoicing, and Customer Relationship Management (CRM) capabilities into a single platform. It's designed to help businesses of all sizes manage their operations more efficiently.

### Key Highlights

- **Multi-language Support**: Supports multiple languages including English, Bengali, Arabic, Greek, Japanese, and more
- **Comprehensive Business Management**: From inventory to invoicing, from customer management to employee tracking
- **Cloud-Ready**: Built for cloud deployment with modern architecture
- **Extensible**: Plugin system for custom functionality
- **Secure**: Advanced permission management and data security features

## ✨ Features

### Core Modules

#### 📊 **Accounting & Finance**
- General Ledger
- Accounts Management
- Transactions Tracking
- Financial Reports
- Payment Gateway Integration
- Multi-currency Support

#### 🧾 **Invoicing & Billing**
- Invoice Creation & Management
- Quote/Quotation System
- Recurring Invoices
- Credit Notes
- Invoice Templates
- Email Invoice Delivery

#### 🛒 **Point of Sale (POS)**
- POS Interface
- Barcode Scanning
- Receipt Printing
- Quick Checkout
- Sales Reports

#### 📦 **Inventory Management**
- Product Management
- Stock Tracking
- Warehouse Management
- Stock Returns
- Product Categories & Groups
- Barcode Generation

#### 👥 **Customer & Supplier Management**
- Customer Profiles
- Supplier Management
- Customer Groups
- Purchase History
- Contact Management

#### 👨‍💼 **Employee Management**
- Employee Profiles
- Department Management
- Designation Tracking
- Attendance System
- Holiday Management
- Permission Management

#### 📈 **Projects & CRM**
- Project Management
- Task Tracking
- Milestones
- Activity Logs
- Customer Communication
- Support Tickets

#### 📊 **Reports & Analytics**
- Sales Reports
- Purchase Reports
- Financial Reports
- Inventory Reports
- Custom Reports
- Dashboard Analytics

#### ⚙️ **Settings & Configuration**
- System Settings
- Tax Configuration (VAT/GST)
- Currency Management
- Location Management
- Units Management
- Email & SMS Settings
- Payment Gateway Configuration

### Additional Features

- **Multi-language Interface**: Support for 20+ languages
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **API Support**: RESTful API for integration
- **Import/Export**: CSV import/export functionality
- **Subscriptions**: Recurring billing management
- **Promotions**: Discount and promotion management
- **Templates**: Customizable invoice and document templates
- **Printing**: Receipt and invoice printing support
- **Email Integration**: Send invoices and notifications via email
- **SMS Integration**: SMS notifications support

## 🔧 Requirements

### Server Requirements

- **PHP**: 7.4 or higher (PHP 8.x recommended)
- **MySQL**: 5.7 or higher / MariaDB 10.2 or higher
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **Extensions**: 
  - mysqli
  - mbstring
  - gd
  - curl
  - openssl
  - zip

### Recommended

- **Memory Limit**: 256MB or higher
- **Upload Size**: 10MB or higher
- **SSL Certificate**: For production environments

## 📦 Installation

### Step 1: Download/Clone

Clone or download the project to your web server directory:

```bash
# If using Git
git clone <repository-url> demo.cloudbillingmanager.com

# Or extract the downloaded ZIP file
```

### Step 2: Set Permissions

Ensure proper file permissions:

```bash
chmod 755 application/
chmod 755 system/
chmod 777 application/logs/
chmod 777 userfiles/
```

### Step 3: Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE cloudbilling_v2 CHARACTER SET utf8 COLLATE utf8_general_ci;
```

2. Import the database schema (if available)

### Step 4: Configure Database

Edit `application/config/database.php`:

```php
$hostname = 'localhost';
$username = 'your_username';
$password = 'your_password';
$database = 'cloudbilling_v2';
```

### Step 5: Configure Base URL

Edit `application/config/config.php`:

```php
$config['base_url'] = 'http://your-domain.com/';
```

### Step 6: Set Environment

Edit `index.php` and set the environment:

```php
define('ENVIRONMENT', 'production'); // or 'development' for development
```

### Step 7: Access the Application

Navigate to your domain in a web browser. If installation is required, you'll be redirected to the installation page.

## ⚙️ Configuration

### Environment Settings

The application supports different environments:

- **development**: Shows errors, enables debugging
- **testing**: Limited error reporting
- **production**: Minimal error reporting, optimized for performance

### Database Configuration

Located in: `application/config/database.php`

### Application Configuration

Located in: `application/config/config.php`

Key settings:
- Base URL
- Tax Type (VAT/GST)
- Timezone
- Encryption keys
- Session settings

### Tax Configuration

The system supports different tax types (VAT, GST, etc.). Configure in:
- Database: `geopos_system` table
- Config: `application/config/config.php`

## 📁 Project Structure

```
demo.cloudbillingmanager.com/
├── application/          # Application code
│   ├── config/           # Configuration files
│   ├── controllers/     # Controllers (MVC)
│   ├── models/          # Data models
│   ├── views/           # View templates
│   ├── libraries/       # Custom libraries
│   ├── helpers/         # Helper functions
│   ├── language/        # Language files
│   └── third_party/     # Third-party libraries
├── system/              # CodeIgniter core
├── assets/              # Frontend assets
├── app-assets/          # Additional assets
├── custom/              # Custom CSS/JS
├── userfiles/           # User uploaded files
├── app/api/             # API endpoints
├── index.php            # Entry point
└── .htaccess            # Apache configuration
```

### Key Directories

- **application/controllers/**: Business logic controllers
- **application/views/**: HTML/PHP view templates
- **application/models/**: Database models
- **assets/myjs/**: Custom JavaScript files
- **userfiles/**: User-uploaded content (invoices, images, etc.)

## 📝 Version Information

- **Version**: 6.3
- **Build**: 129
- **Framework**: CodeIgniter 3.x
- **PHP Version**: 7.4+ / 8.x

## 🔐 Security Notes

1. **Change Default Credentials**: Always change default admin credentials
2. **Secure Database**: Use strong database passwords
3. **HTTPS**: Use SSL/TLS in production
4. **File Permissions**: Set appropriate file permissions
5. **Environment**: Use 'production' environment in live servers
6. **Updates**: Keep the system updated

## 🛠️ Development

### CodeIgniter Framework

This project uses CodeIgniter 3.x MVC framework. Familiarize yourself with:
- [CodeIgniter Documentation](https://codeigniter.com/userguide3/)

### Custom JavaScript

Main JavaScript file: `assets/myjs/control__1scr.js`

### API Endpoints

API endpoints are located in: `app/api/`

## 📚 Documentation

Additional documentation files:
- `FLATPICKR_CALENDAR_UPGRADE.md` - Calendar upgrade notes
- `REDIRECT_MAPPING_DOCUMENTATION.md` - Redirect mapping documentation
- `STATUS_DATEPICKER_FIX_SUMMARY.md` - Datepicker fixes
- `SYSTEM_OPTIMIZATION_SUMMARY.md` - System optimization details
- `WIFI_SETUP_INSTRUCTIONS.txt` - WiFi setup guide

## 🐛 Troubleshooting

### Common Issues

1. **Blank Page**: Check error logs in `application/logs/`
2. **Database Connection Error**: Verify database credentials
3. **Permission Denied**: Check file/folder permissions
4. **404 Errors**: Ensure mod_rewrite is enabled (Apache) or configure Nginx properly

### Error Logs

- Application logs: `application/logs/`
- PHP error log: Check server error log
- API error log: `app/api/error_log`

## 📞 Support

- **Email**: support@ultimatekode.com
- **Website**: https://www.ultimatekode.com

## 📄 License

This software is furnished under a license and may be used and copied only in accordance with the terms of such license.

**Copyright (c) Rajesh Dukiya. All Rights Reserved.**

If you purchased from CodeCanyon, please read the full License from:
http://codecanyon.net/licenses/standard/

---

## 🙏 Acknowledgments

- Built on [CodeIgniter](https://codeigniter.com/) framework
- Uses various open-source libraries and components
- Third-party integrations for payment gateways, email, SMS, etc.

---

**Note**: This is a commercial software. Ensure you have proper licensing before use in production environments.

