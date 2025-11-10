<?php

namespace App\Jobs\HR\ESSL;

use App\Models\HR\ESSL\Device;
use App\Services\HR\ESSL\DeviceSoapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as LaravelLog;

class SyncDeviceLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    protected int $deviceId;
    protected ?string $from;
    protected ?string $to;

    public function __construct(int $deviceId, ?string $from = null, ?string $to = null)
    {
        $this->deviceId = $deviceId;
        $this->from = $from;
        $this->to = $to;
    }

    public function handle(DeviceSoapService $soapService)
    {
        $device = Device::find($this->deviceId);
        if (!$device) {
            LaravelLog::warning('SyncDeviceLogsJob: device not found', ['device_id' => $this->deviceId]);
            return;
        }

        $summary = $soapService->syncDevice($device, $this->from, $this->to);

        LaravelLog::info('SyncDeviceLogsJob completed', array_merge(['device_id' => $this->deviceId], $summary));
    }

    public function failed(\Throwable $exception)
    {
        LaravelLog::error('SyncDeviceLogsJob failed', [
            'device_id' => $this->deviceId,
            'error' => $exception->getMessage()
        ]);
    }
}
