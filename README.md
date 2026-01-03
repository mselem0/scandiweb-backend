# Scandiweb eCommerce Backend

A pure PHP 8.1+ GraphQL API backend for an eCommerce application, built without frameworks to demonstrate core PHP skills, Object-Oriented Programming, and GraphQL implementation.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Architecture](#architecture)
- [GraphQL API](#graphql-api)
- [Code Standards](#code-standards)
- [Testing](#testing)
- [Deployment](#deployment)

---

## 🎯 Overview

This backend implements a complete GraphQL API for an eCommerce platform, featuring:

- **Pure PHP Implementation** - No frameworks (Laravel, Symfony, etc.)
- **Object-Oriented Design** - Demonstrates polymorphism, inheritance, and abstraction
- **GraphQL API** - Complete schema with queries and mutations
- **PSR Compliant** - Follows PSR-1, PSR-4, and PSR-12 standards
- **MySQL Database** - Normalized schema with proper relationships

---

## ✨ Features

### Core Functionality
- ✅ Product listing and filtering by category
- ✅ Product details with attributes and gallery
- ✅ Multi-currency pricing support
- ✅ Order creation and management
- ✅ Flexible attribute system (text and swatch types)

### Technical Features
- ✅ Polymorphic product types (Cloth, Tech, Generic)
- ✅ Polymorphic attribute types (Text, Swatch)
- ✅ Factory pattern for object creation
- ✅ Repository pattern for data access
- ✅ Resolver pattern for GraphQL
- ✅ Type-safe GraphQL schema

---

## 📦 Requirements

- **PHP:** 7.4+ or 8.1+ (recommended)
- **MySQL:** 5.6+ or 8.0+
- **Composer:** 2.0+
- **Web Server:** Apache or Nginx with PHP-FPM
- **Extensions:**
  - PDO
  - PDO_MySQL
  - JSON
  - mbstring

---

## 🚀 Installation

### 1. Clone Repository

```bash
git clone [repository-url]
cd backend-me
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy `.env.example` to `.env` (if exists) or create `.env` file:

```env
DB_HOST=localhost
DB_NAME=scandiweb_db
DB_USER=your_username
DB_PASS=your_password
APP_DEBUG=false
BASE_PATH=/
```

### 4. Set Up Database

See [Database Setup](#database-setup) section below.

### 5. Configure Web Server

#### Apache (.htaccess included)

The project includes `.htaccess` files for Apache configuration. Ensure `mod_rewrite` is enabled.

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/backend-me/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## ⚙️ Configuration

### Database Configuration

Edit `src/Config/Database.php` or use environment variables:

```php
// Database connection settings
DB_HOST=localhost
DB_NAME=scandiweb_db
DB_USER=root
DB_PASS=password
```

### CORS Configuration

CORS headers are configured in `public/index.php`. For production, update:

```php
header('Access-Control-Allow-Origin: https://your-frontend-domain.com');
```

### Base Path

If deploying to a subdirectory, update `public/index.php`:

```php
$basePath = '/your-subdirectory';
```

---

## 🗄️ Database Setup

### 1. Create Database

```sql
CREATE DATABASE scandiweb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Schema

```bash
mysql -u your_username -p scandiweb_db < data/schema.sql
```

Or via MySQL client:

```sql
USE scandiweb_db;
SOURCE data/schema.sql;
```

### 3. Import Data

```bash
php scripts/import-data.php
```

Or manually import `data/data.json` using the import script.

### Database Schema Overview

- **categories** - Product categories
- **products** - Product information
- **product_gallery** - Product images
- **currencies** - Currency definitions
- **product_prices** - Multi-currency pricing
- **attributes** - Attribute definitions (Size, Color, etc.)
- **attribute_items** - Attribute options/values
- **product_attributes** - Product-attribute relationships
- **orders** - Order records
- **order_items** - Order line items

See `DATABASE_ERD.md` for detailed schema documentation.

---

## 📁 Project Structure

```
backend-me/
├── public/                 # Public entry point
│   ├── index.php          # Application bootstrap
│   └── .htaccess          # Apache configuration
├── src/                   # Source code
│   ├── Config/            # Configuration classes
│   │   └── Database.php   # Database connection
│   ├── Controller/        # Request handlers
│   │   └── GraphQL.php    # GraphQL controller
│   ├── GraphQL/           # GraphQL layer
│   │   ├── Mutations/     # Mutation definitions
│   │   ├── Queries/       # Query definitions
│   │   ├── Resolvers/     # Data resolvers
│   │   └── Types/         # GraphQL type definitions
│   ├── Models/            # Domain models
│   │   ├── Abstract/      # Abstract base classes
│   │   ├── Attribute/    # Attribute models
│   │   ├── Product/       # Product models
│   │   ├── Category.php  # Category model
│   │   └── Order.php     # Order model
│   └── Utils/            # Utility classes
│       └── DataImporter.php
├── data/                  # Data files
│   ├── schema.sql        # Database schema
│   └── data.json         # Sample data
├── scripts/              # Utility scripts
│   └── import-data.php  # Data import script
├── composer.json         # Dependencies
└── README.md            # This file
```

---

## 🏗️ Architecture

### Object-Oriented Design

#### Polymorphism

**Products:**
- `AbstractProduct` - Base class with common functionality
- `ClothProduct` - Clothing-specific implementation
- `TechProduct` - Technology product implementation
- `GenericProduct` - Default product type

**Attributes:**
- `AbstractAttribute` - Base class
- `TextAttribute` - Text-based attributes (Size, Capacity)
- `SwatchAttribute` - Color swatch attributes

#### Design Patterns

1. **Factory Pattern** - `createFromArray()` methods create appropriate types
2. **Repository Pattern** - Models handle data access
3. **Resolver Pattern** - GraphQL resolvers delegate to models
4. **Singleton Pattern** - Database connection, TypeRegistry

### Code Organization

- **No Procedural Code** - All code is object-oriented (except bootstrap)
- **PSR-4 Autoloading** - Namespace-based autoloading
- **Separation of Concerns** - Models, Resolvers, Types are separate

---

## 🔌 GraphQL API

### Endpoint

```
POST /graphql
Content-Type: application/json
```

### Queries

#### Get All Categories
```graphql
query {
  categories {
    name
  }
}
```

#### Get Products by Category
```graphql
query {
  products(category: "all") {
    id
    name
    brand
    inStock
    gallery
    prices {
      amount
      currency {
        label
        symbol
      }
    }
    attributes {
      id
      name
      type
      items {
        id
        displayValue
        value
      }
    }
  }
}
```

#### Get Product by ID
```graphql
query {
  product(id: "huarache-x-stussy-le") {
    id
    name
    description
    gallery
    prices {
      amount
      currency {
        label
        symbol
      }
    }
    attributes {
      id
      name
      type
      items {
        id
        displayValue
        value
      }
    }
  }
}
```

### Mutations

#### Create Order
```graphql
mutation {
  createOrder(items: [
    {
      productId: "huarache-x-stussy-le"
      quantity: 1
      selectedAttributes: [
        {
          attributeId: "Size"
          attributeItemId: "40"
        }
      ]
    }
  ]) {
    id
    totalAmount
    currency
    status
    itemCount
  }
}
```

### Postman Collection

A complete Postman collection is available at `Postman_Collection.json` with all queries and mutations pre-configured.

---

## 📏 Code Standards

### PSR Compliance

- **PSR-1:** Basic coding standard
  - PHP tags, class/method naming, constants
- **PSR-4:** Autoloading standard
  - Namespace structure matches directory structure
- **PSR-12:** Extended coding style
  - Indentation, braces, spacing

### Code Style

```php
<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\AbstractModel;

class ClothProduct extends AbstractProduct
{
    protected string $type = 'clothes';

    public function getType(): string
    {
        return 'clothes';
    }
}
```

---

## 🧪 Testing

### Manual Testing

Use the provided Postman collection or GraphQL playground:

1. Import `Postman_Collection.json` into Postman
2. Set `baseUrl` variable to your API endpoint
3. Test all queries and mutations

### Auto QA Tool

Test your deployment with the official Auto QA tool:
http://165.227.98.170/

Ensure all tests pass before submission.

---

## 🚢 Deployment

### Production Checklist

- [ ] Update `.env` with production database credentials
- [ ] Set `APP_DEBUG=false`
- [ ] Configure CORS for production frontend domain
- [ ] Set proper file permissions
- [ ] Enable HTTPS
- [ ] Configure error logging
- [ ] Test all GraphQL endpoints
- [ ] Verify Auto QA tests pass

### Recommended Hosting

- **000webhost.com** (Free PHP hosting)
- **Heroku** (with PHP buildpack)
- **DigitalOcean** (VPS)
- **AWS EC2** (VPS)

### Environment Variables

```env
DB_HOST=your_db_host
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_db_password
APP_DEBUG=false
BASE_PATH=/
```

---

## 📚 Dependencies

### Production

- **webonyx/graphql-php** (^15.2) - GraphQL implementation
- **nikic/fast-route** (^1.3) - Routing
- **vlucas/phpdotenv** (^5.6) - Environment variables

### Development

- PHPUnit (for testing, optional)

---

## 🔧 Troubleshooting

### Common Issues

**1. Database Connection Error**
- Verify database credentials in `src/Config/Database.php`
- Ensure MySQL service is running
- Check database exists

**2. 404 Not Found**
- Verify `.htaccess` is working (Apache)
- Check base path configuration
- Ensure `mod_rewrite` is enabled

**3. CORS Errors**
- Update CORS headers in `public/index.php`
- Verify frontend domain is allowed

**4. GraphQL Errors**
- Check error response for details
- Verify database is populated
- Check PHP error logs

---

## 📖 Additional Documentation

- **Database ERD:** See `DATABASE_ERD.md` in project root
- **GraphQL Schema:** Use introspection query in Postman collection
- **Component Tree:** See `REACT_COMPONENT_TREE.md` for frontend structure

---

## 👨‍💻 Development

### Adding New Product Types

1. Create new class extending `AbstractProduct`
2. Implement `getType()` and `getTypeSpecificData()`
3. Add to factory method in `AbstractProduct::createFromArray()`

### Adding New Attribute Types

1. Create new class extending `AbstractAttribute`
2. Implement `getAttributeType()` and `formatItemForDisplay()`
3. Add to factory method in `AbstractAttribute::createFromArray()`

---

## 📝 License

This project is part of a test task submission for Scandiweb.

---

## 🙏 Acknowledgments

- Scandiweb for providing the test task
- webonyx/graphql-php for the excellent GraphQL library
- PHP-FIG for PSR standards

---

**Built with ❤️ for Scandiweb**
