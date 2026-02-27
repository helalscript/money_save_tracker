# Money Coach App – Full Application Documentation

**Detailed document:** A complete description of where and how the app works.

---

## 1. App Overview

This is a **Laravel API**–based **Money Coach / Savings Tracker** app. The user sets a savings goal and enters only daily spending; the app automatically calculates daily budget, amount saved, progress percentage, and status messages. The home screen shows the active goal; the progress page shows the journey and AI tips/insights.

---

## 2. Project Structure (Where Everything Lives)

```
alexnahai/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/V1/
│   │   │   │   ├── Auth/          # Login, register, password reset
│   │   │   │   ├── User/          # Goals, check-ins, tips, progress, notifications, payments
│   │   │   │   ├── Admin/         # Admin (commented out)
│   │   │   │   └── Public/        # Packages, stepper pages, system info
│   │   │   └── ...
│   │   ├── Middleware/
│   │   │   └── IsUser.php         # Restricts API access to non-admin user role
│   │   └── Resources/
│   │       └── API/V1/
│   │           └── GoalResource.php   # Active goal API output format
│   ├── Models/                    # Goal, CheckIn, User, Tip, UserTip, Payment, ...
│   ├── Services/
│   │   ├── API/V1/User/           # Goal, check-in, progress, tips business logic
│   │   └── AI/
│   │       └── OpenAIService.php   # OpenAI calls for tips and insights
│   └── Helpers/
│       └── Helper.php             # JSON responses, file upload, etc.
├── config/
│   └── services.php               # OPEN_AI_API_KEY, OPEN_AI_API_URL
├── database/migrations/           # goals, check_ins, users, tips, ...
├── routes/
│   ├── api.php                    # All API routes (auth + user + public)
│   └── auth.php                   # Web login (minimal use)
└── docs/
    └── APP_DOCUMENTATION.md       # This document
```

---

## 3. Authentication Flow (How Login/Register Work)

### 3.1 Routes and Middleware

- **File:** `routes/api.php`
- **Guest routes (no login):**
  - `POST /api/register` → Register
  - `POST /api/verify-email` → Email verify (OTP)
  - `POST /api/resend-otp` → Resend OTP
  - `POST /api/login` → Login
  - `POST /api/forget-password`, `POST /api/verify-otp`, `POST /api/reset-password` → Password reset

- **Authenticated routes (Bearer token required):**
  - `GET /api/refresh-token`, `POST /api/logout`, `GET /api/me`, `POST /api/update-profile`, `POST /api/update-password`

- **User-only routes:** `auth:api` + `is_user` middleware
  - `IsUser` (`app/Http/Middleware/IsUser.php`): Verifies login; if `user->role === 'admin'` returns 403. Only non-admin users can hit these routes.

### 3.2 Login Process

1. **Controller:** `App\Http\Controllers\API\V1\Auth\LoginController`
   - Finds user by email, verifies password, checks if email is verified.
2. On success, generates token via `auth('api')->login($user)` and returns JSON.

---

## 4. User Features – Routes and Flow (Where Each Call Goes)

All user features live inside `Route::group(['middleware' => ['auth:api', 'is_user']])` in `routes/api.php`.

| Feature | HTTP | Route | Controller | Service / Model |
|---------|------|-------|-------------|-----------------|
| Goals list | GET | `/api/goals` | GoalController@index | GoalService::index → Goal (active, user_id) |
| Create goal | POST | `/api/goals` | GoalController@store | GoalService::store → Goal::create |
| Single goal | GET | `/api/goals/{id}` | GoalController@show | GoalService::show |
| Update/delete goal | PUT/DELETE | `/api/goals/{id}` | GoalController@update, destroy | GoalService |
| **Active goal (home)** | GET | `/api/active-goal` | GoalController@getActiveGoal | GoalService::getActiveGoal → GoalResource |
| Weekly streak data | GET | `/api/weekly-streak` | GoalController@getWeeklyStreakData | GoalService::getWeeklyStreakData |
| Check-ins list | GET | `/api/check-ins` | CheckInController@index | CheckInService::index |
| **Save check-in (daily spending)** | POST | `/api/check-ins` | CheckInController@store | CheckInService::store → CheckIn::create |
| Check-in activity (daily/weekly/monthly) | GET | `/api/check-ins-activity` | CheckInController@checkInsActivity | CheckInService::checkInsActivity |
| Progress page data | GET | `/api/progress-page` | ProgressPageController@index | ProgressPageService::index |
| AI insights | GET | `/api/progress-page/ai-insights` | ProgressPageController@aiInsights | ProgressPageService::aiInsights → OpenAIService |
| Today's tip | GET | `/api/tips/today` | TipsController@getTodayTips | TipsService::getDailyTips |
| Refresh tip (new tip) | POST | `/api/tips/refresh` | TipsController@refreshTips | TipsService::refreshTips → OpenAIService |
| Mark tip done | POST | `/api/tips/{id}/mark-done` | TipsController@markAsDone | TipsService::markAsDone |
| Notifications | GET/index, show + mark-all, delete-all | `/api/notifications...` | NotificationController | NotificationService |
| Payments | API resource | `/api/payments` | PaymentController | PaymentService |

