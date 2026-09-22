<?php

namespace Modules\Taxido\Repositories\Admin;

use Exception;
use App\Models\User;
use Modules\Taxido\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Modules\Taxido\Models\Driver;
use Modules\Taxido\Models\PushNotification;
use Modules\Taxido\Models\Rider;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class PushNotificationRepository extends BaseRepository
{
    protected $ride;

    protected $user;

    public function model()
    {
        $this->user = new User();
        return PushNotification::class;
    }

    public function index($pushNotificationTable)
    {
        return view('taxido::admin.push-notification.index', ['tableConfig' => $pushNotificationTable]);
    }

    public function create($request)
    {
        return view('taxido::admin.push-notification.create');
    }

    public function sendNotification($request)
    {
        DB::beginTransaction();
        try {
            $pushNotification = $this->model->create([
                'send_to' => $request->send_to,
                'ride_id' => $request->ride_id ?? null,
                'title' => $request->title,
                'message' => $request->message,
                // 'url' => $request->url,
                'notification_type' => $request->send_to,
                'user_id' => getCurrentUserId(),
                'image_id' => $request->image_id,
                'is_scheduled' => $request->schedule,
                'scheduled_at' => $request->scheduleat,
            ]); 

            if ($request->hasFile('image')) {
                $pushNotification->addMedia($request->file('image'))?->toMediaCollection('notification_image');
                $pushNotification->image_url = $pushNotification->getFirstMediaUrl('notification_image');
                $pushNotification->save();
            }

            $hasUsers = false;
            if ($request->send_to === 'all_riders') {
                $hasUsers = Rider::whereNull('deleted_at')->exists();
            } elseif ($request->send_to === 'all_drivers') {
                $hasUsers = Driver::whereNull('deleted_at')->exists();
            }

            if (!$hasUsers) {
                throw new Exception('No users found for the selected group.');
            }

            DB::commit();

            if (!$request->schedule) {
                $phpPath = (new \Symfony\Component\Process\PhpExecutableFinder())->find() ?: 'php';
                $artisanPath = base_path('artisan');
                $basePath = base_path();
                $logPath = storage_path('logs/push-notifications.log');
                $isExecEnabled = function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')));

                if ($isExecEnabled) {
                    $command = "cd {$basePath} && nohup {$phpPath} {$artisanPath} taxido:send-push-notification {$pushNotification->id} >> {$logPath} 2>&1 &";
                    exec($command);
                } else {
                    @set_time_limit(0);
                    @ini_set('memory_limit', '-1');
                    try {
                        \Illuminate\Support\Facades\Artisan::call('taxido:send-push-notification', [
                            'id' => $pushNotification->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Push notification inline sending error: " . $e->getMessage());
                    }
                }
            } 
            Log::info($pushNotification);

            return redirect()->route('admin.push-notification.index')->with('success', __('taxido::static.push_notification.sent_notification'));

        } catch (Exception $e) {

            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $pushNotification = $this->model->findOrFail($id);
            $pushNotification->destroy($id);

            DB::commit();
            return to_route('admin.push-notification.index')->with('success', __('taxido::static.push_notification.delete_successfully'));
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function forceDelete($id)
    {
        try {

            $pushNotification = $this->model->onlyTrashed()->findOrFail($id);
            $pushNotification->forceDelete();

            return redirect()->back()->with('success', __('taxido::static.push_notification.permanent_delete_successfully'));
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function scheduleStatus()
    {
        try {
            $now = Carbon::now();
            $pushNotifications = PushNotification::where('is_scheduled', '!=', 0)
                ->where('scheduled_at', '<', $now)
                ->get();

            $phpPath = (new \Symfony\Component\Process\PhpExecutableFinder())->find() ?: 'php';
            $artisanPath = base_path('artisan');
            $basePath = base_path();
            $logPath = storage_path('logs/push-notifications.log');
            $isExecEnabled = function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')));

            foreach ($pushNotifications as $pushNotification) {
                $pushNotification->update(['is_scheduled' => 0]);

                if ($isExecEnabled) {
                    $command = "cd {$basePath} && nohup {$phpPath} {$artisanPath} taxido:send-push-notification {$pushNotification->id} >> {$logPath} 2>&1 &";
                    exec($command);
                } else {
                    @set_time_limit(0);
                    @ini_set('memory_limit', '-1');
                    try {
                        \Illuminate\Support\Facades\Artisan::call('taxido:send-push-notification', [
                            'id' => $pushNotification->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Push notification inline sending error: " . $e->getMessage());
                    }
                }
            }

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
