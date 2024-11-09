# TechPedia - Backend

Backend API for **TechPedia**, an open-source interactive wiki platform designed to centralize knowledge on web development technologies and modules. This backend, developed with PHP, provides RESTful API endpoints to manage content, users, and permissions. The project is licensed under the Apache License 2.0 and is designed to work seamlessly with the TechPedia frontend.

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Endpoints](#endpoints)
- [License](#license)

## Project Overview

The backend of TechPedia handles the business logic and data management for:
- **User authentication and authorization**: Role-based access control for different user roles (admin, editor, registered user, visitor)
- **CRUD operations for articles and categories**: Create, read, update, and delete articles and categories
- **Markdown support** for articles with syntax highlighting
- **Real-time notifications** for article updates, new articles, and comments
- **Version control** for articles: Automatically saves previous versions of articles on updates, with the ability to restore a specific version
- **Advanced search** functionality with filters for categories, date ranges, and keyword matching

## Tech Stack

- **Language**: PHP
- **Framework**: Laravel
- **Database**: MySQL or MongoDB (configurable)
- **Authentication**: Laravel Passport (OAuth 2.0)
- **Search**: Elasticsearch or Algolia (for advanced search functionalities)
- **API Documentation**: OpenAPI/Swagger (optional)
- **Notifications**: Custom notification system for real-time user alerts

## Installation

To set up the backend environment locally, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/YuketsuSh/TechPedia-Backend.git
   cd TechPedia-Backend
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Set up environment variables**:
    - Copy the `.env.example` file to `.env`:
      ```bash
      cp .env.example .env
      ```
    - Update `.env` with your database credentials and any other configuration values.

4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

5. **Install Passport** for OAuth 2.0 authentication:
   ```bash
   php artisan passport:install
   ```

6. **Run database migrations**:
   ```bash
   php artisan migrate
   ```

7. **Run the development server**:
   ```bash
   php artisan serve
   ```

## Configuration

- **Database**: Configure your database connection in the `.env` file. Default settings are for MySQL.
- **OAuth Configuration**: Passport is used for authentication. Make sure to configure Passport correctly and run `php artisan passport:install` to set up OAuth clients.
- **Search Configuration**: If using Elasticsearch or Algolia, set up the connection settings in `.env`.
- **Notifications**: The notification system will trigger events on article creation, updates, and deletions. Ensure database settings are configured for optimal performance.

## Endpoints

Below are some of the main API endpoints:

- **Authentication**:
    - `POST /register` - Register a new user
    - `POST /login` - Login and obtain an access token

- **Articles**:
    - `GET /api/articles` - Retrieve a list of all articles
    - `POST /api/articles` - Create a new article (admin/editor only)
    - `PUT /api/articles/{article}` - Update an article with version control (admin/editor only)
    - `DELETE /api/articles/{article}` - Delete an article (admin only)
    - `GET /api/articles/{article}/history` - View article version history
    - `POST /api/articles/{article}/restore/{versionId}` - Restore a specific version of an article
    - `GET /api/articles/search?query=your_query` - Search for articles with optional filters (category, date range, etc.)

- **Categories**:
    - `GET /api/categories` - Retrieve all categories
    - `POST /api/categories` - Create a new category (admin only)
    - `PUT /api/categories/{category}` - Update a category (admin only)
    - `DELETE /api/categories/{category}` - Delete a category (admin only)

- **Notifications**:
    - `GET /api/notifications` - Retrieve notifications for the authenticated user
    - `POST /api/notifications/{notification}/mark-as-read` - Mark a notification as read

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE) file for more details.
