# 📚 Sistem Absensi Guru Berbasis QR Code & Face Recognition

## 📋 Ringkasan Proyek

| Item | Deskripsi |
|------|-----------|
| **Nama Aplikasi** | Sistem Absensi Kehadiran Mengajar Guru |
| **Nama Sekolah** | **SMK Negeri Kasomalang** |
| **Tingkat Pendidikan** | SMK (Sekolah Menengah Kejuruan) |
| **Basis** | Laravel 11 + Livewire 3 + Tailwind CSS |
| **Tujuan** | Mencatat kehadiran guru saat mengajar di kelas |
| **Tanggal Mulai** | 25 Desember 2025 |
| **Multi-Sekolah** | Tidak (Single School Application) |

---

## 🏫 Informasi Sekolah

| Item | Nilai |
|------|-------|
| **Nama** | SMK Negeri Kasomalang |
| **Jam Pelajaran/Hari** | ± 8 jam pelajaran |
| **Durasi Senin-Kamis** | 45 menit per jam pelajaran |
| **Durasi Jumat** | 35 menit per jam pelajaran |
| **Durasi** | **Fleksibel** (dapat diatur oleh Kurikulum) |
| **Istirahat** | Tidak masuk jadwal pelajaran |

---

## 🔄 Perubahan dari Aplikasi Asli

| Aspek           | Sebelumnya            | Sesudah                                                             |
| --------------- | --------------------- | ------------------------------------------------------------------- |
| Target User     | Karyawan              | **Guru**                                                            |
| Jenis Absensi   | Masuk/Pulang Kerja    | **Masuk/Keluar Kelas per Mata Pelajaran**                           |
| Trigger Absensi | Shift Kerja           | **Jadwal Pelajaran**                                                |
| QR Code         | Barcode Lokasi Kantor | **QR Code per Sesi Mengajar (Generated oleh Ketua Kelas)**          |
| Metode Absensi  | QR Code + GPS         | **QR Code + GPS + Face Recognition**                                |
| Role User       | Admin, User           | **Superadmin, Admin, Kurikulum, Kepala Sekolah, Guru, Ketua Kelas** |

---

## 👥 Role & Hak Akses

### 1. Superadmin

-   Semua akses Admin
-   Kelola akun Admin
-   Konfigurasi sistem global
-   Backup & restore data

### 2. Admin

-   Kelola master data (Guru, Siswa, Kelas, Mata Pelajaran)
-   Pengaturan metode absensi
-   Kelola tahun ajaran & semester
-   Kelola kalender akademik
-   Reset password user

### 3. Kurikulum

- Kelola jadwal pelajaran
- **Kelola jam pelajaran (durasi fleksibel per hari)**
- Validasi absensi guru
- **Koreksi absensi guru (perlu approval Kepsek)**
- Monitoring kehadiran real-time
- Generate laporan kehadiran
- Assign guru pengganti
- Approve/reject pengajuan izin guru

### 4. Kepala Sekolah

- Dashboard eksekutif (statistik kehadiran)
- Monitoring kehadiran real-time
- Validasi absensi (final approval)
- **Approval koreksi absensi dari Kurikulum**
- Akses semua laporan
- Notifikasi ketidakhadiran

### 5. Guru

-   Absen masuk kelas
-   Absen keluar/selesai kelas
-   Lihat jadwal mengajar
-   Lihat riwayat absensi sendiri
-   Pengajuan izin/sakit
-   Update foto profil (untuk face recognition)

### 6. Ketua Kelas (Siswa)

-   Login ke sistem
-   Generate QR Code untuk absensi guru
-   QR Code hanya bisa di-generate saat jadwal pelajaran aktif
-   Lihat jadwal kelas

---

## 🛠️ Metode Absensi

### Konfigurasi (Diatur oleh Admin)

| Opsi                        | Deskripsi                                             |
| --------------------------- | ----------------------------------------------------- |
| **QR Code Only**            | Guru hanya bisa absen dengan scan QR dari ketua kelas |
| **Face Recognition Only**   | Guru hanya bisa absen dengan selfie + deteksi wajah   |
| **Both (Pilih Salah Satu)** | Guru bisa memilih metode yang tersedia                |
| **Both (Wajib Keduanya)**   | Guru harus absen dengan kedua metode                  |

### Flow Absensi QR Code

```
1. Jadwal pelajaran aktif
2. Ketua kelas login → Generate QR Code
3. QR Code ditampilkan di layar (berlaku X menit)
4. Guru scan QR Code
5. Sistem verifikasi GPS (dalam radius lokasi sekolah)
6. Absensi tercatat
```

### Flow Absensi Face Recognition

```
1. Jadwal pelajaran aktif
2. Guru buka halaman absensi
3. Guru capture selfie via kamera
4. Sistem deteksi wajah (face-api.js)
5. Sistem cocokkan dengan foto profil guru
6. Sistem verifikasi GPS
7. Absensi tercatat
```

