# Working API Endpoints Guide

All endpoints are available at `http://localhost:8000/api` (or your configured base URL)

## Quick Start

The following endpoints are fully implemented and ready to use.

> **Authentication:** Most endpoints require a Bearer token. Get one by calling `POST /api/register` or `POST /api/login`, then include it in all protected requests:
> ```
> Authorization: Bearer <your_token>
> ```

---

## 🔐 **Authentication Endpoints**

### 1. **POST /api/register** - Register a new user

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
  }'
```

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
}
```

> `office_id` is optional — if omitted, the first available office is auto-assigned. Ethiopian work schedules (morning + afternoon shifts) are also auto-created for the user.

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Registration successful",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user",
        "office_id": 1,
        "office": {
            "id": 1,
            "name": "Addis Ababa HQ",
            "latitude": 9.032,
            "longitude": 38.7469,
            "radius_meters": 200
        },
        "created_at": "2026-04-24T10:00:00Z",
        "updated_at": "2026-04-24T10:00:00Z"
    }
}
```

---

### 2. **POST /api/login** - Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "secret123"
  }'
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Login successful",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user",
        "office_id": 1,
        "office": {
            "id": 1,
            "name": "Addis Ababa HQ",
            "latitude": 9.032,
            "longitude": 38.7469,
            "radius_meters": 200
        }
    }
}
```

**Failed login (401):**

```json
{
    "success": false,
    "message": "Invalid email or password"
}
```

---

### 3. **POST /api/logout** - Logout 🔒

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer <your_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 4. **GET /api/me** - Get current user 🔒

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer <your_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2026-04-24T10:00:00Z",
        "updated_at": "2026-04-24T10:00:00Z"
    }
}
```

---

## 🏢 **Office Management Endpoints**

### 1. **GET /api/offices** - List all offices

```bash
curl -X GET http://localhost:8000/api/offices
```

**Response (200 OK):**

```json
{
    "success": true,
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

---

### 2. **POST /api/offices** - Create a new office 🔒

```bash
curl -X POST http://localhost:8000/api/offices \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Bole Branch",
    "latitude": 9.0100,
    "longitude": 38.7800,
    "radius_meters": 150
  }'
```

**Request Body:**

```json
{
    "name": "Bole Branch",
    "latitude": 9.01,
    "longitude": 38.78,
    "radius_meters": 150
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "Bole Branch",
        "latitude": 9.01,
        "longitude": 38.78,
        "radius_meters": 150,
        "created_at": "2026-04-24T11:00:00Z",
        "updated_at": "2026-04-24T11:00:00Z"
    },
    "message": "Office created successfully"
}
```

---

### 3. **GET /api/offices/{id}** - Get specific office

```bash
curl -X GET http://localhost:8000/api/offices/1
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Addis Ababa HQ",
        "latitude": 9.032,
        "longitude": 38.7469,
        "radius_meters": 200,
        "created_at": "2026-04-24T10:00:00Z",
        "updated_at": "2026-04-24T10:00:00Z"
    }
}
```

---

### 4. **PUT /api/offices/{id}** - Update office 🔒

```bash
curl -X PUT http://localhost:8000/api/offices/1 \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "radius_meters": 250,
    "name": "Addis Ababa Main HQ"
  }'
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Addis Ababa Main HQ",
        "latitude": 9.032,
        "longitude": 38.7469,
        "radius_meters": 250,
        "created_at": "2026-04-24T10:00:00Z",
        "updated_at": "2026-04-24T11:15:00Z"
    },
    "message": "Office updated successfully"
}
```

---

### 5. **DELETE /api/offices/{id}** - Delete office 🔒

```bash
curl -X DELETE http://localhost:8000/api/offices/2 \
  -H "Authorization: Bearer <your_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Office deleted successfully"
}
```

---

## 📅 **Schedule Management Endpoints**

### 1. **GET /api/schedules** - List all schedules

```bash
curl -X GET http://localhost:8000/api/schedules
```

**With user filter:**

```bash
curl -X GET "http://localhost:8000/api/schedules?user_id=5"
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "day_of_week": "Monday",
            "start_time": "08:30:00",
            "end_time": "17:30:00",
            "created_at": "2026-04-24T10:00:00Z",
            "updated_at": "2026-04-24T10:00:00Z",
            "user": {
                "id": 5,
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ]
}
```

---

### 2. **POST /api/schedules** - Create schedule 🔒

```bash
curl -X POST http://localhost:8000/api/schedules \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 5,
    "day_of_week": "Monday",
    "start_time": "08:30:00",
    "end_time": "17:30:00"
  }'
```

**Request Body:**

```json
{
    "user_id": 5,
    "day_of_week": "Monday",
    "start_time": "08:30:00",
    "end_time": "17:30:00"
}
```

**Valid days:** Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday

**Response (201 Created):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 5,
        "day_of_week": "Monday",
        "start_time": "08:30:00",
        "end_time": "17:30:00",
        "created_at": "2026-04-24T10:00:00Z",
        "updated_at": "2026-04-24T10:00:00Z",
        "user": {
            "id": 5,
            "name": "John Doe",
            "email": "john@example.com"
        }
    },
    "message": "Schedule created successfully"
}
```

---

### 3. **GET /api/schedules/{id}** - Get specific schedule

```bash
curl -X GET http://localhost:8000/api/schedules/1
```

---

### 4. **PUT /api/schedules/{id}** - Update schedule

```bash
curl -X PUT http://localhost:8000/api/schedules/1 \
  -H "Content-Type: application/json" \
  -d '{
    "start_time": "09:00:00",
    "end_time": "18:00:00"
  }'
