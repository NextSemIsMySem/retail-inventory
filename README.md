# Retail Inventory Management System

Internal admin panel for managing brands, products, and inventory
for a consumer electronics reseller.

## Tech Stack

-   Laravel
-   Filament
-   MariaDB

## Features

-   Brand management (CRUD)
-   Product management with brand relationships
-   Inventory tracking with low-stock indicators
-   Status-based filtering and quick stock actions

## Setup Instructions

1. Clone the repository
2. Copy `.env.example` to `.env`
3. Configure database credentials (MariaDB)
4. Run `composer install`
5. Run `php artisan migrate --seed`
6. Create a Filament admin user
7. Access `/admin`

## Purpose

Built as part of an internship project to demonstrate CRUD
operations and internal admin tooling using Laravel and Filament.
