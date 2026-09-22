<?php

namespace App\Repositories\Admin;

use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\User;
use Exception;
use Spatie\Permission\Models\Role;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatRepository extends BaseRepository
{
    public function model()
    {
        return Message::class;
    }


    public function getEffectiveChatUserId(): int
    {
        $user = auth()->user();

        if (!$user) {
            return auth()->id();
        }

        // Admin operates as themselves
        if ($user->hasRole('admin')) {
            return $user->id;
        }

        // Custom role: module IS NULL and system_reserve = 0
        $isCustomRole = $user->roles()
            ->whereNull('module')
            ->where('system_reserve', false)
            ->exists();

        if ($isCustomRole && $user->hasPermissionTo('chat.index')) {
            return (int) getAdminId();
        }

        return $user->id;
    }

    /**
     * Get all chat-relevant roles from Spatie.
     */
    public function getRoles()
    {
        return Role::where('status', true)->whereNot('name', 'admin')->withCount('users')->orderBy('name', 'ASC')->get();
    }

    /**
     * Get recent chats for the effective chat user (admin ID for custom-role staff).
     */
    public function getRecentChats()
    {
        $currentUserId = $this->getEffectiveChatUserId();

        $rooms = ChatRoom::where('participants', 'like', "%\"{$currentUserId}\"%")
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        $recentChats = [];
        foreach ($rooms as $room) {
            $participants = $room->participants;
            if (!is_array($participants)) continue;

            $otherUserId = collect($participants)->first(fn($id) => $id != $currentUserId);
            if (!$otherUserId) continue;

            $user = User::with('profile_image')->find($otherUserId);
            if ($user) {
                $recentChats[$otherUserId] = [
                    'user_id'      => $otherUserId,
                    'name'         => $user->name,
                    'image'        => $user->profile_image?->original_url ?? null,
                    'role'         => $user->getRoleNames()->first() ?? 'User',
                    'chat_id'      => $room->room_id,
                    'last_message' => $room->last_message,
                    'unread_count' => $room->unread_count[(string) $currentUserId] ?? 0,
                    'updated_at'   => $room->updated_at,
                ];
            }
        }

        return array_values($recentChats);
    }

    /**
     * Get users by role ID (uses effective chat user context).
     */
    public function getUsersByRole($roleId)
    {
        $role = Role::find($roleId);
        if (!$role) return collect([]);

        $currentUserId = $this->getEffectiveChatUserId();

        $users = User::role($role->name)
            ->where('users.id', '!=', $currentUserId)
            ->whereNull('deleted_at')
            ->with(['profile_image'])
            ->get();

        $rooms = ChatRoom::where('participants', 'like', "%\"{$currentUserId}\"%")->get();
        $roomLookup = [];
        foreach ($rooms as $room) {
            $otherId = collect($room->participants)->first(fn($id) => $id != $currentUserId);
            if ($otherId) {
                $roomLookup[$otherId] = $room->updated_at;
            }
        }

        foreach ($users as $user) {
            $user->last_interaction = $roomLookup[$user->id] ?? null;
        }

        return $users;
    }

    /**
     * Get total unread messages for the effective chat user across all rooms.
     */
    public function getTotalUnreadCount()
    {
        $userId = (string) $this->getEffectiveChatUserId();
        $rooms  = ChatRoom::where('participants', 'like', "%\"{$userId}\"%")->get();

        $total = 0;
        foreach ($rooms as $room) {
            $total += $room->unread_count[$userId] ?? 0;
        }

        return $total;
    }
}
