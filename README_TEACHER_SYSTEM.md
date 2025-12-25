# Sistem Absensi Guru SMK Negeri Kasomalang

Sistem absensi pembelajaran berbasis web untuk SMK Negeri Kasomalang dengan fitur QR Code, Face Recognition, dan GPS Validation.

## 📋 Fitur Utama

### 👨‍🏫 Panel Guru
- **Dashboard**: Jadwal hari ini, statistik kehadiran bulan ini, riwayat absensi
- **Absensi**: Check-in/out pembelajaran dengan 3 metode:
  - QR Code scanning (dari ketua kelas)
  - Face Recognition (verifikasi wajah)
  - GPS Validation (radius 100m dari sekolah)
- **Jadwal Mengajar**: Lihat jadwal perminggu dengan detail mata pelajaran, kelas, dan jam pelajaran
- **Pengajuan Izin**: Submit leave request dengan lampiran dokumen
- **Guru Pengganti**: History sebagai guru pengganti atau digantikan

### 👨‍🎓 Panel Siswa (Ketua Kelas)
- **Dashboard**: Jadwal kelas hari ini dan minggu ini
- **QR Code**: Generate dan display QR code untuk absensi guru
  - Auto-generate setiap hari
  - Refresh manual jika diperlukan
  - Valid hanya untuk hari tersebut
- **Jadwal Kelas**: Lihat jadwal lengkap seminggu

### 📚 Panel Kurikulum
- **Dashboard**: Overview sistem
- **Validasi Absensi**: 
  - Approve/reject attendance records
  - Filter berdasarkan tanggal, guru, status
  - Real-time monitoring dengan auto-refresh
- **Manajemen Jadwal**:
  - CRUD jadwal pembelajaran
  - Conflict detection (bentrok jadwal kelas/guru)
  - Atur mata pelajaran, kelas, guru, periode jam pelajaran
- **Manajemen Izin**:
  - Approve/reject leave requests dari guru
  - Lihat dokumen lampiran
  - Notifikasi ke guru

### 🏫 Panel Kepala Sekolah
- **Dashboard**: 
  - Statistik guru dan kehadiran
  - Grafik trend kehadiran 7 hari terakhir
  - Pending validations dan leave requests
- **Laporan**:
  - Laporan kehadiran guru per periode
  - Laporan pembelajaran per kelas
  - Export to PDF/Excel (upcoming)
- **Validasi & Izin**: Same access as Kurikulum

### 🔧 Panel Admin/Superadmin
- Manajemen master data (mata pelajaran, kelas, tahun akademik, jam pelajaran)
- User management
- System settings
- Import/Export data

## 🛠️ Tech Stack

- **Backend**: Laravel 11.47.0
- **Frontend**: Livewire 3, Tailwind CSS
- **Database**: MySQL/MariaDB
- **Authentication**: 
  - Laravel Jetstream (traditional login)
  - Laravel Socialite (Google OAuth untuk guru)
  - Multi-guard (users & students)
- **API**: Laravel Sanctum (untuk mobile app)
- **Face Recognition**: face-api.js (client-side)
- **GPS**: Haversine formula untuk distance calculation

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Node.js & NPM

### Setup Steps

1. **Clone repository**
```bash
git clone https://github.com/dennsoe/pembelajaran-nekas.git
cd pembelajaran-nekas
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure .env**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_absensi_guru
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (untuk guru)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# GPS Settings (SMK Negeri Kasomalang - Subang)
ATTENDANCE_GPS_LATITUDE=-6.5766
ATTENDANCE_GPS_LONGITUDE=107.7467
ATTENDANCE_GPS_RADIUS=100
```

5. **Database migration & seeding**
```bash
php artisan migrate:fresh --seed
```

6. **Build assets**
```bash
npm run build
# atau untuk development
npm run dev
```

7. **Run application**
```bash
php artisan serve
```

Visit: http://localhost:8000

## 👥 Default Users (After Seeding)

### Staff/Guru
- **Superadmin**: superadmin@smkn-kasomalang.sch.id / password
- **Admin**: admin@smkn-kasomalang.sch.id / password
- **Kepala Sekolah**: kepala@smkn-kasomalang.sch.id / password
- **Kurikulum**: kurikulum@smkn-kasomalang.sch.id / password
- **Guru**: 5 guru dengan berbagai mata pelajaran

### Siswa
- **Ketua Kelas**: NIS 2024001, 2024007, dll (6 siswa per kelas) / password
- **Siswa Regular**: NIS 2024002-006, 2024008-012, dll / password

## 🔐 Authentication Flow

