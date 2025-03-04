# Symfony API Integration Assessment

## Overview
This project is a Symfony-based API integration service that fetches property data from **Sprengnetter** and **Europace** APIs, ensuring data consistency, caching results in **Redis**, and storing the data in a **MySQL database**. A Symfony console command (`sync:properties`) is implemented to synchronize property data efficiently.

## Features
- **Fetch property data** from Sprengnetter and Europace APIs.
- **Ensure data consistency** across APIs by merging fetched results.
- **Implement Redis caching** to optimize API calls and improve performance.
- **Handle API failures** with retries and logging mechanisms.
- **Store data in MySQL database** using Doctrine ORM.
- **Log API request successes and failures** using Monolog.

---
## Installation
### Prerequisites
Ensure you have the following installed:
- PHP 8.1+
- Composer
- MySQL
- Redis
- Symfony CLI (optional but recommended)

### Setup Project
1. **Clone the Repository:**
   ```sh
   git clone https://github.com/yourusername/api-integration.git
   cd api-integration
   ```

2. **Install Dependencies:**
   ```sh
   composer install
   ```

3. **Set Up Environment Variables:**
   Copy the `.env` file and configure your database and API URLs:
   ```sh
   cp .env.example .env
   ```
   Edit the `.env` file and add the following variables:
   ```ini
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
   API_SPRENGNETTER_URL="https://api.sprengnetter.com/properties"
   API_EUROPACE_URL="https://api.europace.de/properties"
   REDIS_URL="redis://127.0.0.1:6379"
   ```

4. **Set Up the Database:**
   ```sh
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Run Redis Server (if not running):**
   ```sh
   redis-server
   ```

---
## Usage
### Sync Property Data
Run the Symfony console command to fetch and store property data:
```sh
php bin/console sync:properties
```

### Check Logs
Logs are stored in `var/log/dev.log` (or `prod.log` for production).
To view logs:
```sh
tail -f var/log/dev.log
```

---
## Code Explanation
### `PropertyService.php`
This service handles:
- Fetching data from both APIs.
- Ensuring consistency by merging data.
- Implementing **Redis caching** (1-hour expiration).
- Handling **API failures** and logging errors.

**Caching Logic:**
- Checks if data is already cached in Redis (`properties_data` key).
- If cached, returns it immediately.
- Otherwise, fetches fresh data, stores it in Redis, and returns it.

### `SyncPropertiesCommand.php`
This Symfony command:
- Calls `PropertyService` to fetch property data.
- Saves data to the database using Doctrine ORM.
- Logs success and failure messages with Monolog.

---
## Assessment Requirements Fulfilled ✅
- ✅ **Fetch property data from Sprengnetter and Europace APIs.**
- ✅ **Ensure data consistency across APIs.**
- ✅ **Implement Redis caching for performance optimization.**
- ✅ **Handle API failures gracefully with retries and logging.**
- ✅ **Store property data in MySQL using Doctrine ORM.**
- ✅ **Use Symfony Dependency Injection for better service management.**
- ✅ **Log API request successes and failures using Monolog.**