---

## 📊 Database Schema (Entitas)

### Entitas Baru

#### 1. `subjects` (Mata Pelajaran)

| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| code | string | Kode mapel (MTK, BIN, dll) |
| name | string | Nama mata pelajaran |
| category | enum | **wajib, peminatan, muatan_lokal, ekstrakurikuler** |
| description | text | Deskripsi (opsional) |
| created_at | timestamp | |
| updated_at | timestamp | |

#### 2. `classrooms` (Kelas)

| Field            | Type      | Description                |
| ---------------- | --------- | -------------------------- |
| id               | ULID      | Primary key                |
| name             | string    | Nama kelas (X-A, XI-IPA-1) |
| grade            | int       | Tingkat (10, 11, 12)       |
| academic_year_id | foreignId | Relasi tahun ajaran        |
| created_at       | timestamp |                            |
| updated_at       | timestamp |                            |

#### 3. `students` (Siswa)

| Field           | Type      | Description         |
| --------------- | --------- | ------------------- |
| id              | ULID      | Primary key         |
| nis             | string    | Nomor Induk Siswa   |
| name            | string    | Nama lengkap        |
| email           | string    | Email (untuk login) |
| password        | string    | Password            |
| classroom_id    | foreignId | Relasi kelas        |
| is_class_leader | boolean   | Ketua kelas?        |
| phone           | string    | No. HP              |
| created_at      | timestamp |                     |
| updated_at      | timestamp |                     |

#### 4. `schedules` (Jadwal Pelajaran)

| Field            | Type      | Description              |
| ---------------- | --------- | ------------------------ |
| id               | ULID      | Primary key              |
| teacher_id       | foreignId | Relasi guru (users)      |
| subject_id       | foreignId | Relasi mata pelajaran    |
| classroom_id     | foreignId | Relasi kelas             |
| day_of_week      | int       | Hari (1=Senin, 7=Minggu) |
| start_time       | time      | Jam mulai                |
| end_time         | time      | Jam selesai              |
| academic_year_id | foreignId | Tahun ajaran             |
| semester         | int       | Semester (1 atau 2)      |
| is_active        | boolean   | Jadwal aktif?            |
| created_at       | timestamp |                          |
| updated_at       | timestamp |                          |

#### 5. `academic_years` (Tahun Ajaran)

| Field      | Type      | Description         |
| ---------- | --------- | ------------------- |
| id         | ULID      | Primary key         |
| name       | string    | Nama (2024/2025)    |
| start_date | date      | Tanggal mulai       |
| end_date   | date      | Tanggal selesai     |
| is_active  | boolean   | Tahun ajaran aktif? |
| created_at | timestamp |                     |
| updated_at | timestamp |                     |

#### 6. `academic_calendars` (Kalender Akademik)

| Field            | Type      | Description          |
| ---------------- | --------- | -------------------- |
| id               | ULID      | Primary key          |
| academic_year_id | foreignId | Tahun ajaran         |
| date             | date      | Tanggal              |
| title            | string    | Judul kegiatan       |
| type             | enum      | holiday, event, exam |
| description      | text      | Deskripsi            |
| created_at       | timestamp |                      |
| updated_at       | timestamp |                      |

#### 7. `attendance_settings` (Pengaturan Absensi)

| Field                | Type      | Description                                      |
| -------------------- | --------- | ------------------------------------------------ |
| id                   | ULID      | Primary key                                      |
| method               | enum      | qr_only, face_only, both_optional, both_required |
| tolerance_before     | int       | Toleransi menit sebelum jadwal                   |
| tolerance_after      | int       | Toleransi menit setelah jadwal                   |
| qr_expiry_minutes    | int       | Masa berlaku QR Code                             |
| school_latitude      | decimal   | Latitude lokasi sekolah                          |
| school_longitude     | decimal   | Longitude lokasi sekolah                         |
| location_radius      | int       | Radius valid (meter)                             |
| face_match_threshold | decimal   | Threshold kecocokan wajah (0-1)                  |
| updated_at           | timestamp |                                                  |

#### 8. `qr_codes` (QR Code Session)

| Field       | Type      | Description               |
| ----------- | --------- | ------------------------- |
| id          | ULID      | Primary key               |
| schedule_id | foreignId | Relasi jadwal             |
| student_id  | foreignId | Ketua kelas yang generate |
| code        | string    | Unique code dalam QR      |
| date        | date      | Tanggal generate          |
| expires_at  | timestamp | Waktu kadaluarsa          |
| is_used     | boolean   | Sudah digunakan?          |
| created_at  | timestamp |                           |

#### 9. `leave_requests` (Pengajuan Izin)

