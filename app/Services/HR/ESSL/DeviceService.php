<?php

namespace App\Services\HR\ESSL;

use App\Repositories\HR\ESSL\Interfaces\DeviceRepositoryInterface;
use App\Models\HR\ESSL\Device;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log as LaravelLog;
use App\Jobs\HR\ESSL\SyncDeviceLogsJob;

/**
 * DeviceService
 *
 * Responsibilities:
 * - CRUD for devices (via repository)
 * - pingDevice (fsockopen)
 * - syncDevice (actual SOAP fetch & insert) — delegates to DeviceSoapService when available
 * - requestSync / requestSyncAll — orchestration (queue or inline)
 */
class DeviceService
{
    protected DeviceRepositoryInterface $repo;

    public function __construct(DeviceRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function list(string $propertyCode)
    {
        return $this->repo->listByProperty($propertyCode);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function create(string $propertyCode, array $data)
    {
        $data['property_code'] = $propertyCode;
        // normalize ip and port
        if (!empty($data['ip_address'])) {
            if (strpos($data['ip_address'], ':') !== false && empty($data['port'])) {
                [$ip, $port] = explode(':', $data['ip_address'], 2);
                $data['ip_address'] = $ip;
                $data['port'] = (int)$port;
            }
        }

        return $this->repo->create($data);
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        if (isset($data['ip_address']) && strpos($data['ip_address'], ':') !== false && empty($data['port'])) {
            [$ip, $port] = explode(':', $data['ip_address'], 2);
            $data['ip_address'] = $ip;
            $data['port'] = (int)$port;
        }
        return $this->repo->update($id, $propertyCode, $data);
    }

    public function delete(string $propertyCode, int $id): bool
    {
        return $this->repo->delete($id, $propertyCode);
    }

    /**
     * Ping device using fsockopen (HTTP) - returns array with status
     */
    public function pingDevice(Device $device, int $timeout = 3): array
    {
        $host = $device->ip_address;
        $port = $device->port ?: 80;

        // allow if port included in ip_address
        if (strpos($host, ':') !== false) {
            [$hostOnly, $portOnly] = explode(':', $host, 2);
            $host = $hostOnly;
            $port = (int)$portOnly;
        }

        $result = ['success' => false, 'status' => 'offline', 'last_ping_at' => null, 'message' => ''];

        try {
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if ($fp) {
                fclose($fp);
                $device->status = 'online';
                $device->last_ping_at = now();
                $device->save();
                $result['success'] = true;
                $result['status'] = 'online';
                $result['last_ping_at'] = $device->last_ping_at;
            } else {
                $device->status = 'offline';
                $device->save();
                $result['message'] = "{$errstr} ({$errno})";
            }
        } catch (\Throwable $e) {
            $device->status = 'offline';
            $device->save();
            $result['message'] = $e->getMessage();
            LaravelLog::error('Device ping error: ' . $e->getMessage(), ['device_id' => $device->id]);
        }

        return $result;
    }

    /**
     * Request sync for a device.
     *
     * If $queue=true (default) the method will dispatch a queued job (when queue configured).
     * If $queue=false or queue driver is sync, it will call syncDevice() inline and return the summary.
     *
     * @param Device $device
     * @param bool $queue
     * @param string|null $from ISO datetime (optional)
     * @param string|null $to ISO datetime (optional)
     * @return array|null
     */
    public function syncDevice(Device $device, ?string $from = null, ?string $to = null)
    {
        $soapXml = DevicePunchSyncService::fetchFromSoap($device);
        $body = (string)$soapXml;

        // Step 2️⃣ Parse logs
        $logs = DevicePunchSyncService::parseSoapLogs($body);

        // Step 3️⃣ Sync based on property + device
        $summary = DevicePunchSyncService::sync($device, $logs);

        return response()->json([
            'success' => true,
            'message' => 'Sync completed successfully',
            'data' => $summary,
        ]);
    }

    /**
     * Sync all devices for a property (sequential). Consider dispatching jobs for production.
     */
    public function syncAllForProperty(string $propertyCode): array
    {
        $devices = $this->repo->listByProperty($propertyCode);
        $summary = [];
        foreach ($devices as $d) {
            $summary[] = $this->syncDevice($d);
        }
        return $summary;
    }

    /**
     * Request sync for a device.
     *
     * If $queue=true (default) the method will dispatch a queued job (when queue configured).
     * If $queue=false or queue driver is sync, it will call syncDevice() inline and return the summary.
     *
     * @param Device $device
     * @param bool $queue
     * @param string|null $from ISO datetime (optional)
     * @param string|null $to ISO datetime (optional)
     * @return array|null
     */
    public function requestSync(Device $device, bool $queue = true, ?string $from = null, ?string $to = null): ?array
    {
        // If queue is desired and queue driver is not sync, dispatch job
        if ($queue && config('queue.default') !== 'sync') {
            SyncDeviceLogsJob::dispatch($device->id, $from, $to);
            return ['queued' => true, 'device_id' => $device->id];
        }

        // Inline sync (immediate)
        return $this->syncDevice($device, $from, $to);
    }

    /**
     * Request sync for all devices under a property.
     *
     * @param string $propertyCode
     * @param bool $queue
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public function requestSyncAll(string $propertyCode, bool $queue = true, ?string $from = null, ?string $to = null): array
    {
        $devices = $this->repo->listByProperty($propertyCode);
        $results = [];
        foreach ($devices as $d) {
            $results[] = $this->requestSync($d, $queue, $from, $to);
        }
        return $results;
    }

    public function getLogsByProperty(string $propertyCode)
    {
        $logs =  $this->repo->getAlllogsByProperty(($propertyCode));
        return response()->json([
            'success' => true,
            'message' => 'Amenities fetched successfully.',
            'data' => $logs
        ], 200);
    }
}
