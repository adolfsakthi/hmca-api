<?php

namespace App\Services\HR\ESSL;

use App\Models\HR\ESSL\Device;
use App\Models\HR\ESSL\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceSoapService
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->config = config('essl', [
            'soap_timeout' => 5000,
            'soap_connect_timeout' => 5,
            'insert_chunk' => 200,
            'log_path' => storage_path('logs/essl-soap.log'),
        ]);

        $this->client = new Client([
            'timeout' => $this->config['soap_timeout'],
            'connect_timeout' => $this->config['soap_connect_timeout'],
        ]);
    }

    /**
     * Sync one device for given range (ISO strings or null -> today).
     * Returns summary array.
     */
    public function syncDevice(Device $device, ?string $from = null, ?string $to = null): array
    {
        $errors = [];
        $fetched = 0;
        $inserted = 0;
        $duplicates = 0;

        $fromCarbon = $this->toCarbonOrDefault($from, now()->startOfDay());
        $toCarbon   = $this->toCarbonOrDefault($to, now()->endOfDay());

        $soapBody = $this->buildSoapBody($device, $fromCarbon, $toCarbon);
        $url = $this->buildServiceUrl($device);

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'SOAPAction'   => 'http://tempuri.org/GetTransactionsLog',
                ],
                'body' => $soapBody,
            ]);

            $body = (string) $response->getBody();
        } catch (\Throwable $e) {
            $msg = "SOAP request failed: " . $e->getMessage();
            LaravelLog::error($msg, ['device_id' => $device->id, 'url' => $url]);
            $errors[] = $msg;

            return $this->summary($device, $fromCarbon, $toCarbon, $fetched, $inserted, $duplicates, $errors);
        }

        $strDataList = $this->extractStrDataList($body);
        if ($strDataList === null) {
            $err = 'strDataList not found or empty in SOAP response';
            LaravelLog::warning($err, ['device_id' => $device->id, 'snippet' => substr($body, 0, 200)]);
            $errors[] = $err;
            return $this->summary($device, $fromCarbon, $toCarbon, $fetched, $inserted, $duplicates, $errors);
        }

        $rows = $this->parseStrDataList($strDataList);
        $fetched = count($rows);

        if ($fetched === 0) {
            return $this->summary($device, $fromCarbon, $toCarbon, $fetched, $inserted, $duplicates, $errors);
        }

        $prepared = [];
        foreach ($rows as $r) {
            if (empty($r['employee_code']) || empty($r['punch_at'])) continue;
            $prepared[] = [
                'property_code' => $device->property_code,
                'device_id'     => $device->id,
                'employee_code' => $r['employee_code'],
                'punch_at'      => $r['punch_at'],
                'raw_line'      => $r['raw_line'] ?? null,
                'raw_payload'   => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        DB::beginTransaction();
        try {
            $chunks = array_chunk($prepared, $this->config['insert_chunk'] ?? 200);
            foreach ($chunks as $chunk) {
                $toInsert = [];
                foreach ($chunk as $cand) {
                    $exists = Transaction::where('device_id', $cand['device_id'])
                        ->where('employee_code', $cand['employee_code'])
                        ->where('punch_at', $cand['punch_at'])
                        ->exists();

                    if ($exists) {
                        $duplicates++;
                    } else {
                        $toInsert[] = $cand;
                    }
                }

                if (!empty($toInsert)) {
                    Transaction::insert($toInsert);
                    $inserted += count($toInsert);
                }
            }

            $device->last_sync_at = now();
            $device->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $msg = 'DB insert error: ' . $e->getMessage();
            LaravelLog::error($msg, ['device_id' => $device->id]);
            $errors[] = $msg;
        }

        return $this->summary($device, $fromCarbon, $toCarbon, $fetched, $inserted, $duplicates, $errors);
    }

    protected function summary(Device $device, Carbon $from, Carbon $to, int $fetched, int $inserted, int $duplicates, array $errors): array
    {
        return [
            'device_id' => $device->id,
            'synced_from' => $from->toDateTimeString(),
            'synced_to' => $to->toDateTimeString(),
            'fetched' => $fetched,
            'inserted' => $inserted,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ];
    }

    protected function buildSoapBody(Device $device, Carbon $from, Carbon $to): string
    {
        $serial = htmlspecialchars($device->serial_number ?? '', ENT_XML1);
        $username = htmlspecialchars($device->username ?? '', ENT_XML1);
        $password = htmlspecialchars($device->password ?? '', ENT_XML1);

        $fromS = $from->format('Y-m-d\TH:i:s');
        $toS   = $to->format('Y-m-d\TH:i:s');

        $soap = '<?xml version="1.0" encoding="utf-8"?>' .
            '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">' .
            '<soap:Body>' .
            '<GetTransactionsLog xmlns="http://tempuri.org/">' .
            "<FromDateTime>{$fromS}</FromDateTime>" .
            "<ToDateTime>{$toS}</ToDateTime>" .
            "<SerialNumber>{$serial}</SerialNumber>" .
            "<UserName>{$username}</UserName>" .
            "<UserPassword>{$password}</UserPassword>" .
            '<strDataList></strDataList>' .
            '</GetTransactionsLog>' .
            '</soap:Body>' .
            '</soap:Envelope>';

        return $soap;
    }

    protected function buildServiceUrl(Device $device): string
    {
        $ip = $device->ip_address;
        $port = $device->port ?: null;

        $host = $ip;
        if ($port && strpos($ip, ':') === false) {
            $host = rtrim($ip, '/').":{$port}";
        }

        if (!preg_match('#^https?://#', $host)) {
            $host = 'http://'.$host;
        }

        return rtrim($host, '/').'/WebAPIService.asmx';
    }

    protected function extractStrDataList(string $soapResponse): ?string
    {
        try {
            $xml = @simplexml_load_string($soapResponse);
            if ($xml === false) return null;

            $nodes = $xml->xpath('//strDataList');
            if (!empty($nodes)) {
                $content = trim((string)$nodes[0]);
                if ($content !== '') return $content;
            }

            $nodes2 = $xml->xpath('//GetTransactionsLogResult');
            if (!empty($nodes2)) {
                $content = trim((string)$nodes2[0]);
                if ($content !== '') return $content;
            }

            foreach ($xml->xpath('//*') as $n) {
                $t = trim((string)$n);
                if (strlen($t) > 50 && strpos($t, "\n") !== false) {
                    return $t;
                }
            }

            return null;
        } catch (\Throwable $e) {
            LaravelLog::error('extractStrDataList parse error: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Parse strDataList content into rows.
     * Returns array of ['employee_code'=>..., 'punch_at'=> 'Y-m-d H:i:s', 'raw_line'=>...]
     */
    public function parseStrDataList(string $str): array
    {
        $rows = [];
        $lines = preg_split("/\r\n|\n|\r/", $str);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = preg_split("/\t+|\s{2,}/", $line);
            if (count($parts) < 2) {
                $parts = preg_split("/\s+/", $line);
            }

            if (count($parts) < 2) continue;

            $emp = trim($parts[0]);
            $dateRaw = trim($parts[1]);

            if (!preg_match('/\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $dateRaw) && isset($parts[2])) {
                $dateRaw = $dateRaw . ' ' . trim($parts[2]);
            }

            $ts = strtotime($dateRaw);
            if ($ts === false) continue;

            $dt = date('Y-m-d H:i:s', $ts);

            $rows[] = [
                'employee_code' => $emp,
                'punch_at' => $dt,
                'raw_line' => $line,
            ];
        }

        return $rows;
    }

    protected function toCarbonOrDefault(?string $iso, Carbon $default): Carbon
    {
        if (!$iso) return $default;
        try {
            return Carbon::parse($iso);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