---

## 5. Data Models and Database (Where Data Is Stored)

### 5.1 Goals table (`goals`)

- **Migration:** `database/migrations/2025_10_22_112316_create_goals_table.php`
- **Model:** `App\Models\Goal`
- Important fields:
  - `user_id`, `monthly_income`, `target_amount` (savings target), `start_date`, `end_date`
  - `daily_target` = **(monthly_income - target_amount) / total_days** (daily max spend / daily budget)
  - `current_streak`, `deadline_indicator` (green/yellow/red), `status` (active/inactive)

### 5.2 Check-ins table (`check_ins`)

- **Migrations:** `..._create_check_ins_table.php` + `..._add_spending_fields_to_check_ins_table.php`
- **Model:** `App\Models\CheckIn`
- Important fields:
  - `goal_id`, `check_date`
  - `amount_spending` = the only value the user enters (how much spent today)
  - `amount_saved` = computed by backend: **daily_budget - amount_spending** (on check-in store/update)
  - `target_amount` = snapshot of the goal’s `daily_target` for that day

Data is persisted in the **database** (Laravel Eloquent); the app does not use localStorage/state on the backend.

---

## 6. Savings Calculation Logic (How the Math Works)

This is the core business logic of the app; most of it lives in **GoalService** and **CheckInService**.

### 6.1 When a goal is created (GoalService::store)

- `start_date` = today, `end_date` = user-provided date.
- `total_days` = number of days from start to end.
- **daily_target** = (monthly_income - target_amount) / total_days  
  → This is “daily budget” (max spend per day).

### 6.2 When a check-in is saved (CheckInService::store)

1. Load the active goal.
2. **daily_budget** = goal's `daily_target`.
3. **amount_saved** = daily_budget - amount_spending (can be negative if overspent).
4. Create check-in with: `goal_id`, `amount_spending`, `target_amount` = goal's daily_target, computed `amount_saved`, `check_date`, status.

On check-in update, if `amount_spending` changes, `amount_saved` is recalculated with the same formula.

### 6.3 When the active goal is viewed (GoalService::calculateNewEndDate)

This builds **others_data** for the **active goal** (progress, status, remaining). Flow:

1. **total_saved** = sum of all check-ins' `amount_saved` (or `target_amount - amount_spending` when null).
2. **remaining_saving** = target_amount - total_saved.
3. **progress %** = (total_saved / target_amount) * 100, clamped between 0 and 100.
4. **current_day** = days from start to today (1 to total_days).
5. **expected_saving_by_today** = (target_amount / total_days) × current_day.
6. **status_message:**
   - If total_saved >= expected_saving_by_today → *"At your pace, you'll reach your goal early"*
   - Else → *"You need to save more per day to reach your goal"*
7. **deadline_indicator:** Based on total_saved / expected_saving_by_today: green (≥1), yellow (≥0.8), red (<0.8).

These values are attached to the goal as `others_data` in `getActiveGoal()` and sent via **GoalResource** (saved_amount, remaining_amount, persantage_progress, ai_suggestion/status_message, deadline_indicator, etc.).

### 6.4 Progress page (ProgressPageService::index)

- Uses `others_data` from the active goal (saved_amount, spending_amount, persantage_progress, status_message).
- **total_saved** = others_data's saved_amount.
- **meeting_daily_target** = "On-Track" if status_message contains "early", else "Need to save more".
- best_day_saving, daily_average_saving, streak come from GoalService.

### 6.5 Best day / daily average (GoalService)

- **bestDaySaving():** For each check-in, use amount_saved (or target_amount - amount_spending), then take the max (not below 0).
- **dailyAverageSaving():** Sum of all check-ins' amount_saved / check-in count.

---

## 7. AI Tips and Insights (Where and How)

### 7.1 Tips (TipsService + OpenAIService)

