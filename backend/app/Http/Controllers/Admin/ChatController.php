<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\ChatRepository;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $repository;

    public function __construct(ChatRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $roles       = $this->repository->getRoles();
        $recentChats = $this->repository->getRecentChats();
        $defaultUsers = collect([]);
        if ($roles->count()) {
            $defaultUsers = $this->repository->getUsersByRole($roles->first()->id);
        }

        $user = auth()->user();
        $chatUserId = $this->repository->getEffectiveChatUserId();
        if ($chatUserId !== $user->id) {
            $chatUser = \App\Models\User::find($chatUserId);
            $token    = $chatUser->createToken('admin_chat')->plainTextToken;
        } else {
            $token = $user->createToken('admin_chat')->plainTextToken;
        }

        return view('admin.chat.index', [
            'roles'        => $roles,
            'defaultUsers' => $defaultUsers,
            'recentChats'  => $recentChats,
            'access_token' => $token,
            'chatUserId'   => $chatUserId,   // ← admin's ID for custom-role staff
        ]);
    }

    /**
     * Get users by role ID (AJAX).
     */
    public function getUsersByRole(Request $request)
    {
        $users = $this->repository->getUsersByRole($request->role_id);

        return response()->json([
            'status' => true,
            'data'   => $users
        ]);
    }

    /**
     * Get recent chats (AJAX).
     */
    public function recent()
    {
        $recentChats = $this->repository->getRecentChats();
        return response()->json([
            'status' => true,
            'data'   => $recentChats
        ]);
    }
}
