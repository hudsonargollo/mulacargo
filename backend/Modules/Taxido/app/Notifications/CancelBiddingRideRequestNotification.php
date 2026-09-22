<?php

namespace Modules\Taxido\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Taxido\Models\RideRequest;

class CancelBiddingRideRequestNotification extends Notification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly RideRequest $rideRequest) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $template = EmailTemplate::where('slug', 'ride-status-driver-cancelled')->first();
        if ($template && isset($template->status) && !$template->status) {
            return ['database'];
        }
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $content = EmailTemplate::where('slug', 'ride-status-driver-cancelled')->first();
        
        $locale = request()->hasHeader('Accept-Lang')
            ? request()->header('Accept-Lang')
            : app()->getLocale();

        if ($content) {
            $data = [
                '{{driver_name}}' => $notifiable->name ?? 'Driver',
                '{{ride_number}}' => $this->rideRequest->ride_number,
                '{{Your Company Name}}' => config('app.name'),
            ];

            $emailContent = str_replace(array_keys($data), array_values($data), $content->content[$locale] ?? $content->content['en'] ?? '');

            return (new MailMessage)
                ->subject($content->title[$locale] ?? $content->title['en'] ?? 'Ride Request Cancelled')
                ->markdown('taxido::emails.email-template', [
                    'content' => $content,
                    'emailContent' => $emailContent,
                    'locale' => $locale,
                ]);
        }

        return (new MailMessage)
            ->subject('Ride Request Cancelled')
            ->line("Hello, the ride request #{$this->rideRequest->ride_number} has been cancelled by the rider.")
            ->line("Thank you for using our application!");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ride Request Cancelled',
            'message' => "Ride request #{$this->rideRequest->ride_number} has been cancelled by the rider.",
            'type' => 'cancel_bidding_ride_request',
            'ride_request_id' => $this->rideRequest->id,
            'ride_number' => $this->rideRequest->ride_number,
        ];
    }
}
