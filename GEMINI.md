# Gemini Project Configuration

This document provides instructions and context for the Gemini AI assistant to effectively work on this project.

## Project Overview

This is a web application built on the Laravel framework (PHP) for the backend and a modern frontend stack using React, TypeScript, and Inertia.js. Styling is handled by Tailwind CSS. The project is containerized using Docker.

## Key Technologies

- **Backend:** PHP / Laravel
- **Frontend:** React / TypeScript / Inertia.js
- **Database:** SQL (Managed by Laravel Migrations)
- **Styling:** Tailwind CSS
- **Build Tool:** Vite
- **Containerization:** Docker
- **Testing:** PHPUnit / Pest

## Common Development Commands

### Initial Setup

1. **Copy Environment File:** `cp .env.example .env`
2. **Install PHP Dependencies:** `composer install`
3. **Install JS Dependencies:** `npm install`
4. **Start Docker Containers:** `docker-compose up -d` (or check `Makefile` for a helper command)
5. **Generate App Key:** `php artisan key:generate`
6. **Run Database Migrations:** `php artisan migrate`

### Development

- **Run Vite Dev Server:** `npm run dev`
- **Run Backend Tests:** `php artisan test`
- **Run Database Migrations:** `php artisan migrate`
- **Create a new migration:** `php artisan make:migration create_my_new_table`

### Building

- **Build Frontend for Production:** `npm run build`
