<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\User;
use App\Services\SendNotificationService;
use Exception;
use Illuminate\Http\Request;
use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Events\ChatUnreadCountUpdated;
use App\Repositories\Admin\ChatRepository;
use App\Http\Resources\Api\MessageResource;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    protected $repository;

    public function __construct(ChatRepository $repository)
    {
        $this->repository = $repository;
    }

    private function getEffectiveChatUser(): User
    {
        $authUser = auth()->user();

        if (!$authUser || $authUser->hasRole('admin')) {
            return $authUser;
        }

        $isCustomRole = $authUser->roles()
            ->whereNull('module')
            ->where('system_reserve', false)
            ->exists();

        if ($isCustomRole && $authUser->hasPermissionTo('chat.index')) {
            $admin = getAdmin();
            return $admin ?? $authUser;
        }

        return $authUser;
    }

    public function send(Request $request)
    {
        $request->validate([
            'room_id'     => 'required|string',
            'receiver_id' => 'required|numeric',
            'message'     => 'nullable|string',
            'images'      => 'nullable|array',
            'images.*'    => 'image|max:2048',
        ]);

        // Custom-role staff send AS admin
        $user        = $this->getEffectiveChatUser();
        $hasImages   = $request->hasFile('images') && count($request->file('images')) > 0;
        $messageText = $hasImages ? null : $request->message;

        $message = Message::create([
            'room_id'     => $request->room_id,
            'sender_id'   => $user->id,
            'receiver_id' => $request->receiver_id,
            'sender_name' => $user->name,
            'message'     => $request->message,
            'images'      => null,
            'is_read'     => false,
        ]);

        if ($hasImages) {
            foreach ($request->file('images') as $file) {
                $message->addMedia($file)->toMediaCollection('chat_images');
            }
        }

        $message    = $message->fresh();
        $imagesData = $message->images;
        $room = ChatRoom::updateOrCreate(
            ['room_id' => $request->room_id],
            [
                'participants' => [
                    (string) $user->id,
                    (string) $request->receiver_id
                ],
                'last_message' => [
                    'message' => $messageText,
                    'images'  => $imagesData
                ],
                'updated_at' => now()
            ]
        );
        $room->incrementUnread($request->receiver_id);
        broadcast(new ChatMessageSent($message));

        $receiverTotalUnread = ChatRoom::where('participants', 'like', "%\"{$request->receiver_id}\"%")
            ->get()
            ->sum(fn($r) => $r->unread_count[(string) $request->receiver_id] ?? 0);

        broadcast(new ChatUnreadCountUpdated(
            $request->receiver_id,
            $receiverTotalUnread,
            $room->room_id,
            $room->unread_count[(string) $request->receiver_id] ?? 0,
            $message->message ?? (count($message->images ?? []) > 0 ? 'Sent an image' : '')
        ));

        $receiver = User::find($request->receiver_id);
        if ($receiver) {
            $notificationText = $message->message ?? (count($message->images ?? []) > 0 ? 'Sent an image' : 'New Message');
            $imageUrl         = !empty($imagesData) ? reset($imagesData) : null;

            $pushData = [
                'type'        => 'chat',
                'room_id'     => $room->room_id,
                'sender_id'   => (string) $user->id,
                'receiver_id' => (string) $receiver->id,
            ];

            if ($imageUrl) {
                $pushData['image'] = $imageUrl;
            }

            try {
                SendNotificationService::sendPushNotification(
                    $receiver,
                    'chat-message-received',
                    [
                        'sender_name' => $user->name,
                        'message'     => $notificationText
                    ],
                    $pushData
                );
            } catch (Exception $e) {
                Log::error("Chat Push Notification Error: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'       => true,
            'room_id'      => $room->room_id,
            'data'         => $message,
            'total_unread' => $this->repository->getTotalUnreadCount()
        ]);
    }

    public function clear(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string'
        ]);

        $effectiveUser = $this->getEffectiveChatUser();
        $userId        = $effectiveUser->id;
        $messages      = Message::where('room_id', $request->room_id)->get();

        foreach ($messages as $msg) {
            $clearedBy = $msg->cleared_by ?? [];
            if (!in_array($userId, $clearedBy)) {
                $clearedBy[] = (string) $userId;
                $msg->update(['cleared_by' => $clearedBy]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Chat cleared'
        ]);
    }

    public function messages(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string'
        ]);

        $effectiveUser = $this->getEffectiveChatUser();
        $userId        = $effectiveUser->id;

        $room = ChatRoom::where('room_id', $request->room_id)->first();
        if ($room) {
            $room->markAsRead($userId);
            broadcast(new ChatUnreadCountUpdated(
                $userId,
                $this->repository->getTotalUnreadCount(),
                $room->room_id,
                0
            ));
        }

        $messages = Message::where('room_id', $request->room_id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return response()->json([
            'status'       => true,
            'data'         => MessageResource::collection($messages),
            'total_unread' => $this->repository->getTotalUnreadCount()
        ]);
    }

    /**
     * Get total unread count for the header.
     */
    public function unreadCount()
    {
        return response()->json([
            'status'       => true,
            'total_unread' => $this->repository->getTotalUnreadCount()
        ]);
    }
}
