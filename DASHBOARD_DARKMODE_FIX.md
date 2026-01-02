# Dashboard Dark Mode Fix - Quick Reference

## Apply these replacements in: resources/views/admin/dashboard/index.blade.php

### 1. Metric Cards (Lines ~13-115)
Replace:
```
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
```
With:
```
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
```

Replace all instances of:
- `text-gray-600` → `text-gray-600 dark:text-gray-400`
- `text-gray-900` → `text-gray-900 dark:text-gray-100`
- `text-gray-500` → `text-gray-500 dark:text-gray-400`

### 2. Charts Section (Lines ~116-160)
Same replacements as above for each chart container

### 3. Demand Trends Section (Lines ~161-230)
Replace:
- `bg-green-50` → `bg-green-50 dark:bg-green-900/20`
- `bg-red-50` → `bg-red-50 dark:bg-red-900/20`
- `text-gray-900` → `text-gray-900 dark:text-gray-100`
- `text-gray-600` → `text-gray-600 dark:text-gray-400`

### 4. Filters Section (Lines ~231-290)
Replace:
- `text-gray-700` → `text-gray-700 dark:text-gray-300`
- `border-gray-300` → `border-gray-300 dark:border-gray-700`
- `bg-white` → `bg-white dark:bg-gray-900`
- Input fields: add `dark:bg-gray-900 dark:text-gray-100`

### 5. Performance Table (Lines ~340-420)
Replace:
- `bg-gray-50` → `bg-gray-50 dark:bg-gray-900`
- `bg-white` → `bg-white dark:bg-gray-800`
- `divide-gray-200` → `divide-gray-200 dark:divide-gray-700`
- `hover:bg-gray-50` → `hover:bg-gray-50 dark:hover:bg-gray-700`

### 6. Add Pagination Dark Mode
After the table, find pagination links and add:
```
<div class="mt-4">
    {{ $flightPerformance->appends(request()->query())->links() }}
</div>
```

Make sure pagination component has dark mode (in app layout)

## BookingManagementController - Already Fixed ✅
The controller properly:
- Loads relationships with eager loading
- Paginates results (20 per page)
- Has search filters
- Returns data to view

Just verify the view exists and renders the data properly.