- **Routes:** `GET /api/tips/today`, `POST /api/tips/refresh`, `POST /api/tips/{id}/mark-done`
- **Flow:**
  1. `getDailyTips()`: Checks if there is a UserTip for today; if not, calls `refreshTips()`.
  2. `refreshTips()`: Max 3 tips per day; gets daily_target, remaining_amount, needed_days from active goal; today’s spending (amount_spending from CheckIn) and categories.
  3. This context is passed to **OpenAIService::generateTip()** → builds prompt and calls **OpenAI Chat API** (config: `config/services.php` → OPEN_AI_API_KEY, OPEN_AI_API_URL).
  4. Tip text is stored in `tips` table, then linked to the user in `user_tips` and returned.

### 7.2 Progress page AI insights

- **Route:** `GET /api/progress-page/ai-insights`
- **Flow:** ProgressPageService::aiInsights() gathers the active goal and the last 14 days of check-in data; passes goal target, total spending, remaining, daily limit, streak, recent_days, etc. to **OpenAIService::generateInsights()**.
- OpenAIService builds the prompt and calls the same OpenAI API; splits the response by lines and returns an array of bullet-style insights.

---

## 8. API Response Format (How Data Is Returned)

- **Helper:** Standard JSON via `App\Helpers\Helper::jsonResponse()` / `Helper::jsonErrorResponse()`.
- **Active goal:** Uses **GoalResource** → id, title (Save $X by Date), saved_amount, target_amount, remaining_amount, deadline_indicator, new_end_date, ai_suggestion (or status_message), persantage_progress, etc.

---

## 9. Config and Environment

- **API prefix:** Usually `/api` (Laravel default).
- **OpenAI:** `OPEN_AI_API_KEY`, `OPEN_AI_API_URL` (optional) in `.env`; read under `openai` key in `config/services.php`.
- **Authentication:** APIs use `auth:api` (Sanctum/Passport-style guard) and `is_user` middleware for user role check.

---

## 10. Flow Diagram (Where Each Call Goes)

```
[Mobile/Web client]
        |
        |  POST /api/check-ins { amount_spending: 500 }
        v
  CheckInController::store
        |
        v
  CheckInService::store
        |--> Load Goal (active)
        |--> amount_saved = daily_target - amount_spending
        |--> CheckIn::create(...)
        v
  [Check-in saved to database]

[Home screen – Active goal]
        |
        |  GET /api/active-goal
        v
  GoalController::getActiveGoal
        |
        v
  GoalService::getActiveGoal
        |--> nowActiveGoal() → Goal + checkIns
        |--> calculateStreak(goal)
        |--> others_data = calculateNewEndDate(goal)  <-- total_saved, status_message, progress%, ...
        v
  GoalResource::toArray(goal)
        v
  JSON: saved_amount, target_amount, remaining_amount, status_message, persantage_progress, ...
```

---

## 11. File Responsibilities (What Each File Does)

| File | Responsibility |
|------|----------------|
| `routes/api.php` | All API routes; guest vs auth vs is_user groups |
| `app/Http/Middleware/IsUser.php` | Restricts API access to non-admin user role |
| `app/Http/Controllers/API/V1/User/GoalController.php` | Goal CRUD, getActiveGoal, getWeeklyStreakData |
| `app/Http/Controllers/API/V1/User/CheckInController.php` | Check-in create, index, checkInsActivity |
| `app/Http/Controllers/API/V1/User/ProgressPageController.php` | progress-page, ai-insights |
| `app/Http/Controllers/API/V1/User/TipsController.php` | tips/today, refresh, mark-done |
| `app/Http/Resources/API/V1/GoalResource.php` | Active goal API output shape |
| `app/Services/API/V1/User/GoalService.php` | Goal create, active goal, streak, calculateNewEndDate (savings logic), bestDaySaving, dailyAverageSaving |
| `app/Services/API/V1/User/CheckInService.php` | Check-in store (amount_saved calculation), index, update, checkInsActivity grouping |
| `app/Services/API/V1/User/ProgressPageService.php` | Journey data (total_saved, progress%, meeting_daily_target), aiInsights context and call |
| `app/Services/API/V1/User/TipsService.php` | Daily tip, refresh (context build + OpenAIService), markAsDone |
| `app/Services/AI/OpenAIService.php` | generateTip, generateInsights; OpenAI API calls |
| `app/Models/Goal.php` | goals table, checkIns relation |
| `app/Models/CheckIn.php` | check_ins table, goal relation |

This document describes **where** (which file/route/service) and **how** (which logic/data flow) each part of the app works.
