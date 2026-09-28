<div align="center">

<img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=700&size=30&pause=1000&color=FF6B35&center=true&vCenter=true&width=600&lines=🍽️+UKK+Dapoer+Yayubi;Website+Catering+Modern;Pesan+Mudah%2C+Makan+Enak!+" alt="Typing SVG" />

<br/>

<img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
<img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
<img src="https://img.shields.io/badge/XAMPP-Local%20Server-FB7A24?style=for-the-badge&logo=xampp&logoColor=white"/>
<img src="https://img.shields.io/badge/Midtrans-Payment%20Gateway-00A0E9?style=for-the-badge&logo=stripe&logoColor=white"/>
<img src="https://img.shields.io/badge/Status-Completed-22C55E?style=for-the-badge"/>

<br/><br/>

> 🏆 **Proyek UKK (Uji Kompetensi Keahlian) — Rekayasa Perangkat Lunak**
> 
> Website pemesanan catering modern berbasis PHP Native dengan sistem admin lengkap, pembayaran digital via Midtrans, dan Yayubi AI Assistant.

<br/>

---

</div>

## 🌟 Tentang Proyek

**Dapoer Yayubi** adalah website catering berbasis web yang dibangun sebagai proyek **UKK (Uji Kompetensi Keahlian)** jurusan **Rekayasa Perangkat Lunak**. Website ini memungkinkan pelanggan memesan berbagai menu catering secara online — mulai dari Tumpeng, Nasi Box, hingga Snack Box — dengan sistem pembayaran digital yang aman dan terintegrasi.

<br/>

## ✨ Fitur Unggulan

<table>
<tr>
<td width="50%">

### 🛒 Fitur Pelanggan
- 🏠 **Home Page** — Landing page informatif & menarik
- 📋 **Katalog Menu** — Tumpeng, Nasi Box, Snack Box
- 🛍️ **Pemesanan Online** — Pesan langsung dari website
- 💳 **Pembayaran Digital** — QRIS, GoPay, ShopeePay via Midtrans
- ✅ **Halaman Sukses** — Konfirmasi pesanan real-time
- 🤖 **Yayubi AI** — Asisten AI untuk rekomendasi menu
- 📝 **Ulasan & Rating** — Beri review setelah makan

</td>
<td width="50%">

### ⚙️ Fitur Admin / Penjual
- 🔐 **Login Aman** — Autentikasi dengan bcrypt password
- 📊 **Dashboard Admin** — Statistik & ringkasan lengkap
- 📦 **Manajemen Produk** — CRUD menu (Create, Read, Update, Delete)
- 🗑️ **Soft Delete** — Nonaktifkan menu tanpa hapus permanen
- 📋 **Manajemen Pesanan** — Update status: Pending → Diproses → Selesai
- 👥 **Manajemen User** — Kelola data pelanggan
- 🏢 **Profil Bisnis** — Halaman profil & info catering

</td>
</tr>
</table>

<br/>

## 🍽️ Katalog Menu

<table align="center">
<tr>
<th>🍚 Tumpeng</th>
<th>📦 Nasi Box</th>
<th>🧁 Snack Box</th>
</tr>
<tr>
<td>

- 🏆 Tumpeng Besar — Rp 600.000
- 🥗 Tumpeng Kecil — Rp 350.000

</td>
<td>

- 🍗 Nasi Dus Ayam Goreng — Rp 20.000
- 🍜 Nasi Dus Lengkap — Rp 25.000
- 🥗 Nasi Dus Gudangan — Rp 20.000
- 🥚 Nasi Dus Kering Tempe — Rp 25.000

</td>
<td>

- 🥟 Kroket, Risole Mayo, Putu Ayu
- 🌿 Lemper, Klepon, Nagasari
- 🍃 Dadar Gulung, Onde Onde, Pukis
- 🍫 Macaroni Schootel, Brownies

</td>
</tr>
</table>

<br/>

## 🗄️ Struktur Database

```sql
📁 project_yayubi
├── 👨‍💼 penjual     — Data admin / penjual catering
├── 👥 pembeli     — Data pelanggan yang mendaftar
├── 🍽️ produk      — Katalog menu catering (dengan soft delete)
├── 📋 pesanan     — Transaksi pemesanan
├── 💳 pembayaran  — Riwayat pembayaran & metode
├── ⭐ ulasan      — Rating & review pelanggan
└── 💬 chat        — Pesan antara pembeli & penjual
```

<br/>

## 🏗️ Struktur Proyek

