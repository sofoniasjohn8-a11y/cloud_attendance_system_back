# Cloud Attendance System - API Documentation

**For React Frontend Developers**

This document describes the database schema and API structure for the Cloud Attendance System. Use this as your reference when building the React frontend.

---

## 📋 Table of Contents

1. [Database Schema](#database-schema)
2. [Data Models & Relationships](#data-models--relationships)
3. [API Endpoints](#api-endpoints)
4. [Authentication](#authentication)
5. [Usage Examples](#usage-examples)

---

## Database Schema

### 1. **Offices Table**

Stores office/branch locations. Supports multi-office deployments with geolocation-based attendance tracking.

| Column          | Type          | Description                                                                         |
| --------------- | ------------- | ----------------------------------------------------------------------------------- |
| `id`            | BigInt (PK)   | Unique office identifier                                                            |
| `name`          | String        | Office name (e.g., "Addis Ababa HQ", "Bole Branch")                                 |
| `latitude`      | Decimal(10,8) | GPS latitude coordinate                                                             |
| `longitude`     | Decimal(11,8) | GPS longitude coordinate                                                            |
| `radius_meters` | Integer       | Geofence radius (default: 200m). Employees must be within this distance to clock in |
| `created_at`    | Timestamp     | Record creation time                                                                |
| `updated_at`    | Timestamp     | Last update time                                                                    |

**Sample Data:**

```json
{
    "id": 1,
    "name": "Addis Ababa HQ",
    "latitude": 9.032,
    "longitude": 38.7469,
    "radius_meters": 200,
    "created_at": "2026-04-24T10:00:00Z",
    "updated_at": "2026-04-24T10:00:00Z"
}
```

---

### 2. **Schedules Table**

Defines when employees are supposed to work. Essential for calculating if they're "Late".

| Column        | Type        | Description                                                    |
| ------------- | ----------- | -------------------------------------------------------------- |
| `id`          | BigInt (PK) | Unique schedule identifier                                     |
| `user_id`     | BigInt (FK) | Links to users table                                           |
| `day_of_week` | Enum        | Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday |
| `start_time`  | Time        | Expected start time (e.g., 08:30:00)                           |
| `end_time`    | Time        | Expected end time (e.g., 17:30:00)                             |
| `created_at`  | Timestamp   | Record creation time                                           |
| `updated_at`  | Timestamp   | Last update time                                               |

**Unique Constraint:** `(user_id, day_of_week)` — A user can only have one schedule per day.

**Sample Data:**

```json
{
    "id": 1,
    "user_id": 5,
    "day_of_week": "Monday",
    "start_time": "08:30:00",
    "end_time": "17:30:00",
    "created_at": "2026-04-24T10:00:00Z",
    "updated_at": "2026-04-24T10:00:00Z"
}
```

---

### 3. **Attendances Table** ⭐ (Core)

Logs every clock-in/out event. Stores GPS coordinates for audit trail and reporting.

| Column       | Type          | Description                                          |
| ------------ | ------------- | ---------------------------------------------------- |
| `id`         | BigInt (PK)   | Unique attendance record identifier                  |
| `user_id`    | BigInt (FK)   | Employee reference                                   |
| `office_id`  | BigInt (FK)   | Which branch they clocked into                       |
| `work_date`  | Date          | The workday (indexed for fast report queries)        |
| `clock_in`   | Timestamp     | Check-in timestamp (nullable)                        |
| `clock_out`  | Timestamp     | Check-out timestamp (nullable)                       |
| `lat_in`     | Decimal(10,8) | User's latitude at clock-in (nullable)               |
| `lng_in`     | Decimal(11,8) | User's longitude at clock-in (nullable)              |
| `status`     | Enum          | `present`, `late`, `absent`, `half-day`              |
| `remarks`    | Text          | Optional note (e.g., "Power outage", "WFH approved") |
| `created_at` | Timestamp     | Record creation time                                 |
| `updated_at` | Timestamp     | Last update time                                     |

**Unique Constraint:** `(user_id, work_date)` — Prevents duplicate entries for the same day.

**Indexes:**

- `work_date` — Fast retrieval for monthly/daily reports

**Sample Data:**

```json
{
    "id": 42,
    "user_id": 5,
    "office_id": 1,
    "work_date": "2026-04-24",
    "clock_in": "2026-04-24T08:45:30Z",
    "clock_out": "2026-04-24T17:45:00Z",
    "lat_in": 9.032,
    "lng_in": 38.7469,
    "status": "late",
    "remarks": "Traffic jam on way to office",
    "created_at": "2026-04-24T08:45:30Z",
    "updated_at": "2026-04-24T17:45:00Z"
}
```

---

### 4. **Users Table** (Pre-existing)

Built-in Laravel users table. Extended with attendance relationships.

| Column              | Type        | Description                        |
| ------------------- | ----------- | ---------------------------------- |
| `id`                | BigInt (PK) | Unique user identifier             |
| `name`              | String      | Employee name                      |
| `email`             | String      | Email address (unique)             |
| `email_verified_at` | Timestamp   | Email verification time (nullable) |
| `password`          | String      | Hashed password                    |
| `remember_token`    | String      | Remember me token (nullable)       |
| `created_at`        | Timestamp   | Record creation time               |
| `updated_at`        | Timestamp   | Last update time                   |

---

## Data Models & Relationships

### Model Relationships

```
User
  ├── hasMany Schedules
  └── hasMany Attendances

Office
  └── hasMany Attendances

Schedule
  └── belongsTo User

Attendance
  ├── belongsTo User
  └── belongsTo Office
```

### Eloquent Models

#### **Office Model** (`app/Models/Office.php`)

```php
class Office extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
```

#### **Schedule Model** (`app/Models/Schedule.php`)

```php
class Schedule extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

#### **Attendance Model** (`app/Models/Attendance.php`)

```php
class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'office_id',
        'work_date',
        'clock_in',
        'clock_out',
        'lat_in',
        'lng_in',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
```

---

## API Endpoints

> **Note:** All endpoints should be prefixed with `/api/v1` (e.g., `/api/v1/offices`).  
> Setup in `routes/api.php` and secured with authentication middleware.

### **Offices Endpoints**

| Method   | Endpoint        | Description        |
| -------- | --------------- | ------------------ |
| `GET`    | `/offices`      | List all offices   |
| `POST`   | `/offices`      | Create new office  |
| `GET`    | `/offices/{id}` | Get office details |
| `PUT`    | `/offices/{id}` | Update office      |
| `DELETE` | `/offices/{id}` | Delete office      |

**GET /offices** — List all offices

```json
// Response
{
    "data": [
        {
            "id": 1,
            "name": "Addis Ababa HQ",
            "latitude": 9.032,
            "longitude": 38.7469,
            "radius_meters": 200,
            "created_at": "2026-04-24T10:00:00Z",
            "updated_at": "2026-04-24T10:00:00Z"
        }
    ]
}
```

**POST /offices** — Create new office

```json
// Request Body
{
  "name": "Bole Branch",
  "latitude": 9.0100,
  "longitude": 38.7800,
  "radius_meters": 150
}

// Response (201 Created)
{
  "data": {
    "id": 2,
    "name": "Bole Branch",
    "latitude": 9.0100,
    "longitude": 38.7800,
    "radius_meters": 150,
    "created_at": "2026-04-24T11:00:00Z",
    "updated_at": "2026-04-24T11:00:00Z"
  }
}
```

---

### **Schedules Endpoints**

| Method   | Endpoint                    | Description                           |
| -------- | --------------------------- | ------------------------------------- |
| `GET`    | `/schedules`                | List all schedules (with user filter) |
| `POST`   | `/schedules`                | Create new schedule                   |
| `GET`    | `/schedules/{id}`           | Get schedule details                  |
| `PUT`    | `/schedules/{id}`           | Update schedule                       |
| `DELETE` | `/schedules/{id}`           | Delete schedule                       |
| `GET`    | `/users/{userId}/schedules` | Get schedules for a specific user     |

**GET /users/{userId}/schedules** — Get employee's weekly schedule

```json
// Response
{
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "day_of_week": "Monday",
            "start_time": "08:30:00",
            "end_time": "17:30:00",
            "created_at": "2026-04-24T10:00:00Z",
            "updated_at": "2026-04-24T10:00:00Z"
        },
        {
            "id": 2,
            "user_id": 5,
            "day_of_week": "Tuesday",
            "start_time": "08:30:00",
            "end_time": "17:30:00",
            "created_at": "2026-04-24T10:00:00Z",
            "updated_at": "2026-04-24T10:00:00Z"
        }
    ]
}
```

---

### **Attendances Endpoints** ⭐

| Method  | Endpoint                      | Description                        |
| ------- | ----------------------------- | ---------------------------------- |
| `GET`   | `/attendances`                | List attendances with filters      |
| `POST`  | `/attendances`                | Clock in / Create attendance       |
| `PATCH` | `/attendances/{id}`           | Clock out                          |
| `GET`   | `/attendances/{id}`           | Get attendance details             |
| `GET`   | `/users/{userId}/attendances` | Get employee attendance history    |
| `GET`   | `/attendances/date/{date}`    | Get all attendances for a date     |
| `GET`   | `/reports/monthly`            | Generate monthly attendance report |

**POST /attendances** — Clock In (Employee)

```json
// Request Body
{
  "office_id": 1,
  "latitude": 9.0320,
  "longitude": 38.7469,
  "remarks": "On time"
}

// Response (201 Created)
{
  "data": {
    "id": 42,
    "user_id": 5,
    "office_id": 1,
    "work_date": "2026-04-24",
    "clock_in": "2026-04-24T08:45:30Z",
    "clock_out": null,
    "lat_in": 9.0320,
    "lng_in": 38.7469,
    "status": "present",
    "remarks": "On time",
    "created_at": "2026-04-24T08:45:30Z",
    "updated_at": "2026-04-24T08:45:30Z"
  }
}
```

**PATCH /attendances/{id}** — Clock Out

```json
// Request Body
{
  "clock_out": "2026-04-24T17:45:00Z",
  "remarks": "Completed tasks"
}

// Response
{
  "data": {
    "id": 42,
    "user_id": 5,
    "office_id": 1,
    "work_date": "2026-04-24",
    "clock_in": "2026-04-24T08:45:30Z",
    "clock_out": "2026-04-24T17:45:00Z",
    "lat_in": 9.0320,
    "lng_in": 38.7469,
    "status": "present",
    "remarks": "Completed tasks",
    "created_at": "2026-04-24T08:45:30Z",
    "updated_at": "2026-04-24T17:45:00Z"
  }
}
```

**GET /users/{userId}/attendances?from=2026-04-01&to=2026-04-30** — Employee attendance history

```json
// Response
{
    "data": [
        {
            "id": 40,
            "user_id": 5,
            "office_id": 1,
            "work_date": "2026-04-22",
            "clock_in": "2026-04-22T08:30:00Z",
            "clock_out": "2026-04-22T17:30:00Z",
            "lat_in": 9.032,
            "lng_in": 38.7469,
            "status": "present",
            "remarks": null
        },
        {
            "id": 41,
            "user_id": 5,
            "office_id": 1,
            "work_date": "2026-04-23",
            "clock_in": "2026-04-23T09:15:00Z",
            "clock_out": "2026-04-23T17:45:00Z",
            "lat_in": 9.032,
            "lng_in": 38.7469,
            "status": "late",
            "remarks": "Traffic"
        }
    ],
    "pagination": {
        "total": 20,
        "per_page": 15,
        "current_page": 1,
        "last_page": 2
    }
}
```

**GET /reports/monthly?month=04&year=2026&office_id=1** — Monthly Attendance Report

```json
// Response
{
    "data": {
        "month": "April 2026",
        "office": "Addis Ababa HQ",
        "summary": {
            "total_employees": 25,
            "present_count": 480,
            "late_count": 45,
            "absent_count": 15,
            "half_day_count": 10
        },
        "employees": [
            {
                "user_id": 5,
                "name": "John Doe",
                "present": 20,
                "late": 3,
                "absent": 1,
                "half_day": 0,
                "attendance_rate": "86.96%"
            }
        ]
    }
}
```

---

## Authentication

### How It Works

1. Users authenticate via `/api/login` (credentials: email + password)
2. Server returns a token (Laravel Sanctum or similar)
3. Include token in subsequent requests: `Authorization: Bearer {token}`

### Protected Endpoints

All endpoints should require authentication. Add middleware in routes:

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('offices', OfficeController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('attendances', AttendanceController::class);
});
```

---

## Usage Examples

### React Hooks Example

#### Get Employee's Schedule

```javascript
// Fetch user's weekly schedule
const fetchUserSchedule = async (userId) => {
    const response = await fetch(`/api/v1/users/${userId}/schedules`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });
    const { data } = await response.json();
    return data; // Array of schedules for Mon-Sun
};
```

#### Clock In with GPS

```javascript
// Clock in with geolocation
const clockIn = async (officeId, coords) => {
    const response = await fetch("/api/v1/attendances", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            office_id: officeId,
            latitude: coords.latitude,
            longitude: coords.longitude,
            remarks: "",
        }),
    });
    return response.json();
};
```

#### Clock Out

```javascript
// Clock out
const clockOut = async (attendanceId) => {
    const response = await fetch(`/api/v1/attendances/${attendanceId}`, {
        method: "PATCH",
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            clock_out: new Date().toISOString(),
            remarks: "End of day",
        }),
    });
    return response.json();
};
```

#### Get Attendance Report

```javascript
// Fetch monthly report
const fetchMonthlyReport = async (month, year, officeId) => {
    const response = await fetch(
        `/api/v1/reports/monthly?month=${month}&year=${year}&office_id=${officeId}`,
        {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        },
    );
    return response.json();
};
```

---

## Key Features Summary

✅ **Multi-Office Support** — Manage multiple branches with geolocation-based attendance  
✅ **GPS Audit Trail** — Store exact coordinates at clock-in for verification  
✅ **Schedule Management** — Define weekly schedules to calculate "Late" status  
✅ **Attendance Reports** — Monthly/weekly reports with attendance rates  
✅ **Data Integrity** — Unique constraints prevent duplicate entries  
✅ **Scalable Design** — Indexed queries for fast report generation

---

## Next Steps for React Development

1. **Setup API client** — Create axios/fetch wrapper with authentication
2. **Create contexts** — AuthContext (login/logout), AttendanceContext
3. **Build UI Components:**
    - Login form
    - Clock in/out button with GPS
    - Weekly schedule view
    - Monthly attendance report
    - Employee list & management
4. **Implement geolocation** — Use browser Geolocation API
5. **Add error handling** — Network errors, timeout handling, retry logic

---

**API Version:** v1  
**Database:** PostgreSQL  
**Last Updated:** 2026-04-24
