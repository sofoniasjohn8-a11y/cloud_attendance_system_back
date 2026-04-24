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
        "email": "john@example.com"
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

```bash
curl -X POST http://localhost:8000/api/attendances \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "office_id": 1,
    "latitude": 9.0320,
    "longitude": 38.7469,
    "remarks": "On time"
  }'
```

**Request Body:**

```json
{
    "office_id": 1,
    "latitude": 9.032,
    "longitude": 38.7469,
    "remarks": "On time"
}
```

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
            "office_id": 1,
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

> 🔒 = Requires `Authorization: Bearer <token>` header

| Method | Endpoint                | Auth | Purpose                  |
| ------ | ----------------------- | ---- | ------------------------ |
| GET    | `/api/health`           |      | Check API status         |
| POST   | `/api/register`         |      | Register & get token     |
| POST   | `/api/login`            |      | Login & get token        |
| POST   | `/api/logout`           | 🔒   | Revoke token             |
| GET    | `/api/me`               | 🔒   | Get current user         |
| GET    | `/api/offices`          |      | List all offices         |
| GET    | `/api/offices/{id}`     |      | Get office               |
| POST   | `/api/offices`          | 🔒   | Create office            |
| PUT    | `/api/offices/{id}`     | 🔒   | Update office            |
| DELETE | `/api/offices/{id}`     | 🔒   | Delete office            |
| GET    | `/api/schedules`        | 🔒   | List schedules           |
| POST   | `/api/schedules`        | 🔒   | Create schedule          |
| GET    | `/api/schedules/{id}`   | 🔒   | Get schedule             |
| PUT    | `/api/schedules/{id}`   | 🔒   | Update schedule          |
| DELETE | `/api/schedules/{id}`   | 🔒   | Delete schedule          |
| GET    | `/api/attendances`      | 🔒   | List attendances         |
| POST   | `/api/attendances`      | 🔒   | Clock in                 |
| GET    | `/api/attendances/{id}` | 🔒   | Get attendance           |
| PUT    | `/api/attendances/{id}` | 🔒   | Clock out                |
| DELETE | `/api/attendances/{id}` | 🔒   | Delete attendance        |
| GET    | `/api/reports/monthly`  |      | Monthly report           |

---

**Flow:** Register or Login → save `token` from response → include `Authorization: Bearer <token>` on all 🔒 requests.
