Money Coach App – Scope Reset & New Implementation Plan

From Client message : this below message from client: Alex

It is very simple. The app should ask people how much they make and how much they want to spend
and by what date. On the basis of that, they should have a daily spend limit. Every day they input
what they spent and the app tracks their progress toward their spending goal. The Al should be able
to give them a tip every day on how to save more money and be more financially reasonable. None of
that is in here.

I said to delete "non essential spending" and to just ask people what they spent on each day. I also
said to delete the free trial portion. I also said to fix certain typos, and to have it show a US country
code, rather than UK. None of those steps were followed either.

What are your suggestions? I feel that when we get on calls, they are not productive because my
nstructions are not followed after the call ...

After This message I Give him below this purpose. 
1. Purpose of This Document
This document explains:
●	What the old app was designed to do
●	Why it did not fully match client expectations By Latest feedback and meeting
●	What the new app will do
●	How the logic, UI, backend, and AI will work
●	What data will be collected and how it will be used
●	Planned timeline & next steps
This document is intended to align both sides before development continues.
________________________________________
2. Old App – Description & Original Logic
2.1 Old App Concept
The original app was designed as a money-saving assistant.
2.2 Old User Flow
1.	User logs in
2.	User answers multiple financial questions:
○	Monthly income
○	Fixed expenses
○	Existing savings
○	Debt amount
○	Target savings amount
○	Target date
3.	App calculates how much the user should save
4.	User inputs how much they saved each day
5.	App shows savings progress and remaining amount
6.	AI provides general saving suggestions
2.3 Old App Focus
●	Savings planning
●	Long-term financial calculation
●	Multiple financial categories
2.4 Problems With Old App
●	Logic was too complex for intended use
●	Focused on saving, not daily spending control
●	Included features client requested to remove:
○	Non-essential spending
○	Free trial
○	UK country code
●	Did not match the simplified experience requested
________________________________________
3. Reason for Change (Scope Reset)
After reviewing client feedback, it is clear that:
●	The product should be a daily on spending
●	The app should be simple
●	All calculations should be transparent and easy to understand
This requires a logic reset, not small fixes.
________________________________________
4. New App – Final Agreed Concept
4.1 Core Idea
A daily spending limit app that:
●	Calculates how much a user can spend per day
●	Tracks daily spending
●	Shows progress toward a spending goal
●	Provides one short AI tip per day
________________________________________
5. New App – User Flow
5.1 Onboarding (Only 3 Questions)
1.	How much money do you make? (Monthly or weekly – single option)
2.	How much do you want to spend total? (Budget)
3.	By what date do you want to reach this goal?
No savings, no debt, no expense categories.
________________________________________
5.2 Core Calculation Logic
Total Budget = User-defined spending amount
Total Days = End Date – Start Date
Daily Spend Limit = Total Budget / Total Days
This daily limit updates automatically based on spending behavior.
________________________________________
5.3 Daily User Action
Each day, the user only does one thing:
●	Input how much they spent today
The app automatically:
●	Updates remaining budget
●	Updates remaining days
●	Recalculates daily limit
●	Updates charts and progress
________________________________________
6. Data Model (Simplified)
6.1 User Data
●	income
●	total_budget
●	start_date
●	end_date
6.2 Daily Spending Data
●	user_id
●	date
●	spent_amount
No categorization or tagging required.
________________________________________
7. AI Suggestion System
7.1 Data Sent to AI
●	Daily spend limit
●	Amount spent today
●	Remaining budget
●	Remaining days
7.2 AI Prompt Behavior
The AI will:
●	Give 1 short tip per day
●	Use friendly, motivational language
●	Avoid financial jargon
7.3 Example AI Tips
●	"You went over your daily limit today. Consider a smaller purchase tomorrow."
●	"Nice job staying under budget! Small habits add up."
●	"Try skipping one optional purchase tomorrow to stay on track."
________________________________________
8. UI / Figma Changes Required
8.1 Screens to Update or Redesign
●	Onboarding (reduced to 3 questions)
●	Daily spend input screen (single input field)
●	Progress dashboard
●	Progress view (daily or weekly)
8.2 Elements to Remove
●	Non-essential spending section
●	Free trial screens
●	Complex financial summaries
8.3 Localization Fixes
●	Country code default: United States (+1)
●	Currency: USD ($)
________________________________________
9. Backend Changes Required
●	Remove savings-based logic
●	Remove trial-related tables and middleware
●	Update calculations to spending-based logic
●	Update AI prompt handling
●	Simplify database relations
These changes require refactoring, not patching.
________________________________________
10. API & Integration Status
●	AI API keys: Already received and configured
●	No additional third-party APIs required at this stage
●	Also working on setting up your server. We got this access.
________________________________________
11. Timeline & Next Steps
Step 1 – Requirement Freeze (1 day)
●	Client confirms this document
●	Final approval on logic & scope
Step 2 – Design Update (2–3 days)
●	Update Figma screens
●	Confirm UI flow
Step 3 – Backend Refactor (3–4 days)
●	Logic rebuild
●	Database updates
●	AI integration
Step 4 – Frontend Implementation (3–4 days)
●	UI updates
●	Chart integration
●	Daily input flow
Step 5 – Testing & Review (1–2 days)
●	Bug fixes
●	Client review
________________________________________
12. Working Process Going Forward
To avoid delays and misalignment:
●	All feature changes will be documented
●	Written confirmation required before implementation
●	No additional features added without approval
________________________________________
13. Conclusion
This rebuild aligns the app fully with the client’s expectations:
●	Simple
●	Fast
●	Focused on daily spending behavior
Once approved, development will proceed exactly as documented.
