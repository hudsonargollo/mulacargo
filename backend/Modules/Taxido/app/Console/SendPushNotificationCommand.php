<?php

namespace Modules\Taxido\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Taxido\Models\PushNotification;
use Modules\Taxido\Models\Rider;
use Modules\Taxido\Models\Driver;

class SendPushNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxido:send-push-notification {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notification to all target users in chunks asynchronously.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $pushNotification = PushNotification::find($id);

        if (!$pushNotification) {
            $this->error("PushNotification with ID {$id} not found.");
            return 1;
        }

        $this->info("Sending Push Notification ID: {$id} Target: {$pushNotification->send_to}");

        // Disable query log to prevent memory leaks
        DB::disableQueryLog();

        $query = null;
        if ($pushNotification->send_to === 'all_riders') {
            $query = Rider::whereNull('deleted_at');
        } elseif ($pushNotification->send_to === 'all_drivers') {
            $query = Driver::whereNull('deleted_at');
        }

        if (!$query) {
            $this->error("Invalid send_to group: " . $pushNotification->send_to);
            return 1;
        }

        $imageUrl = $pushNotification?->image_notify?->original_url ?? null;
        $title = $pushNotification->title;
        $message = $pushNotification->message;
        $url = $pushNotification->url;

        // Process in chunks of 200 users
        $query->select('id')->chunkById(200, function ($users) use ($title, $message, $imageUrl, $url) {
            $notifications = [];

            foreach ($users as $user) {
                $notificationData = [];
                if ($url) {
                    $notificationData['url'] = $url;
                }

                $notification = [
                    'message' => [
                        'topic' => "user_{$user->id}",
                        'data' => array_merge($notificationData, [
                            'title' => $title,
                            'body' => $message,
                            'message' => $message,
                        ]),
                        'notification' => [
                            'title' => $title,
                            'body' => $message,
                            'image' => $imageUrl,
                        ],
                    ],
                ];

                if (empty($notificationData)) {
                    unset($notification['message']['data']);
                }

                $notifications[] = $notification;
            }

            if (!empty($notifications)) {
                pushNotificationMulti($notifications);
            }

            // Force garbage collection to prevent memory buildup
            gc_collect_cycles();
        });

        $this->info("Push notification sending finished successfully.");
        return 0;
    }
}
