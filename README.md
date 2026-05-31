# Booking App

Booking App is a hotel room booking application. It allows users to reserve available rooms for specific dates and cancel existing reservations.

## Features

- Backend API in Laravel (Booking service)
- Frontend application built with Vue 3 and Vite

## Technology Stack

- **Frontend**
    - Vue 3
    - Vite

- **Backend / API**
    - PHP 8.3
    - Composer
    - PHPUnit for tests

- **Infrastructure**
    - Docker
    - Docker Compose

## Prerequisites

- Node.js and npm
- PHP 8.3 or compatible for running the API server
- Composer
- Docker and Docker Compose (optional, for containerized environment)

## Project structure (overview)

- `api/` – Laravel REST API application
- `frontend/` – Vue 3 frontend source code
- `docker/` – Docker-related configuration

## Development

### 1. Clone the repository

```bash 
git clone https://github.com/michaltaglewski/booking-app.git
```

### 2. Set up the environment with Docker

First, set up your `.env` file.

```bash
cd booking-app
cp .env.example .env
```

Configure the application service ports or leave them as the default values.

```dotenv
# Public Services Ports
API_PORT=9501
FRONTEND_PORT=8080
DB_PORT=3306
```

**Pay attention to the remaining API and frontend configuration.**

Frontend communication in `frontend/.env`.
```dotenv
VITE_API_BASE_URL=http://localhost:9501
```

API configuration for frontend communication and CORS in `api/.env`.
```dotenv
# Configures CORS allowed origins for the frontend
FRONTEND_APP_URL=http://localhost:8080

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:8080
```

Then build and start the API server.

```bash
docker compose -p booking-app up -d --build
```

Your application is set up and running.
You can access the frontend application at http://localhost:8080 and the backend API at http://localhost:9501.

### 3. Seed the database with sample data
You can run fresh migrations and seed the database with sample data:

```bash
php artisan migrate:fresh --seed
```

After that, you can log in to the frontend application at http://localhost:8080 using the test user credentials:

```
Email: test@example.com
Password: password
```

### 4. PHPUnit tests

```bash
./vendor/bin/phpunit
```