| Field       | Type      | Description                  |
| ----------- | --------- | ---------------------------- |
| id          | ULID      | Primary key                  |
| teacher_id  | foreignId | Guru yang mengajukan         |
| schedule_id | foreignId | Jadwal yang izin             |
| date        | date      | Tanggal izin                 |
| type        | enum      | sick, permission, other      |
| reason      | text      | Alasan                       |
| attachment  | string    | Lampiran (surat dokter, dll) |
| status      | enum      | pending, approved, rejected  |
| approved_by | foreignId | User yang approve            |
| approved_at | timestamp | Waktu approval               |
| notes       | text      | Catatan dari approver        |
| created_at  | timestamp |                              |
| updated_at  | timestamp |                              |

#### 10. `substitute_teachers` (Guru Pengganti)

| Field                 | Type      | Description            |
| --------------------- | --------- | ---------------------- |
| id                    | ULID      | Primary key            |
| leave_request_id      | foreignId | Relasi pengajuan izin  |
| original_teacher_id   | foreignId | Guru asli              |
| substitute_teacher_id | foreignId | Guru pengganti         |
| schedule_id           | foreignId | Jadwal yang digantikan |
| date                  | date      | Tanggal penggantian    |
| assigned_by           | foreignId | User yang assign       |
| created_at            | timestamp |                        |

### Modifikasi Entitas Existing

#### `users` (Guru & Admin)

| Field           | Type   | Description                                |
| --------------- | ------ | ------------------------------------------ |
| ...             | ...    | (existing fields)                          |
| nip             | string | → Ubah jadi NIP/NIK Guru                   |
| group           | enum   | → Tambah: kurikulum, kepala_sekolah        |
| face_encoding   | text   | Data encoding wajah untuk face recognition |
| face_photo_path | string | Path foto wajah untuk referensi            |

## 👨‍🏫 Data Guru (Teacher Fields)

| Field            | Type      | Description                                      |
|------------------|-----------|--------------------------------------------------|
| id               | ULID      | Primary key                                      |
| nip              | string    | NIP/NIK Guru                                     |
| name             | string    | Nama lengkap                                     |
| email            | string    | Email (hanya yang didaftarkan admin, belajar.id) |
| phone            | string    | No. HP                                           |
| gender           | enum      | Laki-laki, Perempuan                             |
| birth_date       | date      | Tanggal lahir                                    |
| address          | text      | Alamat                                           |
| photo_path       | string    | Path foto profil                                 |
| face_encoding    | text      | Data encoding wajah untuk face recognition       |
| face_photo_path  | string    | Path foto wajah untuk referensi                  |
| group            | enum      | guru, kurikulum, kepala_sekolah, admin           |
| is_active        | boolean   | Status aktif                                     |
| created_at       | timestamp |                                                  |
| updated_at       | timestamp |                                                  |

## 🔑 Autentikasi Guru (Google Auth)

- Guru hanya bisa login menggunakan Google Auth (OAuth2)
- Email yang digunakan harus sudah didaftarkan oleh admin (whitelist)
- Hanya email dengan domain **belajar.id** (contoh: guru.smk.belajar.id) yang diterima
- Guru tidak bisa login otomatis hanya dengan email Google, harus terdaftar di sistem
- Admin dapat menambah/menghapus email guru yang diizinkan login
- Jika email belum terdaftar, login Google akan ditolak
- Email dan data guru diverifikasi oleh admin sebelum diaktifkan

### Flow Login Guru
1. Guru klik "Login dengan Google"
2. Sistem redirect ke Google OAuth
3. Setelah sukses, sistem cek email:
    - Jika email terdaftar & domain belajar.id → login sukses
    - Jika email tidak terdaftar → login gagal, tampil pesan "Email belum terdaftar, hubungi admin"
    - Jika domain bukan belajar.id → login gagal, tampil pesan "Hanya email belajar.id yang diizinkan"
4. Guru masuk dashboard sesuai role

#### `attendances` (Absensi)

| Field                | Type      | Description                                      |
| -------------------- | --------- | ------------------------------------------------ |
| id                   | ULID      | Primary key                                      |
| user_id              | foreignId | Relasi guru                                      |
| schedule_id          | foreignId | **BARU** - Relasi jadwal                         |
| qr_code_id           | foreignId | **BARU** - QR Code yang digunakan                |
| date                 | date      | Tanggal                                          |
| time_in              | time      | Jam masuk kelas                                  |
| time_out             | time      | Jam keluar kelas                                 |
| method_in            | enum      | **BARU** - qr_code, face_recognition             |
| method_out           | enum      | **BARU** - qr_code, face_recognition             |
| latitude_in          | decimal   | GPS saat masuk                                   |
| longitude_in         | decimal   | GPS saat masuk                                   |
| latitude_out         | decimal   | GPS saat keluar                                  |
| longitude_out        | decimal   | GPS saat keluar                                  |
| face_photo_in        | string    | **BARU** - Foto selfie masuk                     |
| face_photo_out       | string    | **BARU** - Foto selfie keluar                    |
| face_match_score_in  | decimal   | **BARU** - Skor kecocokan wajah masuk            |
| face_match_score_out | decimal   | **BARU** - Skor kecocokan wajah keluar           |
| status               | enum      | present, late, excused, sick, absent, substitute |
| validated_by         | foreignId | **BARU** - User yang validasi                    |
| validated_at         | timestamp | **BARU** - Waktu validasi                        |
| validation_status    | enum      | **BARU** - pending, validated, rejected          |
| note                 | text      | Catatan                                          |
| created_at           | timestamp |                                                  |
| updated_at           | timestamp |                                                  |

