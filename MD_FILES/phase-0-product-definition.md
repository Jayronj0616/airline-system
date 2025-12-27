# Phase 0 - Product Definition

**Goal:** Define system scope and prove you can think before coding.

---

## Tasks

### 1. System Scope Definition
- [x] Define MVP boundaries (what's IN and what's OUT)
- [x] Document system constraints
- [x] Identify core vs nice-to-have features

### 2. User Role Identification
- [x] Define Passenger role and capabilities
- [x] Define Revenue Admin role and capabilities
- [x] Define System (automated jobs) responsibilities

### 3. Database Setup
- [x] Update `.env` with proper database name (`airline_system`)
- [x] Create database in MySQL
- [x] Test database connection

### 4. Authentication Setup
- [x] Install Laravel Breeze (or decide on auth approach)
- [x] Set up user roles (Passenger, Admin)
- [x] Create user migration with role field

### 5. Frontend Tooling
- [x] Install SweetAlert2 via npm
- [x] Configure Vite for SweetAlert
- [x] Create base Blade layout with SweetAlert integration

### 6. Project Documentation
- [x] Create `SYSTEM_OVERVIEW.md` - One-page system description
- [x] Create `FEATURES.md` - Must-have vs nice-to-have feature list
- [x] Create `CONSTRAINTS.md` - Technical and business constraints
- [x] Create `USER_STORIES.md` - User stories for each role

---

## Deliverables
- [x] Database configured and connected
- [x] Auth system installed
- [x] User roles added to auth
- [x] SweetAlert integrated
- [x] 4 documentation files created
- [x] Clear understanding of what we're building

---

## Notes
- **Stack:** Laravel 10 + Blade + MySQL + SweetAlert2
- **No real payments** - Mock payment flow only
- **Single airline** - Multi-tenant comes later (if ever)
- **Simulated demand** - Background jobs will fake user activity

---

## Next Phase
Once all tasks are complete, move to `phase-1-domain-modeling.md`
