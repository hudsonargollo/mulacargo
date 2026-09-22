<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PushNotificationTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PushNotificationTemplate::updateOrCreate(
            ['slug' => 'chat-message-received'],
            [
                'title' => ['en' => '💬 New Message from {{sender_name}}'],
                'content' => ['en' => '{{message}}'],
                'url' => ['en' => ''],
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PushNotificationTemplate::where('slug', 'chat-message-received')->delete();
    }
};
