This App is a smart money-saving tracker built with Laravel 12 (API backend) and React (web dashboard).
It helps users set financial goals, track daily or weekly savings, and maintain streaks to build consistent saving habits.

🚀 Features
💰 Create multiple saving goals
📅 Track daily / weekly savings progress
🔥 Maintain streaks (no missed days!)
📊 Responsive React web dashboard
🔐 Secure RESTful API built with Laravel 12
📈 Real-time goal analytics & summaries
others (Subcription,notification)

---

## Installation

Follow the steps below to get the app running on your local machine.

### Prerequisites

1. PHP >= 8.2
2. Composer
3. MySQL or any other supported database

### Steps to Install:

1. Clone the repository:
    ```bash
    git clone
    cd file path
    ```

2. Install dependencies:
    ```bash
    composer install && npm install
    ```

3. Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```

4. Generate an application key:
    ```bash
    php artisan key:generate
    ```

5. Configure your database settings in the `.env` file.

6. Run migrations to set up the database schema:
    ```bash
    php artisan migrate
    ```

7. Seed the database with the initial data (including feature and package seeder):
    ```bash
    php artisan db:seed
    ```

8. Start the development server:
    ```bash
    npm run build
    ```

9. Start the development server:
    ```bash
    php artisan serve
    ```

10. Visit your application in the browser:
    ```
    http://127.0.0.1:8000
    ```
