// path: README.md

# PPOB-SMM

PPOB-SMM adalah panel web untuk layanan Pembayaran Pascabayar Online (PPOB) dan Social Media Marketing (SMM) yang dikembangkan berdasarkan proyek Kincai Payment. Panel ini menyediakan berbagai layanan pembayaran seperti pulsa, paket data, token listrik, pembayaran tagihan, layanan SMM, dan banyak lagi.

## Fitur Utama

- **PPOB Services**: Pulsa, Data, PLN, PDAM, BPJS, Pajak, TV, dan lainnya
- **Social Media Marketing**: Likes, Followers, Comments, Views untuk Instagram, Facebook, TikTok, dll
- **Sistem Deposit**: Deposit via bank transfer, e-wallet, dan pulsa
- **Panel Admin**: Manajemen pengguna, layanan, kategori, transaksi, dan laporan
- **Sistem Referral**: Program undangan untuk pengguna baru
- **Payment Gateway**: Integrasi dengan berbagai metode pembayaran
- **Responsive Design**: Tampilan yang optimal di desktop dan mobile

## Informasi Proyek

### Versi Source Code
Proyek ini adalah modifikasi dari **Web Panel PPOB dan SMM Kincai Payment** yang asli. Versi original dapat ditemukan di:
[MyCoding.ID - Web Panel PPOB dan SMM Kincai Payment](https://mycoding.id/item/113/web-panel-ppob-dan-smm-kincai-payment)

### Pengembang
Proyek ini dimodifikasi dan dikembangkan oleh **Rezr230006**. Sebagai pengembang utama, Rezr230006 bertanggung jawab atas:
- Pengembangan fitur baru
- Peningkatan performa sistem
- Pemeliharaan dan update berkala
- Integrasi payment gateway
- Penyesuaian dengan kebutuhan modern

### Dukung Pengembangan
Jika Anda merasa project ini bermanfaat dan ingin mendukung pengembangan lebih lanjut, Anda dapat memberikan donasi melalui platform Saweria:

[![Saweria](https://img.shields.io/badge/Donasi-Saweria-pink?style=for-the-badge&logo=saweria)](https://saweria.co/rezr230006)

**Link Donasi:** https://saweria.co/rezr230006

Donasi Anda akan digunakan untuk:
- Biaya hosting dan domain
- Pembelian API layanan PPOB
- Pengembangan fitur baru
- Maintenance dan update sistem
- Support teknis kepada pengguna

Terima kasih atas dukungan Anda! 🙏

## Instalasi

1. Ekstrak file ZIP ke folder server web Anda
2. Buat database MySQL dan import `DB kincaipayment.sql`
3. Edit file `config.php` dengan informasi database Anda
4. Atur permission folder:

`chmod -R 755 action/ chmod -R 755 ajax/ chmod -R 755 admin/ chmod -R 755 cron/ chmod -R 755 library/ chmod -R 755 upload/ chmod -R 777 assets/media/`

6. Atur cron job untuk file `cron/check-orders.sh`
7. Akses panel admin melalui `admin/index.php`

## Lisensi

Proyek ini dimodifikasi dari Kincai Payment dan dilisensikan di bawah lisensi yang berlaku untuk versi original. Untuk informasi lisensi lengkap, silakan lihat dokumentasi original dari MyCoding.ID.

## Kontak

Untuk pertanyaan, dukungan teknis, atau kerjasama, silakan hubungi:
- Email: rezr230006@gmail.com
- WhatsApp: +62 857-1203-6145
- Saweria: https://saweria.co/rezr230006