---

## ⏰ Fitur Toleransi Waktu

### Konfigurasi

-   **tolerance_before**: Menit sebelum jadwal guru boleh absen (default: 15 menit)
-   **tolerance_after**: Menit setelah jadwal guru masih boleh absen (default: 15 menit)

### Status Kehadiran Berdasarkan Waktu

| Kondisi                                     | Status                 |
| ------------------------------------------- | ---------------------- |
| Absen dalam range toleransi                 | `present` (Hadir)      |
| Absen setelah toleransi_after (masuk kelas) | `late` (Terlambat)     |
| Tidak absen sama sekali                     | `absent` (Tidak Hadir) |
| Ada pengajuan izin approved                 | `excused` / `sick`     |
| Digantikan guru lain                        | `substitute`           |

---

## 🔔 Sistem Notifikasi

### Trigger Notifikasi

| Event                                   | Penerima          | Channel                     |
| --------------------------------------- | ----------------- | --------------------------- |
| Guru belum absen 5 menit setelah jadwal | Kurikulum         | Dashboard, Email (optional) |
| Guru tidak hadir tanpa izin             | Kurikulum, Kepsek | Dashboard, Email            |
| Pengajuan izin baru                     | Kurikulum         | Dashboard                   |
| Izin diapprove/reject                   | Guru              | Dashboard                   |
| Guru pengganti di-assign                | Guru Pengganti    | Dashboard                   |

---

## 📈 Laporan & Monitoring

### Dashboard Kurikulum

-   Kehadiran real-time hari ini
-   Daftar guru yang sedang mengajar
-   Daftar guru yang belum absen (jadwal aktif)
-   Daftar pengajuan izin pending
-   Statistik kehadiran mingguan/bulanan

### Dashboard Kepala Sekolah

-   Ringkasan kehadiran seluruh guru
-   Persentase kehadiran per periode
-   Guru dengan tingkat kehadiran terendah
-   Trend kehadiran (grafik)
-   Export laporan

### Jenis Laporan

1. **Laporan Harian** - Kehadiran semua guru per hari
2. **Laporan Mingguan** - Rekap per minggu
3. **Laporan Bulanan** - Rekap per bulan
4. **Laporan per Guru** - Riwayat kehadiran individu
5. **Laporan per Kelas** - Kehadiran guru di kelas tertentu
6. **Laporan per Mata Pelajaran** - Kehadiran per mapel
7. **Laporan Keterlambatan** - Daftar keterlambatan
8. **Laporan Izin/Sakit** - Rekap izin dan sakit

---

## 🔐 QR Code System

### Spesifikasi QR Code

-   **Content**: JSON encoded `{schedule_id, date, code, expires_at}`
-   **Expiry**: Configurable (default: 5 menit)
-   **Single Use**: Tidak bisa digunakan ulang setelah guru absen
-   **Regenerate**: Ketua kelas bisa generate ulang jika expired

### Validasi QR Code

1. QR Code valid dan belum expired
2. QR Code untuk jadwal yang benar
3. Tanggal sesuai
4. Belum digunakan
5. GPS dalam radius sekolah

---

## 👤 Face Recognition System

### Teknologi

-   **Client-side**: face-api.js (TensorFlow.js based)
-   **Models**: TinyFaceDetector, FaceLandmark68Net, FaceRecognitionNet

### Flow

1. **Setup** (Pertama kali): Guru upload/capture foto wajah → Generate face encoding → Simpan ke database
2. **Absensi**: Capture foto → Detect face → Generate encoding → Compare dengan encoding tersimpan

### Konfigurasi

-   **face_match_threshold**: Minimum similarity score (default: 0.6 / 60%)
-   **Liveness Detection**: (Optional) Deteksi wajah asli vs foto

---

## 📁 Struktur Folder (Perubahan)

