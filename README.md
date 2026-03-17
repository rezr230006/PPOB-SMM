// path: README.md

# PPOB-SMM Platform

PPOB-SMM adalah platform payment point online billing (PPOB) dan social media marketing (SMM) yang komprehensif untuk jasa pembayaran dan layanan interaksi media sosial.

## Fitur Utama

### 📦 Layanan PPOB
- **Pulsa & Data** - Pulsa reguler, pulsa internasional, paket data, SMS
- **Token Listrik** - Pembayaran token PLN prabayar
- **Pascabayar** - PLN, PDAM, BPJS Kesehatan, HP Postpaid, Internet
- **Multifinance** - Angsuran multifinance
- **Voucher Game** - Voucher game online
- **e-Money** - Dana, OVO, GoPay, LinkAja, ShopeePay
- **WiFi ID** - Pembayaran WiFi ID

### 📱 Layanan SMM
- Instagram: Like, Comment, Follow, Share
- Facebook: Like, Comment, Share
- YouTube: Views, Likes, Subscribers
- Twitter: Likes, Retweets, Followers
- TikTok: Views, Likes, Followers

### 💳 Sistem Pembayaran
- Bank Transfer (BCA, Mandiri, BNI, BSI)
- E-Wallet (DANA, OVO, GoPay, LinkAja)
- Pulsa Transfer (XL, Telkomsel, Indosat)
- Voucher Game (Steam, Google Play, App Store)
- Cryptocurrency (Bitcoin, Ethereum)

### 🎯 Fitur Sistem
- Auto Service Sync (setiap 6 jam)
- Order Status Worker (setiap 5 menit)
- Referral System dengan kode undangan
- Dashboard Admin terintegrasi
- Webhook Security dengan signature validation
- Responsive design untuk mobile & desktop

## Persyaratan Sistem

### Server Requirements
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- cPanel/Plesk untuk shared hosting
- Sertifikat SSL (HTTPS)
- Cron Jobs untuk otomasi

### PHP Extensions
- PDO MySQL
- cURL
- JSON
- OpenSSL
- GD Library atau Imagick
- Mbstring

## Instalasi di Shared Hosting

### 1. Upload File
- Upload seluruh file ke direktori utama hosting (biasanya `public_html`)

### 2. Buat Database
- Login ke cPanel
- Buat database baru melalui "MySQL Databases"
- Buat user database dan berikan semua privilages

### 3. Konfigurasi Database
- Edit file `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_database');
define('DB_PASS', 'password_database');
define('DB_NAME', 'nama_database');