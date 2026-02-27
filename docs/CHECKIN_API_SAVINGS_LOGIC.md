# Check-in API: Savings Logic & Response

## Business rules (non-negotiable)

- Savings are calculated **only when a user checks in**. No check-in row → no calculation.
- **Days passed** = number of check-in records (not calendar days).
- **Overspending** never creates negative savings: `daily_save = max(raw_daily_save, 0)`.
- **Backward compatibility**: legacy `amount_spending` and `amount_saved` still accepted and stored.

## Goal setup (derived once)

- `total_money` (M) = goal’s `monthly_income` for the period  
- `goal_save` (G) = goal’s `target_amount`  
- `total_days` (D) = from `start_date` to `end_date`  
- `total_spend_allowed` = M - G  
- `per_day_allowed_spend` = total_spend_allowed / D (stored as goal’s `daily_target`)

## Check-in input

- **Primary (new):** `daily_spent_amount`  
- **Legacy:** `amount_spending` (treated as daily spent), `amount_saved` (used as-is, no negative)

**Calculation when check-in exists:**

- `raw_daily_save` = per_day_allowed_spend - daily_spent_amount  
- `daily_save` = max(raw_daily_save, 0) → stored in `calculated_daily_save`

## API response (GET check-ins index)

Every response includes `today`, `progress`, and `guidance` (read-only; no auto-generated days).

### Example API JSON response (GET /api/check-ins)

```json
{
  "status": true,
  "message": "Check-ins fetched successfully",
  "code": 200,
  "data": {
    "today": {
      "spent": 45.50,
      "saved": 54.50
    },
    "progress": {
      "total_saved": 1200.00,
      "goal_save": 5000.00,
      "goal_completion_percent": 24.00,
      "days_passed": 6,
      "total_days": 15
    },
    "guidance": {
      "remaining_days": 9,
      "remaining_save": 3800.00,
      "next_day_required_save": 422.22,
      "next_day_allowed_spend": 244.45
    },
    "check_ins": [
      {
        "id": 1,
        "goal_id": 1,
        "amount_spending": 45.50,
        "amount_saved": 54.50,
        "daily_spent_amount": 45.50,
        "calculated_daily_save": 54.50,
        "target_amount": 100.00,
        "check_date": "2026-02-20",
        "status": "completed"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 25,
      "total": 6
    },
    "total_amount_spending": 273,
    "total_amount_saved": 1200,
    "daily_target": 100
  }
}
```

- **today**: Filled only if there is a check-in for today; otherwise `spent` and `saved` are `null`.
- **progress**: From existing check-ins only (`days_passed` = count of check-in records).
- **guidance**: `next_day_required_save` and `next_day_allowed_spend` are `null` when `remaining_days` is 0.

## Example: Create check-in (POST /api/check-ins)

**New flow (primary):**

```json
{
  "daily_spent_amount": 60.00
}
```

**Legacy (still supported):**

```json
{
  "amount_spending": 60.00
}
```

or

```json
{
  "amount_saved": 40.00
}
```

At least one of `daily_spent_amount`, `amount_spending`, or `amount_saved` is required.

## Database (additive only)

- **Existing columns kept:** `amount_spending`, `amount_saved`, `target_amount`, `check_date`, etc.
- **Added:** `daily_spent_amount`, `calculated_daily_save` (nullable).
- One row per check-in; no row = no calculation for that day.