```
app/
├── Models/
│   ├── User.php (modified)
│   ├── Attendance.php (modified)
│   ├── Subject.php (new)
│   ├── Classroom.php (new)
│   ├── Student.php (new)
│   ├── Schedule.php (new)
│   ├── AcademicYear.php (new)
│   ├── AcademicCalendar.php (new)
│   ├── AttendanceSetting.php (new)
│   ├── QrCode.php (new)
│   ├── LeaveRequest.php (new)
│   └── SubstituteTeacher.php (new)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/ (modified)
│   │   ├── Kurikulum/ (new)
│   │   ├── KepalaSekolah/ (new)
│   │   ├── Teacher/ (new)
│   │   └── Student/ (new)
│   └── Middleware/
│       ├── KurikulumMiddleware.php (new)
│       ├── KepalaSekolahMiddleware.php (new)
│       └── StudentMiddleware.php (new)
├── Livewire/
│   ├── Admin/ (modified)
│   ├── Kurikulum/ (new)
│   ├── KepalaSekolah/ (new)
│   ├── Teacher/ (new)
│   └── Student/ (new)
└── Services/
    ├── QrCodeService.php (new)
    ├── FaceRecognitionService.php (new)
    ├── AttendanceService.php (new)
    └── NotificationService.php (new)

resources/views/
├── admin/ (modified)
├── kurikulum/ (new)
├── kepala-sekolah/ (new)
├── teacher/ (new)
├── student/ (new)
└── components/ (modified)

database/
├── migrations/ (new migrations)
└── seeders/
    ├── SubjectSeeder.php (new)
    ├── ClassroomSeeder.php (new)
    ├── ScheduleSeeder.php (new)
    └── ... (others)
```

---

## 🚀 Tahapan Implementasi

### Phase 1: Database & Models

-   [ ] Buat migration baru
-   [ ] Buat model baru
-   [ ] Update model existing
-   [ ] Buat factory & seeder

### Phase 2: Backend Logic

-   [ ] Service classes
-   [ ] Middleware baru
-   [ ] Controller baru
-   [ ] Update routes

### Phase 3: Admin Panel

-   [ ] CRUD Mata Pelajaran
-   [ ] CRUD Kelas
-   [ ] CRUD Siswa
-   [ ] CRUD Jadwal
-   [ ] CRUD Tahun Ajaran
-   [ ] Kalender Akademik
-   [ ] Pengaturan Absensi

### Phase 4: Kurikulum Panel

-   [ ] Dashboard monitoring
-   [ ] Validasi absensi
-   [ ] Kelola jadwal
-   [ ] Pengajuan izin
-   [ ] Guru pengganti
-   [ ] Laporan

### Phase 5: Kepala Sekolah Panel

-   [ ] Dashboard eksekutif
-   [ ] Monitoring
-   [ ] Validasi final
-   [ ] Laporan & statistik

### Phase 6: Teacher Features

-   [ ] Dashboard guru
-   [ ] Absen QR Code
-   [ ] Absen Face Recognition
-   [ ] Pengajuan izin
-   [ ] Riwayat absensi

### Phase 7: Student (Ketua Kelas) Features

-   [ ] Login siswa
-   [ ] Generate QR Code
-   [ ] Lihat jadwal

### Phase 8: Face Recognition

-   [ ] Integrasi face-api.js
-   [ ] Setup foto profil guru
-   [ ] Capture & verify

### Phase 9: Notification System

-   [ ] Real-time notifications
-   [ ] Email notifications (optional)

### Phase 10: Testing & Polish

-   [ ] Unit tests
-   [ ] Feature tests
-   [ ] UI/UX improvements
-   [ ] Performance optimization

---

## 📝 Catatan Teknis

### Dependencies Baru (JavaScript)

```json
{
    "face-api.js": "^0.22.2"
}
```

### Face-api.js Models (download ke public/models/)

-   tiny_face_detector_model
-   face_landmark_68_model
-   face_recognition_model

### Environment Variables Baru

```env
# Face Recognition
FACE_MATCH_THRESHOLD=0.6

# QR Code
QR_EXPIRY_MINUTES=5

# Attendance
ATTENDANCE_TOLERANCE_BEFORE=15
ATTENDANCE_TOLERANCE_AFTER=15
SCHOOL_LATITUDE=-6.2088
SCHOOL_LONGITUDE=106.8456
LOCATION_RADIUS=100
```

---

## 📞 Kontak & Referensi

- **Repository**: https://github.com/dennsoe/pembelajaran-nekas
- **Laravel Docs**: https://laravel.com/docs/11.x
- **Livewire Docs**: https://livewire.laravel.com/docs
- **Face-api.js**: https://github.com/justadudewhohacks/face-api.js

---

## ⏱️ Sistem Jam Pelajaran (Lesson Periods)

### Konfigurasi Fleksibel
Kurikulum dapat mengatur jam pelajaran dengan durasi berbeda per hari.

