# TechPedia - Backend

Backend API for **TechPedia**, an open-source interactive wiki platform designed to centralize knowledge on web development technologies and modules. This backend, developed with PHP, provides RESTful API endpoints to manage content, users, and permissions. The project is licensed under the Apache License 2.0 and is designed to work seamlessly with the TechPedia frontend.

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [License](#license)

## Project Overview

The backend of TechPedia handles the business logic and data management for:
- User authentication and authorization
- CRUD operations for articles and categories
- Markdown support for articles with syntax highlighting
- Real-time notifications for article updates
- Role-based access control for users

## Tech Stack

- **Language**: PHP
- **Framework**: Laravel
- **Database**: MySQL or MongoDB (configurable)
- **Authentication**: JWT (JSON Web Tokens)
- **Search**: Elasticsearch or Algolia (for advanced search functionalities)
- **API Documentation**: OpenAPI/Swagger (optional)

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

5. **Run database migrations**:
   ```bash
   php artisan migrate
   ```

6. **Run the development server**:
   ```bash
   php artisan serve
   ```

## Configuration

- **Database**: Configure your database connection in the `.env` file. Default settings are for MySQL.
- **JWT Secret**: Run `php artisan jwt:secret` to generate a unique key for JWT authentication.
- **Search Configuration**: If using Elasticsearch or Algolia, set up the connection settings in `.env`.


## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE) file for more details.