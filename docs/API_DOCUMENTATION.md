# API Documentation - Mobile App

Base URL: `http://your-domain.com/api`

## Authentication

All API endpoints (except login) require authentication using Bearer token.

### Headers
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### 1. Teacher/Staff Login
**POST** `/api/login`

**Request Body:**
```json
{
  "email": "guru@belajar.id",
  "password": "password123"
}
```

**Response Success (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "John Doe",
    "email": "guru@belajar.id",
    "group": "guru",
    "nip": "197001011998031001",
    "has_face_encoding": true
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Response Error (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 2. Student Login
**POST** `/api/student/login`

**Request Body:**
```json
{
  "nis": "2024001",
  "password": "password123"
}
```

**Response Success (200):**
```json
{
  "message": "Login successful",
  "student": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "Jane Doe",
    "nis": "2024001",
    "classroom": "X-RPL-A",
    "is_class_leader": true
  },
  "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

### 3. Logout
**POST** `/api/logout`

**Response Success (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

## Schedule Endpoints

### 1. Get All Schedules
**GET** `/api/schedules`

**Response Success (200):**
```json
{
  "message": "Schedules retrieved successfully",
  "data": [
    {
      "id": "01234567-89ab-cdef-0123-456789abcdef",
      "day": "Monday",
      "day_of_week": 1,
      "subject": "Pemrograman Web",
      "classroom": "X-RPL-A",
      "lesson_period": {
        "start_time": "07:30",
        "end_time": "08:15",
        "period_number": 1
      },
      "academic_year": "2024/2025",
      "room_number": "Lab Komputer 1"
    }
  ]
}
```

### 2. Get Today's Schedules
**GET** `/api/schedules/today`

**Response Success (200):**
```json
{
  "message": "Today's schedules retrieved successfully",
  "date": "2025-01-26",
  "day": "Sunday",
  "data": [...]
}
```

### 3. Get Schedule Detail
**GET** `/api/schedules/{id}`

**Response Success (200):**
```json
{
  "message": "Schedule retrieved successfully",
  "data": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "day": "Monday",
    "subject": {
      "id": 1,
      "name": "Pemrograman Web",
      "code": "PWB"
    },
    "classroom": {
      "id": 1,
      "name": "X-RPL-A",
      "level": "10",
      "major": "RPL"
    },
    "lesson_period": {
      "start_time": "07:30",
      "end_time": "08:15",
      "period_number": 1,
      "duration_minutes": 45
    },
    "academic_year": "2024/2025",
    "room_number": "Lab Komputer 1",
    "is_active": true
  }
}
```

---

## Attendance Endpoints

### 1. Check In
**POST** `/api/attendance/check-in`

**Request Body:**
```json
{
  "schedule_id": "01234567-89ab-cdef-0123-456789abcdef",
  "method": "qr_code",
  "latitude": -6.5766,
  "longitude": 107.7467,
  "qr_code": "abc123def456",
  "face_photo": "base64_encoded_photo_string"
}
```

**Parameters:**
- `schedule_id` (required): ID jadwal pembelajaran
- `method` (required): `qr_code`, `face_recognition`, atau `gps`
- `latitude` (required for GPS): GPS latitude
- `longitude` (required for GPS): GPS longitude
- `qr_code` (required for QR): QR code string dari ketua kelas
- `face_photo` (required for Face): Base64 encoded photo

**Response Success (200):**
```json
{
  "message": "Check-in successful",
  "data": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "time": "07:25:00",
    "method": "qr_code",
    "is_valid_gps": true,
    "status": "on_time"
  }
}
```

**Response Error (422):**
```json
{
  "message": "QR code sudah expired"
}
```

### 2. Check Out
**POST** `/api/attendance/check-out`

**Request Body:**
```json
{
  "attendance_id": "01234567-89ab-cdef-0123-456789abcdef",
  "method": "qr_code",
  "latitude": -6.5766,
  "longitude": 107.7467,
  "qr_code": "abc123def456",
  "face_photo": "base64_encoded_photo_string"
}
```

**Response Success (200):**
```json
{
  "message": "Check-out successful",
  "data": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "time_in": "07:25:00",
    "time_out": "08:10:00",
    "duration_minutes": 45,
    "status": "on_time"
  }
}
```

### 3. Get Attendance History
**GET** `/api/attendance/history?page=1`

**Response Success (200):**
```json
{
  "message": "Attendance history retrieved successfully",
  "data": [
    {
      "id": "01234567-89ab-cdef-0123-456789abcdef",
      "date": "2025-01-25",
      "time_in": "07:25:00",
      "time_out": "08:10:00",
      "duration_minutes": 45,
      "status": "on_time",
      "method_in": "qr_code",
      "method_out": "qr_code",
      "subject": "Pemrograman Web",
      "classroom": "X-RPL-A",
      "is_validated": true
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

### 4. Get Today's Attendance
**GET** `/api/attendance/today`

**Response Success (200):**
```json
{
  "message": "Today's attendance retrieved successfully",
  "date": "2025-01-26",
  "data": [
    {
      "id": "01234567-89ab-cdef-0123-456789abcdef",
      "time_in": "07:25:00",
      "time_out": "08:10:00",
      "status": "on_time",
      "subject": "Pemrograman Web",
      "classroom": "X-RPL-A",
      "lesson_period": {
        "start_time": "07:30",
        "end_time": "08:15"
      }
    }
  ]
}
```

### 5. Validate QR Code
**POST** `/api/qr-code/validate`

**Request Body:**
```json
{
  "qr_code": "abc123def456"
}
```

**Response Success (200):**
```json
{
  "valid": true,
  "message": "QR code valid",
  "qr_code_id": "01234567-89ab-cdef-0123-456789abcdef",
  "classroom": "X-RPL-A",
  "valid_until": "2025-01-26 23:59:59"
}
```

**Response Error (200):**
```json
{
  "valid": false,
  "message": "QR code sudah expired"
}
```

### 6. Register Face
**POST** `/api/face/register`

**Request Body:**
```json
{
  "face_photo": "base64_encoded_photo_string"
}
```

**Response Success (200):**
```json
{
  "message": "Face registered successfully",
  "data": {
    "encoding_saved": true,
    "photo_saved": true
  }
}
```

### 7. Verify Face
**POST** `/api/face/verify`

**Request Body:**
```json
{
  "face_photo": "base64_encoded_photo_string"
}
```

**Response Success (200):**
```json
{
  "message": "Face verified successfully",
  "data": {
    "match": true,
    "score": 0.85
  }
}
```

**Response Error (200):**
```json
{
  "message": "Face verification failed",
  "data": {
    "match": false,
    "score": 0.45
  }
}
```

---

## Student Endpoints

### 1. Get Student Profile
**GET** `/api/student/profile`

**Response Success (200):**
```json
{
  "message": "Profile retrieved successfully",
  "data": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "nis": "2024001",
    "nisn": "0012345678",
    "name": "Jane Doe",
    "email": "jane@student.sch.id",
    "phone": "081234567890",
    "gender": "female",
    "birth_date": "2009-01-15",
    "birth_place": "Subang",
    "address": "Jl. Contoh No. 123",
    "classroom": {
      "id": 1,
      "name": "X-RPL-A",
      "level": "10",
      "major": "RPL"
    },
    "is_class_leader": true,
    "is_active": true
  }
}
```

### 2. Get QR Code (Class Leader Only)
**GET** `/api/student/qr-code`

**Response Success (200):**
```json
{
  "message": "QR code retrieved successfully",
  "data": {
    "qr_code": "abc123def456ghi789jkl",
    "valid_from": "2025-01-26 00:00:00",
    "valid_until": "2025-01-26 23:59:59",
    "classroom": "X-RPL-A"
  },
  "schedules": [
    {
      "id": "01234567-89ab-cdef-0123-456789abcdef",
      "subject": "Pemrograman Web",
      "teacher": "John Doe",
      "lesson_period": {
        "start_time": "07:30",
        "end_time": "08:15",
        "period_number": 1
      },
      "room_number": "Lab Komputer 1"
    }
  ]
}
```

**Response Error (403):**
```json
{
  "message": "Only class leaders can access QR codes"
}
```

### 3. Get Class Schedule
**GET** `/api/student/schedule`

**Response Success (200):**
```json
{
  "message": "Schedule retrieved successfully",
  "classroom": "X-RPL-A",
  "data": [
    {
      "day": "Monday",
      "day_of_week": 1,
      "schedules": [
        {
          "id": "01234567-89ab-cdef-0123-456789abcdef",
          "subject": "Pemrograman Web",
          "teacher": "John Doe",
          "lesson_period": {
            "start_time": "07:30",
            "end_time": "08:15",
            "period_number": 1
          },
          "room_number": "Lab Komputer 1"
        }
      ]
    }
  ]
}
```

### 4. Get Today's Schedule
**GET** `/api/student/schedule/today`

**Response Success (200):**
```json
{
  "message": "Today's schedule retrieved successfully",
  "date": "2025-01-26",
  "day": "Sunday",
  "classroom": "X-RPL-A",
  "data": [...]
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server Error",
  "error": "Error description"
}
```

---

## Rate Limiting

API menggunakan Laravel's default rate limiting:
- **60 requests per minute** untuk authenticated users
- **5 requests per minute** untuk login endpoints

Response saat rate limit exceeded:
```json
{
  "message": "Too Many Attempts."
}
```

Headers akan include:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until rate limit resets

---

## Testing dengan Postman/Insomnia

### 1. Login dan dapatkan token
```
POST http://localhost:8000/api/login
Body: { "email": "guru@belajar.id", "password": "password" }
```

### 2. Simpan token dari response

### 3. Gunakan token untuk request berikutnya
```
GET http://localhost:8000/api/schedules/today
Headers: Authorization: Bearer {token}
```

---

## Mobile App Integration Tips

### 1. Token Storage
- Simpan token di secure storage (Keychain/Keystore)
- Tidak pernah simpan di SharedPreferences/UserDefaults plain

### 2. Token Refresh
- Token Sanctum tidak expire by default
- Implementasi re-login jika dapat 401 response

### 3. Network Error Handling
- Implement retry logic dengan exponential backoff
- Show user-friendly error messages

### 4. Offline Mode
- Cache data penting (schedules, attendance history)
- Queue attendance submission jika offline
- Sync saat online kembali

### 5. GPS & Camera Permissions
- Request permissions sebelum attendance
- Fallback ke QR code jika GPS/camera tidak tersedia