### Database: `lesson_periods`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| period_number | int | Jam ke-1, 2, 3, dst |
| day_of_week | int | 1=Senin, 5=Jumat |
| start_time | time | Waktu mulai |
| end_time | time | Waktu selesai |
| duration_minutes | int | Durasi dalam menit |
| academic_year_id | foreignId | Tahun ajaran |
| is_active | boolean | Status aktif |
| created_at | timestamp | |
| updated_at | timestamp | |

### Contoh Konfigurasi Default
| Hari | Jam Ke | Mulai | Selesai | Durasi |
|------|--------|-------|---------|--------|
| Senin-Kamis | 1 | 07:00 | 07:45 | 45 menit |
| Senin-Kamis | 2 | 07:45 | 08:30 | 45 menit |
| ... | ... | ... | ... | ... |
| Jumat | 1 | 07:00 | 07:35 | 35 menit |
| Jumat | 2 | 07:35 | 08:10 | 35 menit |

---

## 📂 Kategori Mata Pelajaran (SMK)

| Kategori | Contoh Mata Pelajaran |
|----------|----------------------|
| **Wajib (Umum)** | Matematika, Bahasa Indonesia, Bahasa Inggris, PKN, Sejarah Indonesia, Pendidikan Agama |
| **Peminatan/Kejuruan** | Produktif sesuai jurusan (TKJ, RPL, Multimedia, dll) |
| **Muatan Lokal** | Bahasa Sunda, Seni Budaya Daerah |
| **Ekstrakurikuler** | Pramuka, PMR, Paskibra, Futsal, dll |

---

## 🔒 Keamanan & Audit

### 1. Activity Log
Mencatat semua aktivitas penting dalam sistem.

#### Database: `activity_logs`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| user_id | foreignId | User yang melakukan |
| user_type | string | User / Student |
| action | string | login, logout, create, update, delete, attendance_in, attendance_out |
| model_type | string | Model yang diubah |
| model_id | string | ID record yang diubah |
| old_values | json | Nilai sebelum perubahan |
| new_values | json | Nilai setelah perubahan |
| ip_address | string | IP Address |
| user_agent | string | Browser/Device info |
| created_at | timestamp | |

#### Aktivitas yang Dicatat
- Login/Logout semua user
- CRUD semua master data
- Absensi masuk/keluar
- Pengajuan & approval izin
- Koreksi absensi
- Perubahan pengaturan sistem

### 2. Session Management
- **Auto Logout**: Session timeout setelah idle (configurable, default: 30 menit)
- **Single Session**: Optional - 1 user hanya bisa login di 1 device

### 3. IP Logging
- Catat IP address saat absensi
- Catat IP address saat login
- Deteksi lokasi anomali (optional)

---

## 📶 Offline Handling & Queue System

### Skenario Offline
Jika internet putus saat guru melakukan absensi:

1. **Data disimpan di Local Storage browser**
2. **Tampilkan status "Pending Sync"**
3. **Auto retry saat koneksi kembali**
4. **Notifikasi berhasil/gagal setelah sync**

### Database: `offline_attendance_queue`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| user_id | foreignId | Guru |
| schedule_id | foreignId | Jadwal |
| type | enum | in, out |
| method | enum | qr_code, face_recognition |
| data | json | Data absensi (foto, GPS, dll) |
| status | enum | pending, processing, completed, failed |
| attempts | int | Jumlah percobaan |
| error_message | text | Pesan error jika gagal |
| created_at | timestamp | Waktu absen offline |
| synced_at | timestamp | Waktu berhasil sync |

### Flow Offline
```
1. Guru absen (internet putus)
2. Data disimpan ke Local Storage + IndexedDB
3. UI menampilkan "⏳ Menunggu koneksi..."
4. Service Worker mendeteksi koneksi kembali
5. Auto sync ke server
6. Server validasi & simpan ke database
7. UI update "✅ Absensi berhasil disinkronkan"
```

---

## 📥 Import Data Massal (Excel)

### 1. Import Guru
| Kolom Excel | Field Database |
|-------------|----------------|
| NIP | nip |
| Nama Lengkap | name |
| Email | email |
| No. HP | phone |
| Jenis Kelamin | gender |
| Tempat Lahir | birth_place |
| Tanggal Lahir | birth_date |
| Alamat | address |
| Pendidikan | education_id |

### 2. Import Siswa
| Kolom Excel | Field Database |
|-------------|----------------|
| NIS | nis |
| Nama Lengkap | name |
| Email | email |
| No. HP | phone |
| Kelas | classroom_id |
| Ketua Kelas | is_class_leader |

### 3. Import Jadwal
| Kolom Excel | Field Database |
|-------------|----------------|
| Kode Guru / NIP | teacher_id |
| Kode Mapel | subject_id |
| Kelas | classroom_id |
| Hari | day_of_week |
| Jam Ke | period_number |

### Template Excel
Sistem menyediakan template Excel untuk download dengan format yang benar.

---

## ✏️ Koreksi Absensi