```

---

### 5. **DELETE /api/schedules/{id}** - Delete schedule

```bash
curl -X DELETE http://localhost:8000/api/schedules/1
```

---

## 👔 **Attendance Tracking Endpoints** ⭐

### 1. **GET /api/attendances** - List attendances

```bash
curl -X GET http://localhost:8000/api/attendances
```

**With filters:**

```bash
# By user
curl -X GET "http://localhost:8000/api/attendances?user_id=5"

# By date
curl -X GET "http://localhost:8000/api/attendances?date=2026-04-24"

# Date range
curl -X GET "http://localhost:8000/api/attendances?from=2026-04-01&to=2026-04-30"

# By office
curl -X GET "http://localhost:8000/api/attendances?office_id=1"
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": [
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
            "remarks": "Traffic jam",
            "created_at": "2026-04-24T08:45:30Z",
            "updated_at": "2026-04-24T17:45:00Z",
            "user": {
                "id": 5,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "office": {
                "id": 1,
                "name": "Addis Ababa HQ",
                "latitude": 9.032,
                "longitude": 38.7469
            }
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

---

### 2. **POST /api/attendances** - Clock In ⭐ (MOST IMPORTANT) 🔒

> `office_id` is no longer required — the user's assigned office is used automatically.

```bash
curl -X POST http://localhost:8000/api/attendances \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 9.0320,
    "longitude": 38.7469,
    "remarks": "On time"
  }'
```

**Request Body:**

```json
{
    "latitude": 9.032,
    "longitude": 38.7469,
    "remarks": "On time"
}
```

> The system auto-detects which schedule slot (morning or afternoon) the clock-in belongs to based on current time. A user can clock in once per shift per day.

**Response (201 Created):**

```json
{
    "success": true,
    "data": {
        "id": 42,
        "user_id": 5,
        "office_id": 1,
        "work_date": "2026-04-24",
        "clock_in": "2026-04-24T08:45:30Z",
        "clock_out": null,
        "lat_in": 9.032,
        "lng_in": 38.7469,
        "status": "present",
        "remarks": "On time",
        "created_at": "2026-04-24T08:45:30Z",
        "updated_at": "2026-04-24T08:45:30Z"
    },
    "message": "Clocked in successfully. Status: present"
}
```

**Status Determination:**

- `present` - User clocked in on time
- `late` - User clocked in after scheduled start time
- `absent` - No schedule exists for this day

**Geofence Check:**
If user is outside the office radius, you get:

```json
{
    "success": false,
    "message": "You are outside the office geofence. Distance: 450.23m from office.",
    "distance": 450.23,
    "allowed_radius": 200
}
```

---

### 3. **GET /api/attendances/{id}** - Get specific attendance

```bash
curl -X GET http://localhost:8000/api/attendances/42
```

---

### 4. **PUT /api/attendances/{id}** - Clock Out ⭐ 🔒

```bash
curl -X PUT http://localhost:8000/api/attendances/42 \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "clock_out": "2026-04-24 17:45:00",
    "remarks": "Completed daily tasks"
  }'
```

**Request Body:**

```json
{
    "clock_out": "2026-04-24 17:45:00",
    "remarks": "Completed daily tasks"
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "id": 42,
        "user_id": 5,
        "office_id": 1,
        "work_date": "2026-04-24",
        "clock_in": "2026-04-24T08:45:30Z",
        "clock_out": "2026-04-24T17:45:00Z",
        "lat_in": 9.032,
        "lng_in": 38.7469,
        "status": "late",
        "remarks": "Completed daily tasks",
        "created_at": "2026-04-24T08:45:30Z",
        "updated_at": "2026-04-24T17:45:00Z"
    },
    "message": "Clocked out successfully"
}
```

---

### 5. **DELETE /api/attendances/{id}** - Delete attendance record

```bash
curl -X DELETE http://localhost:8000/api/attendances/42
```

---

## 🛡️ **Admin Endpoints** 🔒 (Admin role required)

### **GET /api/admin/users** - List all non-admin users

```bash
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer <admin_token>"
```

---

### **GET /api/admin/users/by-office** - Users grouped by office

```bash
curl -X GET http://localhost:8000/api/admin/users/by-office \
  -H "Authorization: Bearer <admin_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "offices": [
            {
                "id": 1,
                "name": "Addis Ababa HQ",
                "users": [
                    { "id": 1, "name": "John", "email": "john@example.com", "attendances_count": 10 }
                ]
            }
        ],
        "unassigned": []
    }
}
```

---

### **PUT /api/admin/users/{id}/office** - Assign/change user's office

```bash
curl -X PUT http://localhost:8000/api/admin/users/1/office \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{"office_id": 2}'
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Office assigned successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "office_id": 2,
        "office": { "id": 2, "name": "Bole Branch" }
    }
}
```

---

### **PUT /api/admin/users/{id}/role** - Promote/demote user

```bash
curl -X PUT http://localhost:8000/api/admin/users/1/role \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{"role": "admin"}'
```

---

### **DELETE /api/admin/users/{id}** - Delete user

```bash
curl -X DELETE http://localhost:8000/api/admin/users/1 \
  -H "Authorization: Bearer <admin_token>"
```

---

### **PUT /api/admin/attendances/{id}** - Override clock-in/out

```bash
curl -X PUT http://localhost:8000/api/admin/attendances/42 \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "clock_in": "2026-04-24 08:00:00",
    "clock_out": "2026-04-24 12:30:00",
    "status": "present"
  }'
```

---

### **GET /api/admin/overview** - Daily attendance by schedule slot

```bash
curl -X GET "http://localhost:8000/api/admin/overview?date=2026-04-24&office_id=1" \
  -H "Authorization: Bearer <admin_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "date": "2026-04-24",
        "day_of_week": "Thursday",
        "total_users": 2,
        "present": [
            {
                "user": { "id": 1, "name": "John", "email": "john@example.com" },
                "schedule_id": 1,
                "start_time": "08:00:00",
                "end_time": "12:30:00",
                "status": "present",
                "clock_in": "2026-04-24T08:05:00Z",
                "clock_out": "2026-04-24T12:30:00Z"
            }
        ],
        "absent": [
            {
                "user": { "id": 2, "name": "Jane", "email": "jane@example.com" },
                "schedule_id": 3,
                "start_time": "08:00:00",
                "end_time": "12:30:00",
                "status": "absent"
            }
        ],
        "schedule_breakdown": [],
        "office_breakdown": []
    }
}
```

---

### **GET /api/admin/calendar** - Monthly calendar view

```bash
curl -X GET "http://localhost:8000/api/admin/calendar?month=04&year=2026" \
  -H "Authorization: Bearer <admin_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "month": "April 2026",
        "days": [
            {
                "date": "2026-04-24",
                "day_of_week": "Thursday",
                "scheduled": 4,
                "present": 3,
                "absent": 1,
                "late": 1,
                "slots": [
                    {
                        "user_id": 1,
                        "user_name": "John",
                        "start_time": "08:00:00",
                        "end_time": "12:30:00",
                        "status": "present",
                        "clock_in": "2026-04-24T08:05:00Z",
                        "clock_out": "2026-04-24T12:28:00Z"
                    }
                ]
            }
        ]
    }
}
```

---

### **POST /api/admin/notify/absent** - Email one absent user

```bash
curl -X POST http://localhost:8000/api/admin/notify/absent \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "date": "2026-04-24"}'
```

> Also stores a notification record in the DB for the user's dashboard.

---

### **POST /api/admin/notify/absent-all** - Email all absent users

```bash
curl -X POST http://localhost:8000/api/admin/notify/absent-all \
  -H "Authorization: Bearer <admin_token>" \
  -H "Content-Type: application/json" \
  -d '{"date": "2026-04-24"}'
```

---

## 🔔 **Notification Endpoints** 🔒

### **GET /api/notifications** - Get user notifications

```bash
curl -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer <your_token>"
```

**Response (200 OK):**

```json
{
    "success": true,
    "unread_count": 1,
    "data": [
        {
            "id": 1,
            "type": "absent",
            "title": "Absence Recorded",
            "message": "You were marked absent on 2026-04-24.",
            "date": "2026-04-24",
            "is_read": false
        }
    ]
}
```

---

### **PUT /api/notifications/{id}/read** - Mark one as read

```bash
curl -X PUT http://localhost:8000/api/notifications/1/read \
  -H "Authorization: Bearer <your_token>"
```

---

### **PUT /api/notifications/read-all** - Mark all as read

```bash
curl -X PUT http://localhost:8000/api/notifications/read-all \
  -H "Authorization: Bearer <your_token>"
```

---

### **DELETE /api/notifications/{id}** - Delete notification

```bash
curl -X DELETE http://localhost:8000/api/notifications/1 \
  -H "Authorization: Bearer <your_token>"
```

---

## 📊 **Report Endpoints**

### **GET /api/reports/monthly** - Monthly attendance report

```bash
curl -X GET "http://localhost:8000/api/reports/monthly?month=04&year=2026&office_id=1"
```

**Query Parameters:**

- `month` (required) - Month number (01-12)
- `year` (required) - Year (e.g., 2026)
- `office_id` (optional) - Filter by office

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "month": "April 2026",
        "office_id": 1,
        "summary": {
            "total_employees": 25,
            "present_count": 480,
            "late_count": 45,
            "absent_count": 15,
            "half_day_count": 10
        },
        "attendances": [
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
            }
        ]
    }
}
```

---

## 🏥 **Health Check**

### **GET /api/health** - API status

```bash
curl -X GET http://localhost:8000/api/health
```

**Response (200 OK):**

```json
{
    "status": "API is running"
}
```

---

## 🚀 **Testing with Postman or Thunder Client**

### Import to Postman

Set a collection variable `token` — after login, save the returned token there and use `Bearer {{token}}` as the Authorization header on all 🔒 requests.

Create collection with these requests:

1. **Get Health**
    - Method: GET
    - URL: `{{base_url}}/api/health`

1. **Register**
    - Method: POST
    - URL: `{{base_url}}/api/register`
    - Body (JSON):
        ```json
        {
            "name": "John Doe",
            "email": "john@example.com",
            "password": "secret123"
        }
        ```

1. **Login**
    - Method: POST
    - URL: `{{base_url}}/api/login`
    - Body (JSON):
        ```json
        {
            "email": "john@example.com",
            "password": "secret123"
        }
        ```

1. **Create Office**
    - Method: POST
    - URL: `{{base_url}}/api/offices`
    - Body (JSON):
        ```json
        {
            "name": "Bole Branch",
            "latitude": 9.01,
            "longitude": 38.78,
            "radius_meters": 150
        }
        ```

3. **Clock In**
    - Method: POST
    - URL: `{{base_url}}/api/attendances`
    - Body (JSON):
        ```json
        {
            "latitude": 9.032,
            "longitude": 38.7469,
            "remarks": "Arrived on time"
        }
        ```

4. **Clock Out**
    - Method: PUT
    - URL: `{{base_url}}/api/attendances/1`
    - Body (JSON):
        ```json
        {
            "clock_out": "2026-04-24 17:45:00",
            "remarks": "Work completed"
        }
        ```

5. **Get Monthly Report**
    - Method: GET
    - URL: `{{base_url}}/api/reports/monthly?month=04&year=2026&office_id=1`

---

## 📱 **React Integration Example**

```javascript
const API_BASE = "http://localhost:8000/api";

