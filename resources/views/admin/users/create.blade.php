<x-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl font-bold mb-6">Create Admin User</h2>

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Password</label>
                            <input type="password" name="password" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Role</label>
                            <select name="role" id="role" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                <option value="customer">Customer</option>
                                <option value="admin" selected>Admin</option>
                            </select>
                            @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4" id="admin-role-field">
                            <label class="block text-sm font-medium mb-2">Admin Role</label>
                            <select name="admin_role" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                <option value="super_admin">Super Admin</option>
                                <option value="operations">Operations</option>
                                <option value="finance">Finance</option>
                                <option value="support">Support</option>
                            </select>
                            @error('admin_role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">Create User</button>
                            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('role').addEventListener('change', function() {
            document.getElementById('admin-role-field').style.display = this.value === 'admin' ? 'block' : 'none';
        });
    </script>
</x-admin-layout>
