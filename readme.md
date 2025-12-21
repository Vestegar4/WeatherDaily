# 🌦️ WeatherDaily - Intelligent Activity Planner

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-2.0-885630?style=for-the-badge&logo=composer&logoColor=white)
![License](https://img.shields.io/badge/License-Academic-green?style=for-the-badge)

**WeatherDaily** adalah platform manajemen aktivitas cerdas (*Intelligent Activity Planner*) yang mengintegrasikan data cuaca *real-time* untuk membantu pengguna merencanakan jadwal harian mereka.

> *Proyek ini merupakan Tugas Besar (Final Project) yang mencakup 4 Mata Kuliah Inti: Pemrograman Web, Pemrograman Berorientasi Objek (PBO), Basis Data, dan Analisis Desain Berorientasi Objek (ADBO).*

---
Aplikasi ini dirancang untuk memenuhi kompetensi dari 4 mata kuliah berikut:

1.  **Analisis Desain Berorientasi Objek (ADBO):**
    * Perancangan sistem menggunakan diagram UML (Use Case, Class Diagram, Sequence Diagram).
    * Penerapan pola desain MVC (Model-View-Controller) untuk pemisahan logika yang jelas.

2.  **Pemrograman Web:**
    * Implementasi Backend menggunakan PHP Native (Tanpa Framework).
    * Frontend interaktif dengan Bootstrap 5 dan Chart.js.
    * Penerapan Autentikasi, Session Management, dan Integrasi API Eksternal.

3.  **Basis Data:**
    * Perancangan skema database relasional (ERD) yang normal dan efisien.
    * Implementasi operasi CRUD (Create, Read, Update, Delete) yang kompleks.
    * Penggunaan relasi antar tabel (User, Activity, Notification, WeatherLog).

4.  **Pemrograman Berorientasi Objek (PBO):**
    * Penerapan konsep OOP (Class, Object, Inheritance, Encapsulation) secara penuh dalam kode PHP.
    * Penggunaan *Service Layer* dan *Helper Classes* (seperti `WeatherMonitor`, `MailService`) untuk modularitas kode.

---
### 1. 🧠 Smart Recommendation Engine
Sistem menganalisis input aktivitas pengguna dan membandingkannya dengan data ramalan cuaca.
* **Auto-Detection:** Menolak/memperingatkan aktivitas *Outdoor* saat hujan.
* **Outfit Suggestions:** Saran pakaian (misal: "Bawa Jaket" jika < 22°C, "Pakai Sunscreen" jika > 33°C).

### 2. ⚡ Real-time Weather Dashboard
* Data cuaca diambil langsung dari **OpenWeatherMap API**.
* Visualisasi grafik tren suhu 5 hari ke depan menggunakan **Chart.js**.
* Informasi detail: Kelembaban, Kecepatan Angin, dan Tekanan Udara.

### 3. 🛡️ Keamanan & Autentikasi Tingkat Lanjut
* **Secure Hashing:** Password pengguna dienkripsi dengan (`password_hash`).
* **OTP Verification:** Kode verifikasi dikirim via Email (**PHPMailer**) saat registrasi.
* **Role-Based Access Control (RBAC):** Pemisahan hak akses antara **Admin** dan **User**.

### 4. 🔔 Sistem Notifikasi Multi-Platform
* **In-App Toast:** Notifikasi pop-up *real-time* saat menggunakan aplikasi.
* **Email Alerts:** Peringatan otomatis dikirim ke email jika cuaca berubah drastis pada jadwal penting.

---

##  Alat yang Digunakan

* **Backend:** PHP 8.1 (Native MVC Pattern)
* **Frontend:** HTML5, CSS3, JavaScript (ES6)
* **UI Framework:** Bootstrap 5.3 & Bootstrap Icons
* **Database:** MySQL / MariaDB
* **API Service:** OpenWeatherMap API
* **Libraries:**
    * `phpmailer/phpmailer`: Layanan pengiriman email SMTP.
    * `vlucas/phpdotenv`: Manajemen variabel environment (.env).
    * `chart.js`: Visualisasi data statistik.

---
## Sebelum menginstall, pastikan komputer Anda sudah terinstall:

1.  **Web Server & Database:** XAMPP / Laragon (PHP >= 8.0).
2.  **PHP Dependency Manager:** [Composer](https://getcomposer.org/).
3.  **Node.js & NPM:** [Download Node.js](https://nodejs.org/) (Wajib untuk install Bootstrap & Icons).
4.  **Git:** (Opsional, untuk clone repository).

##  Cara Instalasi
Buka terminal (Git Bash / CMD) dan arahkan ke folder `www`
1.  **Clone Repository:**
    ```bash
    cd C:/laragon/www atau cd C:/xampp/htdocs
    git clone [https://github.com/username/WeatherDaily.git](https://github.com/username/WeatherDaily.git)
    cd WeatherDaily
    ```
2.  **Install Dependencies:**
    ```bashs
    composer install
    ```
    *pastikan anda sudah menginstall npm sebelumnya. 

3. **Install Frontend Depedencies.**
    ```bashs
    npm install
    ```
    *Tanpa langkah ini tampilan website akan polos.

4.  **Setup Database:**
    * Aktifkan Apache dan MySQL di XAMPP/Laragon.
    * Buka phpMyAdmin (http://localhost/phpmyadmin).
    * Buat database baru dengan nama: weatherdaily.
    * Import file database weather_daily.sql yang ada di folder root proyek.

5.  **Konfigurasi .env:**
    * Duplikat `.env.example` menjadi `.env`.
    sesuaikan konfigurasi berikut:
    Database Configuration
    * DB_HOST=localhost
    * DB_NAME=weatherdaily
    * DB_USER=root
    * DB_PASS=

    OpenWeatherMap API Key (Daftar Gratis di openweathermap.org)
    * API_WEATHER_KEY=masukkan_api_key_anda_disini

    * SMTP Email Configuration (Untuk OTP & Notif)
    * SMTP_HOST=smtp.gmail.com
    * SMTP_USER=email_anda@gmail.com
    * SMTP_PASS=password_aplikasi_google_anda
    * SMTP_PORT=587

    Catatan: 
    *untuk SMTP PASS jangan gunakan password email utama
    *semua credentials harus disimpan di file .env dan jangan di commit ke github

6.  **Jalankan:**
    * Akses via browser: `http://localhost/WeatherDaily/app/Views/auth/login.php`

---
👤 Akun Demo

Gunakan kredensial berikut untuk pengujian sistem tanpa perlu registrasi ulang:
| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@weatherdaily.com` | `123456` | Manajemen User, Hapus Aktivitas, Monitoring |
| **User** | `user@weatherdaily.com` | `user123` | Buat Jadwal, Cek Cuaca, Edit Profil |
---
📂 Struktur Direktori (MVC Architecture)
```bash
WeatherDaily/ 
├── app/ 
│   ├── Controllers/   
│   ├── Models/        
│   ├── Views/         
│   └── Services/      
├── config/            
├── public/            
├── vendor/            
├── node_modules/      
└── .env               
```
---
## Tim Pengembang
**Nama: 
* Muhammad Rifqy Hamzah (24416255201016)
* Muhammad Yussuf Abrory (24416255201250)
* Tegar Maulana Akbar (24416255201024)

**Program Studi:** TEKNIK INFORMATIKA

---
Copyright © 2025 WeatherDaily. Disusun untuk memenuhi tugas akhir semester Universitas Buana Perjuangan Karawang.
