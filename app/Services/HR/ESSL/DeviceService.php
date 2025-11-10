<?php

namespace App\Services\HR\ESSL;

use App\Repositories\HR\ESSL\Interfaces\DeviceRepositoryInterface;
use App\Models\HR\ESSL\Device;
use App\Models\HR\ESSL\Log;
use App\Models\HR\ESSL\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log as LaravelLog;
use App\Jobs\HR\ESSL\SyncDeviceLogsJob;
use Illuminate\Support\Facades\App;

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
            LaravelLog::error('Device ping error: '.$e->getMessage(), ['device_id' => $device->id]);
        }

        return $result;
    }

    /**
     * Sync logs for a single device for current day (or for provided range).
     *
     * This method preserves previous behaviour but delegates parsing & storing to DeviceSoapService
     * when available in the container. If DeviceSoapService is not bound, the method falls back
     * to a local implementation — but in this codebase we expect DeviceSoapService to exist.
     *
     * @param Device $device
     * @param string|null $from ISO datetime (optional)
     * @param string|null $to ISO datetime (optional)
     * @return array summary (device_id, synced_from, synced_to, fetched, inserted, duplicates, errors)
     */
    public function syncDevice(Device $device, ?string $from = null, ?string $to = null): array
    {
        // If DeviceSoapService is available in container, delegate to it.
        if (App::bound(\App\Services\HR\ESSL\DeviceSoapService::class)) {
            /** @var \App\Services\HR\ESSL\DeviceSoapService $soapService */
            $soapService = App::make(\App\Services\HR\ESSL\DeviceSoapService::class);
            return $soapService->syncDevice($device, $from, $to);
        }

        // Fallback (older inline logic) - keep behaviour identical to previous version.
        $ip = $device->ip_address;
        $port = $device->port ?: 80;
        $serial = $device->serial_number;
        $username = $device->username;
        $password = $device->password; // decrypted via accessor

        // prepare dates: today 00:00:00 to 23:59:59 or provided range
        $fromIso = $from ?: now()->startOfDay()->format('Y-m-d\\TH:i:s');
        $toIso   = $to ?: now()->endOfDay()->format('Y-m-d\\TH:i:s');

        $soapBody = '<?xml version="1.0" encoding="utf-8"?>' .
            "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">" .
            '<soap:Body>' .
            '<GetTransactionsLog xmlns="http://tempuri.org/">' .
            "<FromDateTime>{$fromIso}</FromDateTime>" .
            "<ToDateTime>{$toIso}</ToDateTime>" .
            "<SerialNumber>{$serial}</SerialNumber>" .
            "<UserName>{$username}</UserName>" .
            "<UserPassword>{$password}</UserPassword>" .
            '<strDataList></strDataList>' .
            '</GetTransactionsLog>' .
            '</soap:Body>' .
            '</soap:Envelope>';

        $url = rtrim($ip, '/') . ($port ? ":{$port}" : '') . '/WebAPIService.asmx';
        if (!preg_match('#^https?://#', $url)) {
            $url = 'http://' . $url;
        }

        $client = new Client(['timeout' => 20, 'connect_timeout' => 5]);
        $headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://tempuri.org/GetTransactionsLog'
        ];

        $fetched = 0; $inserted = 0; $duplicates = 0; $errors = [];

        try {
            $response = $client->post($url, ['body' => $soapBody, 'headers' => $headers]);
            $body = (string)$response->getBody();

            // parse SOAP response
            $xml = @simplexml_load_string($body);
            if ($xml === false) {
                throw new \Exception('Invalid SOAP response');
            }

            // Extract strDataList content if present
            $strDataList = null;
            $nodes = $xml->xpath('//strDataList');
            if (!empty($nodes)) {
                $strDataList = trim((string)$nodes[0]);
            } else {
                // fallback: search for GetTransactionsLogResult or any large text node
                $nodes2 = $xml->xpath('//GetTransactionsLogResult');
                if (!empty($nodes2)) {
                    $strDataList = trim((string)$nodes2[0]);
                }
            }

            if ($strDataList) {
                // lines separated by newlines; each line contains tab-separated values: code TAB datetime
                $lines = preg_split("/\r\n|\n|\r/", $strDataList);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    // some lines may have trailing/leading tabs/spaces
                    // split by tab or multiple spaces
                    $parts = preg_split("/\t+|\s{2,}/", $line);
                    if (count($parts) < 2) continue;

                    $empCode = trim($parts[0]);
                    $dtRaw = trim($parts[1]);
                    if (!$empCode || !$dtRaw) continue;

                    $dt = date('Y-m-d H:i:s', strtotime($dtRaw));
                    if (!$dt) continue;

                    $fetched++;

                    // check duplicate
                    $exists = Transaction::where('device_id', $device->id)
                        ->where('punch_at', $dt)
                        ->where('employee_code', $empCode)
                        ->exists();

                    if ($exists) { $duplicates++; continue; }

                    Transaction::create([
                        'property_code' => $device->property_code,
                        'device_id' => $device->id,
                        'employee_code' => $empCode,
                        'punch_at' => $dt,
                        'raw_line' => $line,
                        'raw_payload' => null,
                    ]);

                    $inserted++;
                }
            } else {
                $errors[] = 'strDataList not found or empty in SOAP response';
            }

            $device->last_sync_at = now();
            $device->save();

        } catch (\Throwable $e) {
            LaravelLog::error('Sync device error: '.$e->getMessage(), ['device_id' => $device->id, 'url' => $url]);
            $errors[] = $e->getMessage();
        }

        return [
            'device_id' => $device->id,
            'synced_from' => $fromIso,
            'synced_to' => $toIso,
            'fetched' => $fetched,
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ];
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
}
