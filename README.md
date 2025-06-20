# 🏨 Hotel Management System (Team Member 2)

This is a QloApps-inspired hotel management system module built using:

- ✅ PHP (with MySQLi)
- ✅ Bootstrap 5
- ✅ Modern CSS (Qlo-style)
- ✅ Fully responsive design

## 👥 Developed for:
- **Team Member 2**
  - Check-In & Check-Out
  - Billing and Add-On Services
  - Logs, Role Access, Cron Scripts

---

## 📂 Folder Structure Overview

├── checkin/ # Guest check-in interface
├── checkout/ # Guest checkout, express checkout
├── billing/ # Billing modules and utils
├── includes/ # Shared header, footer, auth, db
├── assets/ # CSS, JS, images, fonts
├── dashboard/ # Clerk dashboard
├── cronjobs/ # Scheduled tasks
├── reservations/ # Auto cancel / no-show handlers
├── logs/ # Clerk action logs
├── middleware/ # Hidden rule enforcement
├── auth/ # login/logout
├── database/ # schema.sql
├── lang/ # Language support
├── index.php # Role-based dashboard redirect
└── README.md
---

## ⚙️ Setup Guide

### ✅ Requirements

- PHP 8.x
- MySQL/MariaDB
- Apache (XAMPP, WAMP, MAMP)
- Composer (optional, not required)

### 🛠️ Installation Steps

1. Clone/download the repo and extract to your `htdocs/` (XAMPP)
2. Import `/database/schema.sql` into a MySQL DB named `hotel_management`
3. Configure `/includes/db.php` with your DB credentials
4. Add a test user to `users` table:

```sql
INSERT INTO users (username, password, role) VALUES (
  'clerk1',
  '$2y$10$Y3yJSExDpFk7BXfEIT7SMe3nQU0nFgq7sR2UulbIuHbQ4m/3JKJOy', -- password: 12345
  'clerk'
);
