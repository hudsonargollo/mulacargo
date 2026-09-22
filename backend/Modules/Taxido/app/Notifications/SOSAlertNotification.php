<?php

namespace Modules\Taxido\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

use App\Models\EmailTemplate;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SOSAlertNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public $ride;
    public $sos;
    public $language;

    public function __construct($ride, $sos, $language = null)
    {
        $this->ride = $ride;
        $this->sos  = $sos;
        $this->language = $language ?? getDefaultLangLocale();
        app()->setLocale($this->language);
    }

    public function via($notifiable): array
    {
        $template = EmailTemplate::where('slug', 'sos-alert-admin')->first();
        if ($template && isset($template->status) && !$template->status) {
            return ['database', 'broadcast'];
        }

        // Avoid crashing on local environments with unconfigured mail settings
        $host = config('mail.mailers.smtp.host');
        if ($host === 'ENTER_YOUR_HOST' || empty($host)) {
            return ['database', 'broadcast'];
        }

        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('taxido::static.sos.email.subject'))
            ->greeting(__('taxido::static.sos.email.greeting'))
            ->line(__('taxido::static.sos.email.body'))
            ->line(__('taxido::static.sos.email.ride_id', ['ride_number' => $this->ride->ride_number]))
            ->line(__('taxido::static.sos.email.coordinates', [
                'lat' => $this->sos->location_coordinates['lat'],
                'lng' => $this->sos->location_coordinates['lng']
            ]))
            ->action(__('taxido::static.sos.email.action'), url('/admin/sos-alerts/' . $this->sos->id))
            ->line(__('taxido::static.sos.email.footer'));
    }

    public function toArray($notifiable): array
    {
        $createdBy = $this->sos->created_by;

        $isDriver = false;
        if ($createdBy) {
            if ($createdBy->hasRole('driver') || ($this->ride && $createdBy->id === $this->ride->driver_id)) {
                $isDriver = true;
            }
        } elseif ($this->ride) {
            if ($this->sos->created_by_id && $this->sos->created_by_id === $this->ride->driver_id) {
                $isDriver = true;
            }
        }

        if ($isDriver) {
            $driverName = $createdBy?->name ?? $this->ride?->driver?->name ?? 'N/A';
            $vehicleType = $this->ride?->vehicle_type?->name ?? 'N/A';

            if ($this->ride) {
                $message = __('taxido::static.sos.notification.driver_message', [
                    'driver_name' => $driverName,
                    'vehicle_type' => $vehicleType,
                ]);

            } else {
                $message = __('taxido::static.sos.notification.driver_message_no_ride', [
                    'driver_name' => $driverName,
                    'vehicle_type' => $vehicleType,
                ]);
            }
        } else {
            $riderName = $createdBy?->name ?? $this->ride?->rider?->name ?? 'N/A';
            $riderEmail = $createdBy?->email ?? $this->ride?->rider?->email ?? 'N/A';

            if ($this->ride) {
                $message = __('taxido::static.sos.notification.rider_message', [
                    'rider_name' => $riderName,
                    'rider_email' => $riderEmail,
                    'ride_number' => null,
                ]);
            } else {
                $message = __('taxido::static.sos.notification.rider_message_no_ride', [
                    'rider_name' => $riderName,
                    'rider_email' => $riderEmail,
                ]);
            }
        }

        return [
            'title'   => __('taxido::static.sos.notification.title'),
            'message' => $message,
            'type'    => 'sos_alert',
        ];
    }
}
