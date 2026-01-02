<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('admin_role')) {
            $query->where('admin_role', $request->admin_role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,admin',
            'admin_role' => 'nullable|required_if:role,admin|in:super_admin,operations,finance,support',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'admin_role' => $validated['admin_role'] ?? null,
            'is_active' => true,
        ]);

        // Audit log
        UserAuditLog::create([
            'user_id' => $user->id,
            'performed_by' => auth()->id(),
            'action' => 'created',
            'new_value' => json_encode([
                'role' => $user->role,
                'admin_role' => $user->admin_role,
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load(['auditLogs.performedBy', 'bookings']);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:customer,admin',
            'admin_role' => 'nullable|required_if:role,admin|in:super_admin,operations,finance,support',
        ]);

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'admin_role' => $user->admin_role,
        ];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'admin_role' => $validated['admin_role'] ?? null,
        ]);

        // Audit log
        UserAuditLog::create([
            'user_id' => $user->id,
            'performed_by' => auth()->id(),
            'action' => 'updated',
            'old_value' => json_encode($oldValues),
            'new_value' => json_encode([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'admin_role' => $user->admin_role,
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully');
    }

    public function updateRole(Request $request, User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'admin_role' => 'required|in:super_admin,operations,finance,support',
        ]);

        $oldRole = $user->admin_role;

        $user->update([
            'role' => 'admin',
            'admin_role' => $validated['admin_role'],
        ]);

        // Audit log
        UserAuditLog::create([
            'user_id' => $user->id,
            'performed_by' => auth()->id(),
            'action' => 'role_changed',
            'old_value' => $oldRole,
            'new_value' => $validated['admin_role'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Role updated successfully');
    }

    public function disable(Request $request, User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot disable your own account');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'is_active' => false,
            'disabled_at' => now(),
            'disabled_by' => auth()->id(),
            'disabled_reason' => $validated['reason'],
        ]);

        // Audit log
        UserAuditLog::create([
            'user_id' => $user->id,
            'performed_by' => auth()->id(),
            'action' => 'account_disabled',
            'new_value' => $validated['reason'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Account disabled successfully');
    }

    public function enable(User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Unauthorized');
        }

        $user->update([
            'is_active' => true,
            'disabled_at' => null,
            'disabled_by' => null,
            'disabled_reason' => null,
        ]);

        // Audit log
        UserAuditLog::create([
            'user_id' => $user->id,
            'performed_by' => auth()->id(),
            'action' => 'account_enabled',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Account enabled successfully');
    }

    public function auditLogs(User $user)
    {
        $logs = $user->auditLogs()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.audit-logs', compact('user', 'logs'));
    }
}
