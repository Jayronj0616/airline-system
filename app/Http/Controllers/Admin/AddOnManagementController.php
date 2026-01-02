<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\AddOnAvailability;
use App\Models\FareClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddOnManagementController extends Controller
{
    public function index()
    {
        $addOns = AddOn::withCount('availability')->orderBy('type')->paginate(20);
        
        return view('admin.add-ons.index', compact('addOns'));
    }

    public function create()
    {
        $types = ['baggage', 'meal', 'seat_upgrade', 'insurance', 'priority_boarding', 'lounge_access'];
        
        return view('admin.add-ons.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:add_ons',
            'type' => 'required|in:baggage,meal,seat_upgrade,insurance,priority_boarding,lounge_access',
            'description' => 'nullable|string|max:500',
            'base_price' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $addOn = AddOn::create($validated);

        return redirect()
            ->route('admin.add-ons.show', $addOn)
            ->with('success', 'Add-on created successfully');
    }

    public function show(AddOn $addOn)
    {
        $addOn->load(['availability.fareClass']);
        $fareClasses = FareClass::all();
        
        return view('admin.add-ons.show', compact('addOn', 'fareClasses'));
    }

    public function edit(AddOn $addOn)
    {
        $types = ['baggage', 'meal', 'seat_upgrade', 'insurance', 'priority_boarding', 'lounge_access'];
        
        return view('admin.add-ons.edit', compact('addOn', 'types'));
    }

    public function update(Request $request, AddOn $addOn)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:add_ons,code,' . $addOn->id,
            'type' => 'required|in:baggage,meal,seat_upgrade,insurance,priority_boarding,lounge_access',
            'description' => 'nullable|string|max:500',
            'base_price' => 'required|numeric|min:0',
            'max_quantity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $addOn->update($validated);

        return redirect()
            ->route('admin.add-ons.show', $addOn)
            ->with('success', 'Add-on updated successfully');
    }

    public function destroy(AddOn $addOn)
    {
        // Check if used in bookings
        if ($addOn->bookingAddOns()->exists()) {
            return back()->with('error', 'Cannot delete add-on that has been used in bookings');
        }

        $addOn->delete();

        return redirect()
            ->route('admin.add-ons.index')
            ->with('success', 'Add-on deleted successfully');
    }

    public function toggleActive(AddOn $addOn)
    {
        $addOn->update(['is_active' => !$addOn->is_active]);

        return back()->with('success', 'Add-on status updated');
    }

    public function addAvailability(Request $request, AddOn $addOn)
    {
        $validated = $request->validate([
            'route_origin' => 'nullable|string|size:3',
            'route_destination' => 'nullable|string|size:3',
            'fare_class_id' => 'nullable|exists:fare_classes,id',
            'price_override' => 'nullable|numeric|min:0',
        ]);

        // Check if already exists
        $exists = $addOn->availability()
            ->where('route_origin', $validated['route_origin'] ?? null)
            ->where('route_destination', $validated['route_destination'] ?? null)
            ->where('fare_class_id', $validated['fare_class_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This availability rule already exists');
        }

        $addOn->availability()->create([
            'route_origin' => $validated['route_origin'] ?? null,
            'route_destination' => $validated['route_destination'] ?? null,
            'fare_class_id' => $validated['fare_class_id'] ?? null,
            'price_override' => $validated['price_override'] ?? null,
            'is_available' => true,
        ]);

        return back()->with('success', 'Availability rule added');
    }

    public function removeAvailability(AddOnAvailability $availability)
    {
        $availability->delete();

        return back()->with('success', 'Availability rule removed');
    }

    public function toggleAvailability(AddOnAvailability $availability)
    {
        $availability->update(['is_available' => !$availability->is_available]);

        return back()->with('success', 'Availability rule updated');
    }
}
