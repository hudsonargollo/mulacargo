<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;


function isCustomChatRole($user): bool
{
    if (!$user) return false;

    $isCustomRole = $user->roles()
        ->whereNull('module')
        ->where('system_reserve', false)
        ->exists();

    return $isCustomRole && $user->hasPermissionTo('chat.index');
}


Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    $room = DB::table('chat_rooms')
        ->where('room_id', $roomId)
        ->first();

    if (!$room) {
        $ids = explode('_', $roomId);
        if (in_array((string) $user->id, $ids)) {
            return true;
        }
        // Allow custom-role staff when admin's ID is in the generated room ID
        $adminId = (string) getAdminId();
        if (in_array($adminId, $ids) && isCustomChatRole($user)) {
            return true;
        }
        return false;
    }

    $participants = json_decode($room->participants, true);
    if (!is_array($participants)) {
        return false;
    }

    // Direct participant
    if (in_array((string) $user->id, $participants)) {
        return true;
    }

    // Custom-role staff: allowed when admin is a participant in the room
    $adminId = (string) getAdminId();
    if (in_array($adminId, $participants) && isCustomChatRole($user)) {
        return true;
    }

    return false;
}, ['guards' => ['web', 'sanctum']]);



Broadcast::channel('user.notifications.{userId}', function ($user, $userId) {
    // Own channel
    if ((string) $user->id === (string) $userId) {
        return true;
    }

    // Custom-role staff listening to admin's notification channel
    $adminId = (string) getAdminId();
    if ((string) $userId === $adminId && isCustomChatRole($user)) {
        return true;
    }

    return false;
}, ['guards' => ['web', 'sanctum']]);