### Workflow
```
1. Kurikulum menemukan data absensi yang perlu dikoreksi
2. Kurikulum submit koreksi dengan alasan
3. Sistem kirim notifikasi ke Kepala Sekolah
4. Kepala Sekolah review & approve/reject
5. Jika approved, data absensi diupdate
6. Activity log mencatat perubahan
```

### Database: `attendance_corrections`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| attendance_id | foreignId | Absensi yang dikoreksi |
| requested_by | foreignId | Kurikulum yang request |
| old_status | enum | Status lama |
| new_status | enum | Status baru yang diajukan |
| old_time_in | time | Jam masuk lama |
| new_time_in | time | Jam masuk baru |
| old_time_out | time | Jam keluar lama |
| new_time_out | time | Jam keluar baru |
| reason | text | Alasan koreksi |
| attachment | string | Bukti pendukung |
| status | enum | pending, approved, rejected |
| approved_by | foreignId | Kepsek yang approve |
| approved_at | timestamp | Waktu approval |
| rejection_reason | text | Alasan jika ditolak |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 📊 Tracking Jam Mengajar

### Statistik per Guru
| Metrik | Deskripsi |
|--------|-----------|
| Total Jam/Minggu | Jumlah jam mengajar per minggu |
| Total Jam/Bulan | Jumlah jam mengajar per bulan |
| Total Jam/Semester | Jumlah jam mengajar per semester |
| Kehadiran (%) | Persentase kehadiran |
| Keterlambatan | Jumlah terlambat masuk kelas |
| Izin/Sakit | Jumlah hari izin/sakit |

### Database: `teaching_hour_summaries`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| teacher_id | foreignId | Guru |
| academic_year_id | foreignId | Tahun ajaran |
| semester | int | Semester |
| month | int | Bulan (1-12) |
| week | int | Minggu ke- |
| scheduled_hours | int | Jam terjadwal |
| actual_hours | int | Jam terealisasi |
| present_count | int | Jumlah hadir |
| late_count | int | Jumlah terlambat |
| absent_count | int | Jumlah tidak hadir |
| excused_count | int | Jumlah izin |
| sick_count | int | Jumlah sakit |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 🔌 API untuk Mobile App

### Authentication
- `POST /api/auth/login` - Login (Guru/Siswa)
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Guru Endpoints
- `GET /api/teacher/schedule` - Jadwal mengajar
- `GET /api/teacher/attendance/today` - Absensi hari ini
- `POST /api/teacher/attendance/check-in` - Absen masuk
- `POST /api/teacher/attendance/check-out` - Absen keluar
- `GET /api/teacher/attendance/history` - Riwayat absensi
- `POST /api/teacher/leave-request` - Ajukan izin
- `GET /api/teacher/leave-request` - List pengajuan izin

### Siswa (Ketua Kelas) Endpoints
- `GET /api/student/schedule` - Jadwal kelas
- `POST /api/student/qr-code/generate` - Generate QR Code
- `GET /api/student/qr-code/active` - QR Code aktif

### Face Recognition Endpoints
- `POST /api/face/register` - Register wajah guru
- `POST /api/face/verify` - Verifikasi wajah

### API Security
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Rate Limiting**: 60 requests/minute
- **Validation**: Form Request classes

---

## 💾 Backup & Archive System

### 1. Auto Backup Database
| Setting | Default | Deskripsi |
|---------|---------|-----------|
| Frequency | Daily | Setiap hari jam 00:00 |
| Retention | 30 days | Simpan backup 30 hari |
| Storage | Local + Cloud | Simpan di server + cloud (optional) |

### Database: `backup_logs`
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| filename | string | Nama file backup |
| size | bigint | Ukuran file (bytes) |
| type | enum | full, incremental |
| status | enum | success, failed |
| error_message | text | Error jika gagal |
| created_at | timestamp | |

### 2. Archive Tahun Ajaran
Ketika tahun ajaran berakhir:

1. **Archive Data** - Pindahkan ke tabel archive
2. **Generate Report** - Buat laporan akhir tahun
3. **Keep Summary** - Simpan ringkasan statistik
4. **Optional Delete** - Hapus data detail (configurable)

### Database: `archived_attendances`
Struktur sama dengan `attendances` + field:
| Field | Type | Description |
|-------|------|-------------|
| archived_at | timestamp | Waktu diarsipkan |
| archived_by | foreignId | User yang mengarsipkan |

### Archive Policy
- Data absensi detail: Archive setelah 2 tahun
- Data ringkasan: Simpan permanent
- Data guru/siswa: Soft delete jika tidak aktif

---

## 🆕 Entitas Database Tambahan

### `lesson_periods` (Jam Pelajaran)
Lihat section "Sistem Jam Pelajaran" di atas.

### `activity_logs` (Log Aktivitas)
Lihat section "Keamanan & Audit" di atas.

