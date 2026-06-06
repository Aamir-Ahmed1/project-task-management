# Project & Task Management System

A role-based project and task management system built with Laravel 12 + Vue 3 SPA.

## Architecture Decisions

- **API-first**: REST API consumed by Vue 3 SPA frontend
- **Vue 3 SPA**: Composition API, Vue Router, Axios, Bootstrap 5 UI
- **Service Layer Pattern**: Business logic extracted into Service classes for clean controller separation
- **RBAC**: Spatie Laravel Permission with 3 roles (Admin, Project Manager, Employee)
- **Sanctum**: Token-based API authentication
- **Auditable Trait**: Eloquent trait for automatic audit logging on model CRUD
- **Enums**: PHP 8.1 enums for type-safe status/priority values
- **Normalized MySQL Schema**: 10 tables with proper foreign keys and indexes

## Roles & Permissions

| Role | Capabilities |
|------|-------------|
| **Admin** | Full system access — CRUD projects/tasks/users, manage assignments, view all data, reports, audit logs |
| **Project Manager** | Manage assigned projects, create/edit tasks within them, reply to work logs, view project reports |
| **Employee** | View assigned tasks, submit work logs, receive deadline notifications |

## Tech Stack

- **Laravel 12** (PHP 8.2)
- **Vue 3** (Composition API, SPA)
- **Bootstrap 5** (UI framework)
- **MySQL** (normalized schema)
- **Laravel Sanctum** (API tokens)
- **Spatie Laravel Permission** (RBAC)
- **Laravel Notifications** (mail + database)

## Setup

```bash
git clone <repo-url>
cd task-manager
composer install
npm install
cp .env.example .env
```

Configure `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file

MAIL_MAILER=log
```

Start:
```bash
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=DatabaseSeeder
npm run build
php artisan serve
```

Open `http://localhost:8000` in your browser.

## Default Users

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | password | Admin |
| manager@example.com | password | Project Manager |
| employee@example.com | password | Employee |

## API Endpoints

### Authentication
- `POST /api/login` — Login (returns token)
- `POST /api/register` — Register (creates employee)
- `POST /api/logout` — Logout (revokes token)
- `GET /api/me` — Get authenticated user
- `POST /api/forgot-password` — Send reset link
- `POST /api/reset-password` — Reset password

### Projects
- `GET /api/projects` — List (filter: status, manager_id, date_from, date_to, search)
- `POST /api/projects` — Create (Admin)
- `GET /api/projects/{id}` — Show
- `PUT /api/projects/{id}` — Update (Admin/PM)
- `DELETE /api/projects/{id}` — Delete (Admin)
- `POST /api/projects/{id}/archive` — Archive (Admin/PM)
- `GET /api/projects/{id}/progress` — Progress overview

### Tasks
- `GET /api/tasks` — List (filter: status, priority, assigned_to, project_id, deadline_from, deadline_to)
- `POST /api/tasks` — Create (Admin/PM)
- `GET /api/tasks/{id}` — Show with work logs
- `PUT /api/tasks/{id}` — Update
- `DELETE /api/tasks/{id}` — Delete (Admin)
- `PATCH /api/tasks/{id}/status` — Update status
- `POST /api/tasks/{id}/assign` — Assign employee
- `GET /api/tasks/{id}/timeline` — Activity timeline

### Work Logs
- `GET /api/tasks/{task}/work-logs` — List logs for task
- `POST /api/tasks/{task}/work-logs` — Submit log (Employee)
- `GET /api/work-logs/{id}` — Show log
- `POST /api/work-logs/{id}/replies` — Reply to log (PM)
- `GET /api/work-logs/{id}/replies` — Get replies

### Notifications
- `GET /api/notifications` — List user notifications
- `POST /api/notifications/{id}/read` — Mark as read
- `POST /api/notifications/read-all` — Mark all as read

### Dashboards
- `GET /api/dashboard/admin` — Admin analytics
- `GET /api/dashboard/project-manager` — PM analytics
- `GET /api/dashboard/employee` — Employee dashboard

### Reports
- `GET /api/reports/projects` — All projects (Admin)
- `GET /api/reports/projects/{id}` — Single project
- `GET /api/reports/employee/{id}` — Employee report

### Audit Logs
- `GET /api/audit-logs` — List (Admin, filterable)

## Scheduler (Background Jobs)

```bash
php artisan notifications:send-deadline-reminders  # Every 10 min
php artisan notifications:process-overdue          # Every 5 min
```

Run the scheduler:
```bash
php artisan schedule:work
```

## Security

- Passwords hashed with Bcrypt
- Sanctum token authentication
- Role-based middleware gates all endpoints
- Input validation via Form Requests
- SQL injection protection via Eloquent
- Soft deletes on projects and tasks
- Audit logging for all model changes

## Database Schema

- **users** — id, name, email, password, is_active, roles (via Spatie)
- **projects** — name, description, dates, status, project_manager_id, created_by
- **tasks** — name, description, priority, status, deadline, hours, project_id, assigned_to
- **work_logs** — task_id, user_id, description, hours_worked, attachment
- **log_replies** — work_log_id, user_id, reply
- **notifications** — polymorphic notifications table
- **audit_logs** — user_id, action, entity_type/id, old/new values, IP
- **permissions/roles** — Spatie permission tables
