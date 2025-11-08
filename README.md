# 🧭 Bintex Archive — Sistem Arsip Flipbook Bergaya RPG Pixel

Sebuah aplikasi internal berbasis **Laravel + Vue 3** untuk mengelola dan menampilkan dokumen PDF sebagai **flipbook interaktif** bergaya **RPG klasik / pixelated**.  
Akses publik hanya untuk melihat konten, sedangkan login internal dikelola oleh admin.

---

## ✨ Fitur Utama

-   🌍 **Publik & Internal**
    -   Publik dapat mengakses tampilan situs (flipbook viewer).
    -   Login internal (username/password dibuat oleh admin).
-   🧱 **Struktur Konten Hirarkis**
    -   **Storage** → **Bintex** → **Dokumen (PDF)**
    -   Mendukung CRUD tanpa batas untuk setiap level.
-   📖 **Flipbook Viewer**
    -   PDF ditampilkan sebagai efek membalik halaman (flipbook interaktif).
    -   Render berbasis **gambar halaman**, bukan file PDF asli.
-   🔒 **Keamanan Dokumen**
    -   File PDF tidak bisa diunduh langsung.
    -   Server-side rendering memastikan user hanya melihat hasil konversi gambar.
-   🎨 **Tema Visual**
    -   Desain bergaya **RPG klasik** dengan **pixel font**, spritesheet UI, dan efek retro.
-   ⚙️ **Manajemen Hak Akses**
    -   Role-based access control: admin, editor, user, dan lainnya.

---

## 🧩 Arsitektur Teknis

### 🖥️ Backend — Laravel

-   **Autentikasi:**  
    Menggunakan **JWT** atau **Laravel Sanctum** untuk mendukung mode SPA.
-   **API CRUD:**  
    Endpoint untuk:
    -   Storage
    -   Bintex
    -   Document
    -   Page (hasil konversi PDF)
-   **File Handling & Security:**
    -   PDF disimpan di **private storage** (tidak bisa diakses via URL publik).
    -   Gambar halaman dilayani melalui endpoint dengan **signed/expiring URLs**.
-   **Job Queue:**
    -   Saat admin mengunggah PDF → sistem menjalankan **job queue** untuk:
        -   Konversi PDF → urutan gambar (per halaman)
        -   Penambahan watermark opsional
    -   Menggunakan **Laravel Queue** (Redis atau Database driver).
-   **Worker & Tools:**
    -   Menggunakan **Imagick** atau **Poppler (`pdftoppm`)** untuk konversi PDF.

---

### 🖼️ Frontend — Vue 3 (Composition API)

-   **Home Page:**  
    Menampilkan daftar **Storage** dalam tampilan pixel art seperti lemari.
-   **Storage Page:**  
    Menampilkan **Bintex** (folder koleksi) sebagai kartu visual.
-   **Viewer Page:**  
    Komponen flipbook yang menampilkan urutan gambar halaman.
-   **UI Design:**
    -   Font pixelated
    -   CSS dengan nearest-neighbor scaling
    -   Spritesheet UI untuk nuansa RPG klasik

---

### 🗄️ Database — MySQL

| Tabel       | Deskripsi                                             |
| ----------- | ----------------------------------------------------- |
| `users`     | Data pengguna dan peran                               |
| `roles`     | Hak akses dan izin                                    |
| `storages`  | Lemari / tempat koleksi utama                         |
| `bintexes`  | Folder atau koleksi dalam storage                     |
| `documents` | Metadata PDF                                          |
| `pages`     | Hasil konversi per halaman (image path, urutan, dsb.) |

---

### ⚙️ Queue & Background Process

-   **Antrian (Queue):** Menggunakan Redis atau Database.
-   **Worker:** Menangani tugas berat seperti:
    -   Konversi PDF ke image sequence.
    -   Penyimpanan & watermark.
-   **Keamanan File:** PDF asli tidak pernah dilayani langsung.

---

## 🔐 Keamanan

-   HTTPS diaktifkan untuk semua koneksi.
-   Proteksi **CSRF** & **rate limiting** di endpoint penting.
-   File PDF tersimpan privat.
-   Signed URL untuk akses sementara pada gambar.
-   Role-based access control untuk dashboard & API.

---

## 🚀 Tech Stack

| Komponen          | Teknologi                       |
| ----------------- | ------------------------------- |
| **Backend**       | Laravel 10+                     |
| **Frontend**      | Vue 3 (Composition API)         |
| **Database**      | MySQL                           |
| **Queue**         | Redis / Database                |
| **PDF Converter** | Imagick / Poppler               |
| **Deployment**    | Self-hosted (server perusahaan) |

---

## 🧰 Rencana Pengembangan

-   [ ] Implementasi sistem peran (RBAC)
-   [ ] Flipbook rendering dengan caching gambar
-   [ ] Mode gelap (dark theme) untuk UI pixel
-   [ ] Watermark dinamis per user (opsional)
-   [ ] Dashboard admin untuk pengelolaan Storage / Bintex / Dokumen

---

## 📜 Lisensi

Proyek ini bersifat **internal dan tidak untuk distribusi publik**.  
Hak cipta © 2025 — [Nama Perusahaan / Tim Pengembang].

---

> 🕹️ _"Membuka dokumen seolah membuka buku sihir dalam dunia RPG pixelated — aman, indah, dan imersif."_
