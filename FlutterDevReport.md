# Flutter Frontend Development Plan (Action Plan for AI Agent)

## 📌 Project Overview
The Laravel backend for the "Management App" has been fully upgraded to support a separate mobile application via a RESTful API powered by Laravel Sanctum. This document serves as a blueprint for the Flutter Developer (or AI Agent) to kickstart the mobile app development, ensuring seamless integration with the existing backend.

---

## 🏗️ Architecture & Tech Stack Recommendations
*   **Framework:** Flutter (Latest Version)
*   **State Management:** Provider, Riverpod, or BLoC (Provider is recommended for simplicity matching the API).
*   **Networking:** `dio` package for HTTP requests.
*   **Local Storage:** `shared_preferences` or `flutter_secure_storage` for securely storing the Sanctum authentication token.
*   **Routing:** `go_router` for clean and declaritive navigation.

---

## 🔗 Global API Configuration
*   **Base URL:** `http://[YOUR_SERVER_IP_OR_DOMAIN]/api/v1`
*   **Headers:**
    *   `Accept: application/json`
    *   `Content-Type: application/json`
    *   *After Login:* `Authorization: Bearer <YOUR_SANCTUM_TOKEN>`

### 🛠️ Interceptor Logic (Dio)
You must implement a Dio Interceptor that automatically attaches the `Bearer Token` to every protected request. If a Request returns a `401 Unauthorized`, the app should clear the local session and redirect the user back to the Login Screen.

---

## 📚 API Endpoints Documentation

### 1. Authentication (Public)
| Method | Endpoint | Description | Payload Body | Response Details |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/login` | Authenticate User | `username`, `password` | Returns `data.token`, user info, and an array of `roles`. **Must save token locally.** |
| `POST` | `/logout` | Destroy Token | *Requires Bearer Token* | Invalidates the token on the server. |
| `GET` | `/user` | Get Current User | *Requires Bearer Token* | Validates token and returns basic user profile. |

*Note: You must route the user to either the "Engineer Dashboard" or the "Worker Dashboard" depending on the `roles` returned during login.*

---

### 2. Engineer Endpoints (Protected - Engineer Role)
**Prefix:** `/engineer/`

| Method | Endpoint | Description | Payload Body |
| :--- | :--- | :--- | :--- |
| `GET` | `cv` | Get the engineer's CV | - |
| `PUT` | `cv` | Update CV details | `profile_details`, `experience`, `education` |
| `POST` | `skills` | Add skills to the CV | `skills` (Array of Strings) |
| `GET` | `projects` | Get projects assigned to the engineer | - |
| `GET` | `tasks` | Get assigned tasks (Optionally pass `?project_id=1`) | - |
| `PUT` | `tasks/{id}`| Update specific task progress/status | `progress` (0-100), `status` ('قيد التنفيذ', 'مكتملة'...) |
| `GET` | `reports` | Get all reports written by the engineer | - |
| `POST` | `reports` | Submit a new technical report | `project_id`, `workshop_id`, `service_id`, `report_type`, `report_details` |
| `PUT` | `reports/{id}`| Edit an existing report | `report_type`, `report_details` |
| `DELETE`| `reports/{id}`| Delete a report | - |

---

### 3. Worker Endpoints (Protected - Worker Role)
**Prefix:** `/worker/`

| Method | Endpoint | Description | Payload Body |
| :--- | :--- | :--- | :--- |
| `GET` | `cv` | Get the worker's CV | - |
| `PUT` | `cv` | Update CV details | `profile_details`, `experience`, `education` |
| `POST` | `skills` | Add skills to the CV | `skills` (Array of Strings) |
| `GET` | `workshops`| Get workshops assigned to this worker | - |
| `GET` | `tasks` | Get assigned tasks | - |
| `PUT` | `tasks/{id}`| Update specific task progress/status | `progress` (0-100), `status` ('قيد التنفيذ', 'مكتملة'...) |

*(Note: Workers do not have access to the Reports system).*

---

## 🧱 Expected Data Models (JSON Responses Structure)
When fetching data, the API returns it wrapped in a standard `Resource` format:
```json
{
  "success": true,
  "message": "Success message here",
  "data": { ... Object or Array ... },
  "status_code": 200
}
```

**Task Model Example (Data):**
```json
{
   "id": 1,
   "description": "Fix electrical wiring in block A",
   "progress": 50,
   "status": "قيد التنفيذ",
   "start_date": "2026-04-09",
   "project": {"id": 1, "name": "Al-Sahel Mall"},
   "workshop": {"id": 3, "name": "Electrical Workshop"}
}
```

---

## 🚀 Execution Checklist for AI Agent
To complete the mobile app, follow these structured steps:

- [ ] **Step 1: Scaffolding:** Setup the Flutter project, define the folder structure (e.g., MVVM architecture), and add dependencies (`dio`, `provider`, `shared_preferences`).
- [ ] **Step 2: Core Network Layer:** Build the API Service class, the `Dio` client, interceptors, and basic custom Exception models for error handling.
- [ ] **Step 3: Auth Flow:** Implement `LoginScreen`, `AuthRepository`, state logic, and Token persistence. Provide a routing guard to check if the user is authenticated on app startup.
- [ ] **Step 4: Role-Based Routing:** Upon successful login, direct "Engineers" to an `EngineerHomeNavigator` and "Workers" to a `WorkerHomeNavigator`.
- [ ] **Step 5: Dashboard Implementation (Engineer):** 
    - Build Tasks List & Details Screen (Allowing progress updates).
    - Build Projects Overview.
    - Build Reports CRUD screens.
    - Build CV Profile view and edit form (including Skills array input).
- [ ] **Step 6: Dashboard Implementation (Worker):**
    - Build Tasks List & Details Screen.
    - Build Workshops list.
    - Build CV Profile view and edit form.
- [ ] **Step 7: Theming & UX:** Apply a professional UI theme matching the "Management App" style. Include loading indicators (`CircularProgressIndicator`), error snackbars, and empty state screens.

---
**Agent Note:** You can refer back to this document to strictly follow the URL paths, JSON keys, and payloads expected by the Laravel Backend. Good luck!