### `offline_attendance_queue` (Antrian Offline)
Lihat section "Offline Handling" di atas.

### `attendance_corrections` (Koreksi Absensi)
Lihat section "Koreksi Absensi" di atas.

### `teaching_hour_summaries` (Ringkasan Jam Mengajar)
Lihat section "Tracking Jam Mengajar" di atas.

### `backup_logs` (Log Backup)
Lihat section "Backup & Archive" di atas.

### `archived_attendances` (Arsip Absensi)
Lihat section "Backup & Archive" di atas.

### `app_settings` (Pengaturan Aplikasi)
| Field | Type | Description |
|-------|------|-------------|
| id | ULID | Primary key |
| key | string | Nama setting |
| value | text | Nilai setting |
| type | enum | string, int, bool, json |
| group | string | Grup setting |
| description | text | Deskripsi |
| updated_at | timestamp | |

---

## 🔄 Update Tahapan Implementasi

### Phase 1: Database & Models ✏️
- [ ] Buat migration baru (termasuk entitas tambahan)
- [ ] Buat model baru
- [ ] Update model existing
- [ ] Buat factory & seeder
- [ ] **Setup Activity Log system**

### Phase 2: Backend Logic
- [ ] Service classes
- [ ] Middleware baru
- [ ] Controller baru
- [ ] Update routes
- [ ] **API Routes (Sanctum)**

### Phase 3: Admin Panel
- [ ] CRUD Mata Pelajaran (dengan kategori)
- [ ] CRUD Kelas
- [ ] CRUD Siswa
- [ ] **CRUD Jam Pelajaran (fleksibel per hari)**
- [ ] CRUD Jadwal
- [ ] CRUD Tahun Ajaran
- [ ] Kalender Akademik
- [ ] Pengaturan Absensi
- [ ] **Import Excel (Guru, Siswa, Jadwal)**
- [ ] **Backup & Restore**

### Phase 4: Kurikulum Panel
- [ ] Dashboard monitoring
- [ ] Validasi absensi
- [ ] Kelola jadwal
- [ ] **Kelola jam pelajaran**
- [ ] Pengajuan izin
- [ ] Guru pengganti
- [ ] **Koreksi absensi**
- [ ] Laporan
- [ ] **Tracking jam mengajar**

### Phase 5: Kepala Sekolah Panel
- [ ] Dashboard eksekutif
- [ ] Monitoring
- [ ] Validasi final
- [ ] **Approval koreksi absensi**
- [ ] Laporan & statistik
- [ ] **Archive tahun ajaran**

### Phase 6: Teacher Features
- [ ] Dashboard guru
- [ ] Absen QR Code
- [ ] Absen Face Recognition
- [ ] Pengajuan izin
- [ ] Riwayat absensi
- [ ] **Statistik jam mengajar**

### Phase 7: Student (Ketua Kelas) Features
- [ ] Login siswa
- [ ] Generate QR Code
- [ ] Lihat jadwal

### Phase 8: Face Recognition
- [ ] Integrasi face-api.js
- [ ] Setup foto profil guru
- [ ] Capture & verify

### Phase 9: Notification & Security
- [ ] Real-time notifications
- [ ] Email notifications (optional)
- [ ] **Activity logging**
- [ ] **Session management (auto logout)**
- [ ] **IP logging**

### Phase 10: API & Offline
- [ ] **REST API untuk mobile**
- [ ] **Offline queue system**
- [ ] **Auto sync**

### Phase 11: Backup & Archive
- [ ] **Auto backup database**
- [ ] **Archive system**
- [ ] **Data retention policy**

### Phase 12: Testing & Polish
- [ ] Unit tests
- [ ] Feature tests
- [ ] API tests
- [ ] UI/UX improvements
- [ ] Performance optimization

---

## 📝 Environment Variables Tambahan

```env
# School Info
SCHOOL_NAME="SMK Negeri Kasomalang"
SCHOOL_LATITUDE=-6.xxxxx
SCHOOL_LONGITUDE=106.xxxxx

# Session
SESSION_LIFETIME=30
SESSION_IDLE_TIMEOUT=30

# Backup
BACKUP_ENABLED=true
BACKUP_FREQUENCY=daily
BACKUP_RETENTION_DAYS=30
BACKUP_CLOUD_ENABLED=false
BACKUP_CLOUD_DISK=s3

# Archive
ARCHIVE_AFTER_YEARS=2
ARCHIVE_AUTO_DELETE=false

# API
API_RATE_LIMIT=60
API_TOKEN_EXPIRY=1440

# Offline
OFFLINE_QUEUE_MAX_ATTEMPTS=3
OFFLINE_QUEUE_RETRY_DELAY=300
```

---

_Dokumen ini akan diupdate seiring perkembangan proyek._

**Last Updated**: 25 Desember 2025
