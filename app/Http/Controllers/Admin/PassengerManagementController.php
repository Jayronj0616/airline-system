<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\PassengerEditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PassengerManagementController extends Controller
{
    // Allowed fields for typo corrections only
    private const EDITABLE_FIELDS = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'phone' => 'Phone',
    ];

    // Identity fields that CANNOT be edited
    private const PROTECTED_FIELDS = [
        'date_of_birth',
        'gender',
        'nationality',
        'passport_number',
    ];

    public function index(Request $request)
    {
        $query = Passenger::with(['booking.flight', 'seat']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($q) use ($search) {
                      $q->where('booking_reference', 'like', "%{$search}%");
                  });
            });
        }

        $passengers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.passengers.index', compact('passengers'));
    }

    public function show(Passenger $passenger)
    {
        $passenger->load([
            'booking.flight.aircraft',
            'booking.fareClass',
            'seat',
            'editLogs.user'
        ]);

        return view('admin.passengers.show', compact('passenger'));
    }

    public function edit(Passenger $passenger)
    {
        $passenger->load('booking.flight');
        
        return view('admin.passengers.edit', [
            'passenger' => $passenger,
            'editableFields' => self::EDITABLE_FIELDS,
        ]);
    }

    public function update(Request $request, Passenger $passenger)
    {
        // Validate only editable fields
        $rules = [];
        foreach (self::EDITABLE_FIELDS as $field => $label) {
            if ($request->has($field)) {
                $rules[$field] = $field === 'email' ? 'required|email' : 'required|string|max:100';
            }
        }
        $rules['reason'] = 'required|string|max:500';

        $validated = $request->validate($rules);
        $reason = $validated['reason'];
        unset($validated['reason']);

        // Check if trying to edit protected fields
        foreach (self::PROTECTED_FIELDS as $field) {
            if ($request->has($field) && $request->input($field) != $passenger->$field) {
                return back()->with('error', "Cannot edit protected identity field: {$field}");
            }
        }

        // Detect and validate changes
        $changes = [];
        foreach ($validated as $field => $newValue) {
            $oldValue = $passenger->$field;
            
            if ($oldValue != $newValue) {
                // Check if change is minimal (typo correction)
                if (!$this->isTypoCorrection($oldValue, $newValue)) {
                    return back()
                        ->with('error', "Change to {$field} appears substantial. Only minor typo corrections allowed.")
                        ->withInput();
                }
                
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            return back()->with('error', 'No changes detected');
        }

        DB::beginTransaction();
        try {
            // Update passenger
            $passenger->update($validated);

            // Log each change
            foreach ($changes as $field => $values) {
                PassengerEditLog::logEdit(
                    $passenger->id,
                    $field,
                    $values['old'],
                    $values['new'],
                    $reason
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.passengers.show', $passenger)
                ->with('success', 'Passenger information corrected successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to update passenger: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Check if change is a typo correction (minimal edit distance).
     */
    private function isTypoCorrection($old, $new)
    {
        $old = strtolower(trim($old ?? ''));
        $new = strtolower(trim($new ?? ''));

        // Same value
        if ($old === $new) {
            return true;
        }

        // Calculate Levenshtein distance (edit distance)
        $distance = levenshtein($old, $new);
        $maxLength = max(strlen($old), strlen($new));

        // Allow up to 20% character changes or max 3 characters
        $threshold = min(3, ceil($maxLength * 0.2));

        return $distance <= $threshold;
    }

    /**
     * View passenger history (read-only).
     */
    public function history(Passenger $passenger)
    {
        $passenger->load([
            'booking.flight',
            'editLogs.user',
            'checkIn',
            'boardingPass'
        ]);

        return view('admin.passengers.history', compact('passenger'));
    }
}
