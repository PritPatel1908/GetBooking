<tbody class="bg-white divide-y divide-gray-200">
    @forelse($users as $user)
    <tr class="hover:bg-gray-50 transition-colors" data-user-id="{{ $user->id }}">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <img class="h-10 w-10 rounded-full mr-3" src="{{ $user->profile_photo_path ? asset($user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $user->name }}">
                <div>
                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500">{{ ucfirst($user->user_type ?? 'User') }}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $user->phone }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('d M Y') }}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 text-xs rounded-full {{ $user->user_type == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }} font-medium">
                {{ ucfirst($user->user_type) }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.show', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View User">
                    <i class="fas fa-eye"></i>
                </a>
                <button class="text-blue-600 hover:text-blue-900 transition-colors edit-user-btn" title="Edit User" data-user-id="{{ $user->id }}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-red-600 hover:text-red-900 transition-colors delete-user-btn" title="Delete User" data-user-id="{{ $user->id }}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
            No users found. Add your first user by clicking the "Add User" button.
        </td>
    </tr>
    @endforelse
</tbody>

<!-- Pagination -->
<div class="flex items-center justify-between mt-6">
    <div class="text-sm text-gray-600">
        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} entries
    </div>
    <div>
        {{ $users->links() }}
    </div>
</div>