```
📁 Website Yayubi/
├── 📄 index.php                    ← Entry point (redirect berdasarkan role)
│
├── 📁 Home page/                   ← Landing page & halaman utama
│   ├── home.html / home.php
│   └── index.php
│
├── 📁 tampilan_menu/               ← Katalog & detail menu
│   ├── menu.php                    ← Semua menu
│   ├── nasibox.php                 ← Khusus Nasi Box
│   ├── tumpeng.php                 ← Khusus Tumpeng
│   ├── snack.php                   ← Khusus Snack Box
│   └── 📁 gambar/                  ← Foto-foto menu
│
├── 📁 pembayaran/                  ← Sistem pembayaran
│   ├── index.php                   ← Halaman bayar
│   ├── midtrans_config.php         ← Konfigurasi Midtrans ⚠️
│   ├── midtrans_callback.php       ← Webhook notifikasi pembayaran
│   └── sukses.php                  ← Halaman konfirmasi sukses
│
├── 📁 sistem_admin/                ← Backend admin panel
│   ├── 📁 controller/              ← Logic bisnis (MVC)
│   │   ├── produk.controller.php
│   │   └── user.controller.php
│   ├── 📁 models/                  ← Akses database (MVC)
│   │   ├── produk.models.php
│   │   └── user.models.php
│   ├── 📁 view/                    ← Tampilan halaman (MVC)
│   │   ├── index.php               ← Login
│   │   ├── daftar.php              ← Registrasi
│   │   └── 📁 admin/
│   │       └── dashboard.php       ← Panel admin lengkap
│   ├── 📁 database/
│   │   └── koneksi.php             ← Koneksi MySQL
│   ├── 📁 uploads/                 ← Foto produk yang di-upload
│   └── project_yayubi.sql          ← Dump database lengkap
│
├── 📁 profil_bisnis/               ← Halaman profil Dapoer Yayubi
│
└── 📁 Yayubi AI/                   ← Asisten AI berbasis Gemini
    └── Yayubi AI.html
```

<br/>

## 🚀 Cara Instalasi

### Prasyarat
- ✅ [XAMPP](https://www.apachefriends.org/) (PHP 8.x + MySQL/MariaDB)
- ✅ Web Browser (Chrome / Firefox)
- ✅ Akun [Midtrans](https://midtrans.com/) (untuk fitur pembayaran)

### Langkah Instalasi

**1. Clone repository ini**
```bash
git clone https://github.com/Reizo-Rajendra/UKK-Waroeng-Yayubi.git
```

**2. Pindahkan ke folder htdocs XAMPP**
```
C:\xampp\htdocs\Website Yayubi\
```

**3. Import database**
- Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
- Buat database baru: `project_yayubi`
- Import file: `sistem_admin/project_yayubi.sql`

**4. Konfigurasi Midtrans** *(opsional — untuk fitur bayar)*

Edit file `pembayaran/midtrans_config.php`:
```php
define('MIDTRANS_SERVER_KEY', 'Mid-server-XXXXXX'); // ← isi Server Key kamu
define('MIDTRANS_CLIENT_KEY', 'Mid-client-XXXXXX'); // ← isi Client Key kamu
define('MIDTRANS_IS_PRODUCTION', false);             // false = mode Sandbox
```
> 💡 Dapatkan API Key di [Dashboard Midtrans](https://dashboard.midtrans.com) → Settings → Access Keys

**5. Jalankan XAMPP & buka browser**
```
http://localhost/Website Yayubi/
```

<br/>

## 🔑 Akun Default

| Role | Email | Password |
|------|-------|----------|
| 👨‍💼 Admin | `admin@yayubi.com` | `admin123` |
| 👥 Pembeli 1 | `siti@gmail.com` | `pembeli123` |
| 👥 Pembeli 2 | `budi@gmail.com` | `pembeli123` |

> ⚠️ **Ganti password** setelah instalasi untuk keamanan!

<br/>

## 💳 Pembayaran yang Didukung

<div align="center">

| Metode | Keterangan |
|--------|-----------|
| ![QRIS](https://img.shields.io/badge/QRIS-Universal-red?style=flat-square) | Scan QR dari semua e-wallet |
| ![GoPay](https://img.shields.io/badge/GoPay-Digital-00AAE4?style=flat-square) | Bayar langsung via GoPay |
| ![ShopeePay](https://img.shields.io/badge/ShopeePay-Digital-EE4D2D?style=flat-square) | Bayar lewat ShopeePay |

*Powered by **[Midtrans](https://midtrans.com/)** Payment Gateway 🔒*

</div>

<br/>

## 🧑‍💻 Tech Stack

<div align="center">

| Layer | Teknologi |
|-------|-----------|
| **Backend** | PHP 8.x Native (MVC Pattern) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Server** | Apache (XAMPP) |
| **Payment** | Midtrans Snap API |
| **AI Assistant** | Yayubi AI (Gemini-based) |
| **Auth** | PHP Session + bcrypt |

</div>

<br/>

## 📸 Tampilan Aplikasi

> 🖼️ *Screenshot akan segera ditambahkan*

<br/>

## 📐 Arsitektur MVC

```
User Request
     │
     ▼
   View (tampilan_menu/, sistem_admin/view/)
     │
     ▼
Controller (sistem_admin/controller/)
     │
     ▼
  Model (sistem_admin/models/)
     │
     ▼
Database (project_yayubi — MySQL)
```

<br/>

## 🙏 Kredit & Terima Kasih

<div align="center">

Proyek ini dibuat dengan ❤️ sebagai tugas **UKK Rekayasa Perangkat Lunak**

| | |
|---|---|
| 👨‍💻 **Developer** | [Reizo Rajendra](https://github.com/Reizo-Rajendra) |
| 🏫 **Program** | Uji Kompetensi Keahlian (UKK) |
| 📚 **Jurusan** | Rekayasa Perangkat Lunak |
| 📅 **Tahun** | 2026 |

<br/>

---

<sub>Made with 🔥 by Reizo Rajendra · UKK Dapoer Yayubi · 2026</sub>

</div>