### Guru (Teachers)
1. **Google OAuth** (Recommended):
   - Klik "Login with Google"
   - Hanya email @belajar.id yang diperbolehkan
   - Admin harus mendaftarkan user terlebih dahulu
   
2. **Traditional Login**:
   - Email & password
   - Email verification required

### Siswa (Students)
- Login dengan NIS & password
- Separate guard (student guard)
- Akses terbatas untuk ketua kelas

### Admin/Kurikulum/Kepala Sekolah
- Email & password login
- Role-based access control via middleware

## 📱 Mobile API

API endpoints tersedia untuk mobile app development:

### Authentication
```
POST /api/login
POST /api/student/login
POST /api/logout
```

### Schedules
```
GET /api/schedules
GET /api/schedules/today
GET /api/schedules/{id}
```

### Attendance
```
POST /api/attendance/check-in
POST /api/attendance/check-out
GET /api/attendance/history
GET /api/attendance/today
POST /api/qr-code/validate
POST /api/face/register
POST /api/face/verify
```

### Student
```
GET /api/student/profile
GET /api/student/qr-code
GET /api/student/schedule
GET /api/student/schedule/today
```

## 🗄️ Database Structure

### Main Tables
- **users**: Guru, admin, kurikulum, kepala sekolah
- **students**: Siswa dengan flag ketua kelas
- **subjects**: Mata pelajaran
- **classrooms**: Kelas (X-RPL-A, XI-RPL-B, dll)
- **schedules**: Jadwal pembelajaran (guru, mapel, kelas, hari, jam)
- **lesson_periods**: Jam pelajaran (1-8 dengan waktu mulai/selesai)
- **attendances**: Record kehadiran guru per pembelajaran
- **qr_codes**: QR code untuk absensi (generated by ketua kelas)
- **leave_requests**: Permohonan izin guru
- **substitute_teachers**: History guru pengganti
- **academic_years**: Tahun ajaran
- **academic_calendars**: Kalender akademik
- **attendance_settings**: Pengaturan GPS coordinates, radius, dll
- **activity_logs**: Audit trail semua aktivitas

## 🎯 Attendance Methods

### 1. QR Code
- Ketua kelas generate QR code setiap hari
- Guru scan QR code untuk check-in/out
- QR code valid hanya untuk hari tersebut
- Auto-expire di akhir hari

### 2. Face Recognition
- Guru register wajah di sistem
- Capture foto saat check-in/out
- face-api.js compare dengan foto terdaftar
- Match score threshold: 0.6 (configurable)

### 3. GPS Validation
- Browser request geolocation permission
- Calculate distance dengan Haversine formula
- Radius default: 100m dari titik sekolah
- Fallback jika GPS tidak tersedia

## 🔒 Security Features

- CSRF Protection (Laravel)
- XSS Prevention
- SQL Injection Prevention (Eloquent ORM)
- Password Hashing (bcrypt)
- API Token Authentication (Sanctum)
- Role-based Access Control (Middleware)
- Activity Logging untuk audit trail
- Google OAuth domain whitelist (@belajar.id)

## 📊 Business Logic

### Attendance Status
- **on_time**: Check-in sebelum atau tepat lesson_period.start_time
- **late**: Check-in setelah lesson_period.start_time
- **incomplete**: Check-in tanpa check-out
- **invalid**: Ditolak saat validasi

### Validation Flow
1. Guru melakukan attendance (check-in/out)
2. Status awal: pending validation (is_validated = false)
3. Kurikulum/Kepala Sekolah review attendance
4. Approve → is_validated = true, status tetap
5. Reject → is_validated = true, status = invalid

### Leave Request Flow
1. Guru submit leave request dengan:
   - Tanggal izin
   - Alasan
   - Schedule IDs yang terpengaruh
   - Lampiran (optional)
2. Status: pending
3. Kurikulum/Kepala Sekolah approve/reject
4. Notification dikirim ke guru
5. Jika approved, dapat assign guru pengganti

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up Google OAuth credentials (production domain)
- [ ] Configure GPS coordinates sesuai lokasi sekolah
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure proper file permissions
- [ ] Set up backup schedule
- [ ] Configure queue workers untuk background jobs
- [ ] Set up monitoring (Laravel Telescope optional)

## 🤝 Contributing

Untuk development:
1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

[Your License Here]

## 👨‍💻 Developer

Developed for SMK Negeri Kasomalang

## 📞 Support

Untuk bantuan atau pertanyaan, hubungi:
- Email: [Your Email]
- GitHub Issues: https://github.com/dennsoe/pembelajaran-nekas/issues
