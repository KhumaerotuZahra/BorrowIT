# BorrowIT - Asset Management System

Sistem manajemen peminjaman aset berbasis web untuk PT BPI, dibangun dengan Laravel 11 dan MySQL.

## Fitur

### Admin
- **Dashboard** — Statistik total aset, stok tersedia, pending request, dan active borrow dengan grafik bulanan
- **Manage Users** — CRUD user dengan role (Admin/User) dan departemen
- **Asset Management** — CRUD aset dengan auto-generated Asset ID (BPI-YY-NNNN)
- **Borrow Request** — Approve/Reject/Handover permintaan peminjaman
- **Active Borrow** — Monitoring peminjaman aktif dan proses pengembalian
- **Notification** — Notifikasi real-time untuk setiap aktivitas peminjaman
- **Monthly Borrowing** — Analitik dan laporan peminjaman bulanan

### User
- **Dashboard** — Overview peminjaman aktif, overdue, dan notifikasi
- **My Borrowings** — Request peminjaman baru dan tracking status

### Autentikasi
- Login dengan username/email dan password
- Change Password
- Forgot Password via email (@ptbpi.co.id)
- Reset Password dengan token (expired 60 menit)

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL
- **Frontend:** Blade Templates, Vanilla CSS, JavaScript
- **Icons:** Lucide Icons
- **Charts:** Chart.js
- **Font:** Inter (Google Fonts)

## Instalasi

1. Clone repository
```bash
git clone <repo-url>
cd BorrowIT
```

2. Install dependencies
```bash
composer install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Konfigurasi database di `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=borrowit
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migrasi dan seeder
```bash
php artisan migrate --seed
```

6. Jalankan server
```bash
php artisan serve
```

## Default Login

| Role  | Email              | Password     |
|-------|--------------------|--------------|
| Admin | admin@ptbpi.co.id  | password123  |
| User  | john@ptbpi.co.id   | password123  |

## Struktur Database

- **users** — Data user dengan role dan departemen
- **assets** — Data aset dengan Asset ID otomatis
- **borrowings** — Data peminjaman (pending → approved → active → returned)
- **notifications** — Notifikasi sistem

## SMTP Email

Untuk mengaktifkan fitur email (forgot password, notifikasi), konfigurasi SMTP di `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=mail.ptbpi.co.id
MAIL_PORT=587
MAIL_USERNAME=noreply@ptbpi.co.id
MAIL_PASSWORD=<password>
MAIL_ENCRYPTION=tls
```
