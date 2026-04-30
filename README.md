# Task Scheduler

A PHP task scheduling application with a web UI, REST API, and CLI — no framework.

## Requirements

- PHP 8.1+
- Redis 6+
- Composer

## Installation

```bash
git clone <repo>
cd task-scheduler
composer install
cp .env.example .env
```

## Configuration

Edit `.env`:

```
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
APP_ENV=production
LOG_LEVEL=info
```

## Usage

### Web

Point your web server document root at `public/`. All requests route through `public/index.php`.

Example nginx config:

```nginx
server {
    listen 80;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Docker Setup
1. **Requirements**
- Docker
- Docker Compose
2. **Configure environment**
```bash
   cp .env.docker.example .env
```
Edit `.env` if needed.

3. **Set permissions for logs directory**
```bash
   sudo chown -R www-data:www-data logs
   sudo chmod -R 775 logs
```

4. **Build and start containers**
```bash
   docker compose up -d --build
```

5. **Open in browser**
```
http://localhost:8080/
```

Pages:
- `GET /` — home page with quick stats
- `GET /tasks` — task list (loads data via fetch)
- `GET /tasks/create` — create task form

API endpoints:
- `GET  /api/tasks` — list all tasks (optional `?status=pending|running|done|error`)
- `POST /api/tasks` — create task (`{"offset":"+15m","payload":"echo hello"}`)
- `GET  /api/tasks/{id}` — get single task

### CLI

```bash
# Add a task scheduled 15 minutes from now
php cli task:add +15m "echo hello world"

# Add a task scheduled 3 hours from now
php cli task:add +3h "php /path/to/script.php"

# List all tasks
php cli task:list

# List only pending tasks
php cli task:list --status=pending

# with Docker
docker compose exec app php cli <command>
```

### Cron

Add to your crontab to process due tasks every minute:

```
* * * * * /usr/bin/php /path/to/project/cron.php >> /dev/null 2>&1
```

### Tests

Run tests from terminal:

```
composer test
```

## Docker (local development)

### Quick start

```bash
cp .env.docker.example .env
docker compose up --build -d
```

Open http://localhost — the app should respond immediately.

> **Note:** `.env.docker.example` sets `REDIS_HOST=redis` (the Compose service name).
> Do **not** use `.env.example` for Docker — it points to `127.0.0.1`.

### Daily commands

```bash
# CLI commands
docker compose exec app php cli task:list
docker compose exec app php cli task:add +15m "echo hello"

# Run tests
docker compose exec app composer test

# Tail scheduler logs
docker compose logs -f scheduler

# Inspect Redis keys
docker compose exec redis redis-cli KEYS '*'

# Stop containers
docker compose down

# Stop and delete Redis data volume
docker compose down -v
```

### Xdebug

The `app` container has Xdebug 3 pre-installed (`mode=debug,develop`).
Configure your IDE to listen on port **9003** with IDE key **PHPSTORM**.
On Linux the `extra_hosts: host.docker.internal:host-gateway` entry in
`docker-compose.yml` routes Xdebug traffic back to the host automatically.

## Project Structure

```
├── bootstrap.php                    Container setup + provider registration
├── cli                              CLI entry point
├── cron.php                         Cron entry point
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── .dockerignore
├── .env.example
├── .env.docker.example              .env preset for Docker (REDIS_HOST=redis)
├── phpunit.xml
├── config/
│   ├── app.php                      App config (reads from .env)
│   └── database.php                 Redis config (reads from .env)
├── docker/
│   ├── apache/
│   │   └── 000-default.conf         Apache VirtualHost (DocumentRoot → public/)
│   └── php/
│       ├── php.ini                  PHP runtime settings
│       └── xdebug.ini               Xdebug 3 config (port 9003, IDE key PHPSTORM)
├── public/
│   ├── index.php                    Web entry point
│   └── assets/
│       ├── css/app.css
│       └── js/app.js
├── src/
│   ├── Application.php              Web / CLI / cron dispatch
│   ├── Container.php                PSR-style DI container
│   ├── Console/
│   │   ├── Kernel.php               Command registry + usage output
│   │   └── Commands/
│   │       ├── Contracts/
│   │       │   └── CommandInterface.php
│   │       ├── AbstractCommand.php  writeln() / error() helpers
│   │       ├── TaskAddCommand.php
│   │       └── TaskListCommand.php
│   ├── Controller/
│   │   ├── AbstractController.php   json() / view() / redirect() helpers
│   │   ├── PageController.php
│   │   └── TaskController.php       JSON API endpoints
│   ├── Enum/
│   │   └── TaskStatus.php           pending | running | done | error
│   ├── Exception/
│   │   ├── StorageException.php
│   │   └── TaskNotFoundException.php
│   ├── Http/
│   │   ├── Request.php              Parses HTTP input + carries route params
│   │   └── Response.php             Immutable fluent response builder
│   ├── Logger/
│   │   └── FileLogger.php           PSR-3 file logger
│   ├── Model/
│   │   └── Task.php
│   ├── Providers/
│   │   ├── Contracts/
│   │   │   └── ServiceProviderInterface.php
│   │   ├── AppServiceProvider.php        Binds LoggerInterface
│   │   ├── StorageServiceProvider.php    Binds StorageInterface
│   │   └── RepositoryServiceProvider.php Binds TaskRepositoryInterface
│   ├── Repository/
│   │   ├── Contracts/
│   │   │   └── TaskRepositoryInterface.php
│   │   └── RedisTaskRepository.php
│   ├── Router/
│   │   ├── Router.php               HTTP router with {param} pattern matching
│   │   └── Routes/
│   │       ├── WebRoutes.php        Page route definitions
│   │       └── ApiRoutes.php        API route definitions
│   ├── Service/
│   │   ├── TaskService.php          Task creation + retrieval
│   │   └── TaskScheduler.php        Cron runner (pending → running → done/error)
│   ├── Storage/
│   │   ├── Contracts/
│   │   │   └── StorageInterface.php
│   │   └── RedisStorage.php
│   └── Support/
│       ├── EnvLoader.php            Parses .env file into $_ENV / $_SERVER
│       └── Uuid.php                 UUID v4 generator
├── tests/
│   └── Unit/
│       ├── Model/
│       │   └── TaskTest.php
│       ├── Service/
│       │   ├── TaskSchedulerTest.php
│       │   └── TaskServiceTest.php
│       └── Support/
│           └── UuidTest.php
├── views/
│   ├── layout.php
│   ├── home.php
│   └── tasks/
│       ├── create.php
│       └── list.php
└── logs/                            app.log written here (git-ignored)
    └── .gitkeep
```

## Extending Storage Drivers

1. Implement `App\Storage\Contracts\StorageInterface` (5 methods: `get`, `set`, `delete`, `keys`, `exists`).
2. Register your driver in `src/bootstrap.php`:

```php
$container->bind(StorageInterface::class, function (): MyStorage {
    return new MyStorage(/* config */);
});
```

All repository and service code depends only on the interface, so no other changes are needed.
