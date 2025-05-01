# Laravel 12 + Filament 3 Project

This is a Laravel 12 project using [Filament v3](https://filamentphp.com/docs/3.x) for modern, elegant admin panel interfaces.

---

## 🧰 Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- A database (MySQL, PostgreSQL, SQLite, etc.)

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/amttmg/central-kyc-demo.git
cd central-kyc-demo
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy `.env` and configure your database and other settings:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Build Frontend Assets

```bash
npm run build

php artisan serve
```

---

## 🔐 Admin Panel Login

1. Create a Filament user (if not seeded):

```bash
php artisan make:filament-user
```

2. Access the panel:

```
http://localhost:8000/admin
```

---

## 🧱 File Structure Notes

- Filament Resources: `app/Filament/Resources`
- Models: `app/Models`
- Migrations: `database/migrations`
- Factories: `database/factories`

---

## 🧪 Running Tests

```bash
php artisan test
```

---

## 📦 Deployment

For production:

```bash
php artisan config:cache
php artisan route:cache
php artisan migrate --force
npm run build
```

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
