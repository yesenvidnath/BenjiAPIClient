
# Benji API Client Backend Repository

Welcome to the **Benji API Client Backend Repository**! This repository contains the complete backend implementation for the Benji Application, built using Laravel. It provides the core functionality and API endpoints required for the Benji ecosystem, integrating seamlessly with the frontend and admin modules.

---

## Project Overview

The Benji API Client serves as the backbone of the Benji ecosystem, managing data flow, business logic, and API endpoints. It powers features for user management, financial insights, authentication, and much more.

### Key Features:
- **User Management**: APIs for user registration, authentication, and role management.
- **Financial Insights**: Endpoints for managing and analyzing financial data.
- **Integration with Third-Party Services**: Seamless integration with payment gateways, Google APIs, and more.
- **Secure Authentication**: Token-based authentication using Laravel Sanctum.
- **Scalable Architecture**: Designed to handle large-scale applications.

---

## Project Structure

### Key Directories:

- **app/**: Core application logic and models.
- **config/**: Configuration files for database, services, and other settings.
- **database/**: Migrations, seeders, and factories for database setup.
- **routes/**: API route definitions (web.php, api.php).
- **resources/views/**: Laravel Blade templates for web rendering.
- **storage/**: Logs, cached files, and compiled views.

---

## Requirements

### Prerequisites:
Ensure the following dependencies are installed:

- PHP 8.1+
- Composer
- MySQL database
- Laravel Framework 10.10
- Node.js and npm (for asset management)
- Redis (optional for caching and queue management)

---

## Environment Configuration

### Setting Up the Environment:
Configure the `.env` file for your environment:

```env
APP_NAME=BenjiAPI
APP_ENV=local
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=benji_database
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@benji.com"
MAIL_FROM_NAME="Benji Support"

SANCTUM_STATEFUL_DOMAINS=localhost
```

---

## Getting Started

### Installation Steps:

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/yesenvidnath/BenjiAPIClient.git
   cd BenjiAPIClient
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Set Up Environment Variables:**
   Create a `.env` file and configure it as described above.

4. **Run Database Migrations and Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Serve the Application:**
   ```bash
   php artisan serve
   ```

6. **Set Up Storage:**
   Link the storage directory for public access:
   ```bash
   php artisan storage:link
   ```

7. **Compile Frontend Assets (Optional):**
   ```bash
   npm install
   npm run dev
   ```

---

## API Endpoints

### Authentication:
- `POST /api/register`: Register a new user.
- `POST /api/login`: Log in a user.
- `POST /api/logout`: Log out the current user.

### Financial Management:
- `GET /api/expenses`: Retrieve user expenses.
- `POST /api/expenses`: Add a new expense.
- `PUT /api/expenses/{id}`: Update an expense.
- `DELETE /api/expenses/{id}`: Delete an expense.

### Admin Functions:
- `GET /api/users`: Retrieve all users.
- `POST /api/users`: Add a new user.
- `DELETE /api/users/{id}`: Delete a user.

For detailed documentation, visit the [API Documentation](http://localhost/docs).

---

## Contribution Guidelines

Contributions are welcome! Please adhere to the following steps:

1. Fork the repository and create a new branch for your feature or bugfix.
2. Write clean, well-documented code.
3. Test your changes thoroughly before submitting a pull request.
4. Open a detailed pull request with a summary of your changes.

---

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

---

## Contact

For further inquiries or support:
- **Name**: K.K.Y. Vidnath
- **Email**: support@benji.com

Thank you for contributing to the Benji API Client!
