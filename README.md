# Sinar Roda Utama Task Management System

Sistem manajemen tugas internal untuk **PT. Sinar Roda Utama** yang dibangun menggunakan Laravel 11 dan Vue.js 3. Proyek ini mengimplementasikan API CRUD dengan standar arsitektur Laravel modern dan desain antarmuka berbasis Utility-Driven Design (Tailwind CSS).

## 🚀 Fitur Utama
- **Manajemen Karyawan**: CRUD lengkap untuk data karyawan/penanggung jawab.
- **Manajemen Tugas (Tasks)**: 
    - Penugasan tugas ke karyawan tertentu.
    - Validasi rentang tanggal (Start & End Date).
    - Status tugas otomatis: `Pending`, `Ongoing`, `Done`, dan `Overdue`.
    - Bulk Delete: Menghapus banyak tugas sekaligus menggunakan checklist.
- **Antarmuka Modern**: Dashboard responsif dengan pendekatan Atomic Design.
- **API Versioning**: Menggunakan standar `/api/v1/` untuk skalabilitas.

## 🛠️ Tech Stack
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3 (Composition API)
- **Styling**: Tailwind CSS
- **Database**: SQLite (Default)
- **Build Tool**: Vite

## 📥 Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer Anda:

1. **Clone Proyek**
   ```bash
   git clone <repository-url>
   cd sinarrodautama
   ```

2. **Instalasi Dependencies (Backend)**
   ```bash
   composer install
   ```

3. **Instalasi Dependencies (Frontend)**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Persiapan Database (SQLite)**
   Secara default proyek ini menggunakan SQLite.
   - Buat file database kosong:
     ```bash
     # Windows (PowerShell)
     New-Item database/database.sqlite
     # Linux/Mac
     touch database/database.sqlite
     ```
   - Jalankan Migrasi:
     ```bash
     php artisan migrate
     ```

6. **Menjalankan Proyek**
   Buka dua terminal terpisah:
   - **Terminal 1 (Backend):**
     ```bash
     php artisan serve
     ```
   - **Terminal 2 (Frontend):**
     ```bash
     npm run dev
     ```
   Akses aplikasi di: `http://localhost:8000`

---

## 📄 Dokumentasi Postman

Gunakan JSON di bawah ini untuk menguji API melalui Postman.

### Cara Menggunakan:
1. Copy seluruh kode JSON di bawah.
2. Buka Postman.
3. Klik tombol **Import** (di pojok kiri atas).
4. Pilih tab **Raw text** dan paste kodenya.
5. Klik **Continue** lalu **Import**.

### Postman Collection JSON:
```json
{
	"info": {
		"name": "Sinar Roda Utama API v2 - Complete",
		"schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
	},
	"item": [
		{
			"name": "1. Employees (Karyawan)",
			"item": [
				{
					"name": "List All Employees",
					"request": {
						"method": "GET",
						"header": [ { "key": "Accept", "value": "application/json" } ],
						"url": "http://localhost:8000/api/v1/users"
					}
				},
				{
					"name": "Create Employee",
					"request": {
						"method": "POST",
						"header": [
							{ "key": "Accept", "value": "application/json" },
							{ "key": "Content-Type", "value": "application/json" }
						],
						"body": {
							"mode": "raw",
							"raw": "{\n    \"name\": \"Budi Santoso\",\n    \"email\": \"budi@sinarroda.com\",\n    \"password\": \"password123\"\n}"
						},
						"url": "http://localhost:8000/api/v1/users"
					}
				}
			]
		},
		{
			"name": "2. Tasks",
			"item": [
				{
					"name": "List All Tasks",
					"request": {
						"method": "GET",
						"header": [ { "key": "Accept", "value": "application/json" } ],
						"url": "http://localhost:8000/api/v1/tasks"
					}
				},
				{
					"name": "Create Task",
					"request": {
						"method": "POST",
						"header": [
							{ "key": "Accept", "value": "application/json" },
							{ "key": "Content-Type", "value": "application/json" }
						],
						"body": {
							"mode": "raw",
							"raw": "{\n    \"title\": \"Audit Inventaris\",\n    \"description\": \"Pengecekan stok fisik\",\n    \"start_date\": \"2026-05-20\",\n    \"end_date\": \"2026-05-25\",\n    \"assigned_to\": 1\n}"
						},
						"url": "http://localhost:8000/api/v1/tasks"
					}
				}
			]
		}
	],
	"variable": [
		{
			"key": "base_url",
			"value": "http://localhost:8000/api/v1"
		}
	]
}
```

---
**PT. Sinar Roda Utama** - *Internal Management System v1.0*

*Created by bachtiyar93*
