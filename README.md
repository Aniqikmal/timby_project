# 🪵 Timby — Eco-Friendly Wooden Toys

A full-stack PHP e-commerce web application for a premium eco-friendly wooden toys brand.

Built with **PHP**, **MySQL (mysqli)**, and a custom **Arboreal Elegance** design system.

![Timby Homepage](images/shop2.png)

---

## ✨ Features

| Role | Features |
|---|---|
| 🛍️ **Public** | Browse products, search, view product details, read reviews, newsletter subscribe |
| 👤 **Member** | Register/Login, shopping cart, checkout, view orders, write reviews, custom requests |
| ⚙️ **Admin** | Manage products (add/edit/delete), view members, manage transactions & order statuses |
| 📢 **Marketing** | View and manage promotional coupons, newsletter subscribers, custom requests |

---

## 🗂️ Project Structure

```
timby_project/
├── index.php              # Homepage / product listing
├── login.php              # Login page
├── register.php           # Registration page
├── member_dashboard.php   # Member orders & notifications
├── admin_dashboard.php    # Admin control panel
├── marketing_dashboard.php# Marketing panel
├── product_details.php    # Single product + reviews
├── view_cart.php          # Shopping cart
├── checkout.php           # Checkout form
├── receipt.php            # Order receipt
├── edit_profile.php       # Member profile editor
├── request_custom.php     # Custom toy request form
├── db_conn.php            # ⚠️ NOT in Git — see db_conn.example.php
├── db_conn.example.php    # ✅ DB config template (copy & rename)
├── timbydb_setup.sql      # Database schema + sample data
├── style.css              # Arboreal Elegance design system
└── images/                # Product and UI images
```

---

## 🚀 Local Setup (XAMPP)

### 1. Clone the repository
```bash
git clone https://github.com/YOUR_USERNAME/timby_project.git
```
Place it inside `C:\xampp\htdocs\`

### 2. Set up the database
1. Open **XAMPP Control Panel** → Start **Apache** and **MySQL**
2. Go to **http://localhost/phpmyadmin**
3. Click **Import** → select `timbydb_setup.sql` → click **Go**

### 3. Configure the database connection
```bash
# Copy the example config
copy db_conn.example.php db_conn.php
```
Then open `db_conn.php` and confirm the local settings:
```php
define('DB_ENV', 'local');
// localhost / root / "" / timbydb
```

### 4. Open the app
```
http://localhost/timby_project/
```

---

## 🔑 Test Login Credentials

| Role | Email | Password |
|---|---|---|
| Admin | `admin@timby.com` | `password` |
| Marketing | `marketing@timby.com` | `password` |
| Member | `ahmad@email.com` | `password` |

> ⚠️ These are sample credentials from `timbydb_setup.sql`. Change them in production!

---

## 🎨 Design System — *Arboreal Elegance*

| Token | Value |
|---|---|
| Primary Brown | `#8B5E3C` |
| Cream Background | `#fbfbe2` |
| Dark Teal | `#446464` |
| Headline Font | EB Garamond |
| Body Font | Plus Jakarta Sans |

---

## 🛠️ Tech Stack

- **Backend**: PHP 8+ (procedural mysqli)
- **Database**: MySQL / MariaDB
- **Frontend**: Vanilla HTML + CSS (no frameworks)
- **Design**: Custom CSS design system (CSS variables)
- **Server**: Apache (XAMPP)

---

## ⚠️ Security Notes

- `db_conn.php` is excluded from git via `.gitignore` — **never commit live credentials**
- Passwords are hashed with `password_hash()` / `password_verify()`
- SQL queries use **prepared statements** with `bind_param()`
- All user inputs are sanitized with `htmlspecialchars()` and `intval()`

---

## 📄 License

This project was developed as a university software engineering project.

---

*Made with 🌿 by the Timby Team — UNIMAS*
