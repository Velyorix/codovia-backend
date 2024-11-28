# Codovia - Backend

Backend API for **Codovia**, an open-source interactive wiki platform designed to centralize knowledge on web development technologies and modules. This backend, developed with PHP, provides RESTful API endpoints to manage content, users, and permissions. The project is licensed under the Apache License 2.0 and is designed to work seamlessly with the Codovia frontend.

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Endpoints](#endpoints)
- [License](#license)

## Project Overview

The backend of Codovia handles the business logic and data management for:
- **User authentication and authorization**: Role-based access control for different user roles (admin, editor, registered user, visitor)
- **CRUD operations for articles and categories**: Create, read, update, and delete articles and categories
- **Markdown support** for articles with syntax highlighting
- **Real-time notifications** for article updates, new articles, and comments
- **Version control** for articles: Automatically saves previous versions of articles on updates, with the ability to restore a specific version
- **Favorites functionality**: Allows users to add, remove, and list their favorite articles
- **Ratings feature**: Allows users to rate articles and view average ratings
- **Tags management**: Enables tagging of articles for categorization and filtering
- **Reading Progress Tracking**: Allows users to track their reading progress on articles, including a reading history
- **Advanced search** functionality with Meilisearch, allowing filters for categories, tags, date ranges, and keyword matching
- **Pagination** for listing resources with customizable items per page

## Tech Stack

- **Language**: PHP
- **Framework**: Laravel
- **Database**: MySQL or MongoDB (configurable)
- **Authentication**: Laravel Passport (OAuth 2.0)
- **Search**: Meilisearch for advanced search functionalities
- **API Documentation**: OpenAPI/Swagger (optional)
- **Notifications**: Custom notification system for real-time user alerts

## Installation

To set up the backend environment locally, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/YuketsuSh/codovia-backend.git
   cd codovia-backend
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

### Database
Configure your database connection in the `.env` file. Default settings are for MySQL.

### OAuth Configuration
Passport is used for authentication. Ensure Passport is correctly configured and run `php artisan passport:install` to set up OAuth clients.

### Meilisearch Configuration
Codovia's search functionality is powered by Codovia. To enable Meilisearch, follow these steps:

1. **Install and Run Meilisearch**: Install Meilisearch on your server or use a hosted instance.
2. **Set Environment Variables**:
    - In the `.env` file, add the following:
      ```env
      SCOUT_DRIVER=meilisearch
      MEILISEARCH_HOST=https://your-meilisearch-domain.com
      MEILISEARCH_KEY=your_public_search_key
      MEILISEARCH_ADMIN_KEY=your_admin_key
      ```
    - Replace `your-meilisearch-domain.com` with your Meilisearch server's URL and provide the appropriate keys.
3. **Customize Scout Configuration**:
    - Open `config/scout.php` and ensure Meilisearch settings are configured as needed. You can define index-specific settings, such as `filterableAttributes` and `sortableAttributes`, which allow for efficient filtering and sorting.
4. **Sync Data with Meilisearch**:
    - To index existing data, run:
      ```bash
      php artisan scout:import "App\Models\Article"
      ```
    - This command will sync all articles with Meilisearch for instant searchability.

### Notifications
The notification system will trigger events on article creation, updates, and deletions. Ensure database settings are configured for optimal performance.

## Endpoints

Below are some of the main API endpoints:

- **Authentication**:
    - `POST /register` - Register a new user
    - `POST /login` - Login and obtain an access token

- **Articles**:
    - `GET /api/articles?per_page={number}` - Retrieve a paginated list of all articles (default 10 per page, customizable with `per_page` parameter)
    - `POST /api/articles` - Create a new article (admin/editor only)
    - `PUT /api/articles/{article}` - Update an article with version control (admin/editor only)
    - `DELETE /api/articles/{article}` - Delete an article (admin only)
    - `GET /api/articles/{article}/history` - View article version history
    - `POST /api/articles/{article}/restore/{versionId}` - Restore a specific version of an article
    - `GET /api/articles/search?query=your_query&category={category_id}&tag={tag_id}&date_from={start_date}&date_to={end_date}&per_page={number}` - Search for articles with optional filters (category, tags, date range, etc.) and pagination support
    - `POST /api/articles/{article}/rate` - Submit a rating for an article (authenticated users only)
    - `GET /api/articles/{article}/ratings` - Get the average rating and list of ratings for a specific article

- **Categories**:
    - `GET /api/categories?per_page={number}` - Retrieve a paginated list of all categories (default 10 per page)
    - `POST /api/categories` - Create a new category (admin only)
    - `PUT /api/categories/{category}` - Update a category (admin only)
    - `DELETE /api/categories/{category}` - Delete a category (admin only)

- **Comments**:
    - `GET /api/articles/{article}/comments?per_page={number}` - Retrieve a paginated list of comments for a specific article (default 10 per page)
    - `POST /api/articles/{article}/comments` - Create a new comment for a specific article (authenticated users only)
    - `DELETE /api/comments/{comment}` - Delete a comment (authorized users only: the comment owner or users with comment management permissions)

- **Favorites**:
    - `POST /api/articles/{article}/favorite` - Add an article to the favorites list (authenticated users only)
    - `DELETE /api/articles/{article}/favorite` - Remove an article from the favorites list (authenticated users only)
    - `GET /api/favorites?per_page={number}` - Retrieve a paginated list of favorite articles for the authenticated user (default 10 per page)

- **Tags**:
    - `GET /api/tags` - Retrieve a list of all tags
    - `POST /api/tags` - Create a new tag (admin only)
    - `DELETE /api/tags/{tag}` - Delete a tag (admin only)
    - `POST /api/articles/{article}/tags` - Associate tags with an article (admin only)

- **Reading Progress**:
    - `POST /api/articles/{article}/progress` - Update the reading progress of an article for an authenticated user
    - `GET /api/articles/{article}/progress` - Retrieve the current reading progress of an article for an authenticated user
    - `GET /api/reading-history` - Retrieve the authenticated user's reading history, showing all articles they have made progress on

- **Notifications**:
    - `GET /api/notifications` - Retrieve notifications for the authenticated user
    - `POST /api/notifications/{notification}/mark-as-read` - Mark a notification as read
    - `DELETE /api/notifications/{notification}` - Delete a specific notification for an authenticated user

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE) file for more details.