const authHeaders = () => ({
    "Content-Type": "application/json",
    Authorization: `Bearer ${JSON.parse(localStorage.getItem("current_user"))?.token}`,
});

// Register
const register = async (name, email, password) => {
    const res = await fetch(`${API_BASE}/register`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password }),
    });
    const data = await res.json();
    if (data.success) localStorage.setItem("current_user", JSON.stringify(data));
    return data;
};

// Login
const login = async (email, password) => {
    const res = await fetch(`${API_BASE}/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
    });
    const data = await res.json();
    if (data.success) localStorage.setItem("current_user", JSON.stringify(data));
    return data;
};

// Logout
const logout = async () => {
    await fetch(`${API_BASE}/logout`, { method: "POST", headers: authHeaders() });
    localStorage.removeItem("current_user");
};

// Clock In
const clockIn = async (officeId, latitude, longitude) => {
    const res = await fetch(`${API_BASE}/attendances`, {
        method: "POST",
        headers: authHeaders(),
        body: JSON.stringify({ office_id: officeId, latitude, longitude, remarks: "" }),
    });
    return res.json();
};

// Clock Out
const clockOut = async (attendanceId) => {
    const res = await fetch(`${API_BASE}/attendances/${attendanceId}`, {
        method: "PUT",
        headers: authHeaders(),
        body: JSON.stringify({
            clock_out: new Date().toISOString().replace("T", " ").slice(0, 19),
            remarks: "End of workday",
        }),
    });
    return res.json();
};

// Get Attendance History
const getHistory = async (userId, from, to) => {
    const res = await fetch(
        `${API_BASE}/attendances?user_id=${userId}&from=${from}&to=${to}`,
        { headers: authHeaders() },
    );
    return res.json();
};

// Get Monthly Report
const getReport = async (month, year, officeId) => {
    const res = await fetch(
        `${API_BASE}/reports/monthly?month=${month}&year=${year}&office_id=${officeId}`,
        { headers: authHeaders() },
    );
    return res.json();
};
```

---

## ✅ **All Endpoints Summary**

> 🔒 = Bearer token required | 👑 = Admin role required

| Method | Endpoint                          | Auth | Purpose                        |
| ------ | --------------------------------- | ---- | ------------------------------ |
| GET    | `/api/health`                     |      | Check API status               |
| POST   | `/api/register`                   |      | Register, auto-assign office & schedules |
| POST   | `/api/login`                      |      | Login & get token              |
| POST   | `/api/logout`                     | 🔒   | Revoke token                   |
| GET    | `/api/me`                         | 🔒   | Get current user + office      |
| GET    | `/api/offices`                    |      | List all offices               |
| GET    | `/api/offices/{id}`               |      | Get office                     |
| POST   | `/api/offices`                    | 🔒   | Create office                  |
| PUT    | `/api/offices/{id}`               | 🔒   | Update office location/radius  |
| DELETE | `/api/offices/{id}`               | 🔒   | Delete office                  |
| GET    | `/api/schedules`                  | 🔒   | List schedules                 |
| POST   | `/api/schedules`                  | 🔒   | Create schedule                |
| GET    | `/api/schedules/{id}`             | 🔒   | Get schedule                   |
| PUT    | `/api/schedules/{id}`             | 🔒   | Update schedule                |
| DELETE | `/api/schedules/{id}`             | 🔒   | Delete schedule                |
| GET    | `/api/attendances`                | 🔒   | List attendances               |
| POST   | `/api/attendances`                | 🔒   | Clock in (office auto-detected)|
| GET    | `/api/attendances/{id}`           | 🔒   | Get attendance                 |
| PUT    | `/api/attendances/{id}`           | 🔒   | Clock out                      |
| DELETE | `/api/attendances/{id}`           | 🔒   | Delete attendance              |
| GET    | `/api/notifications`              | 🔒   | Get user notifications         |
| PUT    | `/api/notifications/{id}/read`    | 🔒   | Mark notification read         |
| PUT    | `/api/notifications/read-all`     | 🔒   | Mark all notifications read    |
| DELETE | `/api/notifications/{id}`         | 🔒   | Delete notification            |
| GET    | `/api/reports/monthly`            |      | Monthly report                 |
| GET    | `/api/admin/users`                | 👑   | List all users (non-admin)     |
| GET    | `/api/admin/users/by-office`      | 👑   | Users grouped by office        |
| GET    | `/api/admin/users/{id}`           | 👑   | Get user + attendance history  |
| PUT    | `/api/admin/users/{id}/office`    | 👑   | Assign/change user's office    |
| PUT    | `/api/admin/users/{id}/role`      | 👑   | Promote/demote user            |
| DELETE | `/api/admin/users/{id}`           | 👑   | Delete user                    |
| PUT    | `/api/admin/attendances/{id}`     | 👑   | Override clock-in/out/status   |
| GET    | `/api/admin/overview`             | 👑   | Daily attendance by schedule   |
| GET    | `/api/admin/calendar`             | 👑   | Monthly calendar view          |
| POST   | `/api/admin/notify/absent`        | 👑   | Email + notify one absent user |
| POST   | `/api/admin/notify/absent-all`    | 👑   | Email + notify all absent users|

---

## ⏰ **Ethiopian Work Schedules**

All users are automatically assigned two daily shifts (Monday–Friday):

| Shift | Ethiopian Time | Standard Time |
|-------|---------------|---------------|
| Morning | 2:00 – 6:30 | `08:00 – 12:30` |
| Afternoon | 7:30 – 11:30 | `13:30 – 17:30` |

A user can clock in **once per shift per day**. The system auto-detects which shift based on the current time.

---

**Flow:** Register → office & schedules auto-assigned → Login → save `token` → include `Authorization: Bearer <token>` on all 🔒 requests → Clock in without specifying office.
