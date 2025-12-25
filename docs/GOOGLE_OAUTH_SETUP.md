# Setup Google OAuth untuk Guru

## Mendapatkan Google OAuth Credentials

### 1. Buka Google Cloud Console
- Kunjungi: https://console.cloud.google.com/
- Login dengan akun Google

### 2. Buat Project Baru (jika belum ada)
- Klik "Select a project" di bagian atas
- Klik "New Project"
- Nama project: `SMK Negeri Kasomalang Attendance`
- Klik "Create"

### 3. Enable Google+ API
- Di sidebar, pilih "APIs & Services" > "Library"
- Cari "Google+ API"
- Klik dan pilih "Enable"

### 4. Buat OAuth Credentials
- Di sidebar, pilih "APIs & Services" > "Credentials"
- Klik "Create Credentials" > "OAuth client ID"
- Pilih "Configure consent screen" jika diminta:
  - User Type: External
  - App name: SMK Negeri Kasomalang Attendance
  - User support email: [email sekolah]
  - Developer contact: [email developer]
  - Scopes: tambahkan `userinfo.email` dan `userinfo.profile`
  - Test users: tambahkan beberapa email @belajar.id untuk testing
  - Save

### 5. Create OAuth Client ID
- Application type: Web application
- Name: Attendance Web App
- Authorized JavaScript origins:
  ```
  http://localhost:8000
  https://your-domain.com
  ```
- Authorized redirect URIs:
  ```
  http://localhost:8000/auth/google/callback
  https://your-domain.com/auth/google/callback
  ```
- Klik "Create"

### 6. Copy Credentials
- Client ID: `xxxxx.apps.googleusercontent.com`
- Client Secret: `xxxxxxxxxxxxxxxx`
- Save ke .env file

## Konfigurasi .env

```env
GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback
```

## Testing OAuth Flow

### 1. Local Development
```bash
php artisan serve
```

### 2. Akses Login Page
- Buka http://localhost:8000/login
- Klik "Login with Google"

### 3. Google OAuth Flow
1. Redirect ke Google login page
2. Login dengan akun @belajar.id
3. Allow permissions
4. Redirect kembali ke aplikasi
5. Auto-login jika user sudah terdaftar

### 4. Troubleshooting

**Error: redirect_uri_mismatch**
- Pastikan URL di Google Console sama persis dengan yang di .env
- Include trailing slash atau tidak, harus sama
- HTTP vs HTTPS harus sama

**Error: Hanya email @belajar.id yang diperbolehkan**
- Google OAuth berhasil, tapi domain email tidak sesuai
- Pastikan menggunakan email @belajar.id

**Error: Akun belum terdaftar**
- User belum didaftarkan oleh admin
- Admin harus create user dengan email @belajar.id terlebih dahulu

## Mendaftarkan User Guru

### Via Admin Panel
1. Login sebagai admin/superadmin
2. Buka "Manajemen Pengguna"
3. Klik "Tambah Guru"
4. Isi form:
   - NIP
   - Nama
   - **Email: [nama]@belajar.id** (penting!)
   - Phone
   - Password (optional, akan di-bypass oleh Google OAuth)
   - Pilih group: "guru"
   - Status: Active
5. Save

### Via Seeder/Import
- Gunakan seeder untuk bulk registration
- Import dari Excel dengan kolom email @belajar.id

## Production Deployment

### 1. Update Google Console
- Tambahkan production domain ke Authorized origins
- Tambahkan production callback URL
- Remove localhost dari list (atau biarkan untuk staging)

### 2. Update .env Production
```env
APP_URL=https://attendance.smkn-kasomalang.sch.id
GOOGLE_CLIENT_ID=[production client id]
GOOGLE_CLIENT_SECRET=[production secret]
GOOGLE_REDIRECT_URI=https://attendance.smkn-kasomalang.sch.id/auth/google/callback
```

### 3. Verifikasi Domain (Google Console)
- Untuk production, Google mungkin minta verifikasi domain
- Ikuti instruksi verifikasi (DNS record atau upload file)

### 4. Publish Consent Screen
- Ubah status dari "Testing" ke "In production"
- Submit for verification jika diperlukan

## Security Best Practices

1. **Never commit .env to git**
   - `.env` sudah ada di `.gitignore`
   - Gunakan `.env.example` sebagai template

2. **Rotate credentials periodically**
   - Generate client secret baru setiap 6-12 bulan
   - Update di production tanpa downtime

3. **Monitor OAuth usage**
   - Check Google Console quotas
   - Set up alerts untuk error rates

4. **Backup strategy**
   - Save client ID & secret di secure location (password manager)
   - Document untuk disaster recovery

## Testing Checklist

- [ ] OAuth redirect works
- [ ] Domain @belajar.id diterima
- [ ] Domain lain ditolak
- [ ] User tidak terdaftar ditolak dengan pesan yang jelas
- [ ] User tidak aktif ditolak
- [ ] Non-guru group ditolak
- [ ] Google ID tersimpan di database setelah pertama kali login
- [ ] Re-login dengan Google berhasil tanpa issue
- [ ] Logout dan login lagi berhasil
- [ ] Session persistent (remember me)
