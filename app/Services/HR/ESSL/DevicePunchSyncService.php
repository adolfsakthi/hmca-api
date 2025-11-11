<?php

namespace App\Services\HR\ESSL;

use App\Models\HR\ESSL\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DevicePunchSyncService
{
    public static function fetchFromSoap($device): string
    {


        $url = "http://{$device->ip_address}:{$device->port}/WebAPIService.asmx";
        $from = $device->last_sync_at
            ? Carbon::parse($device->last_sync_at)
            : now()->startOfDay();
        $to = now()->endOfDay()->format('Y-m-d\T23:59:59');

        $body = '<?xml version="1.0" encoding="utf-8"?>' .
            "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">" .
            '<soap:Body>' .
            '<GetTransactionsLog xmlns="http://tempuri.org/">' .
            "<FromDate>{$from}</FromDate>" .
            "<ToDate>{$to}</ToDate>" .
            "<SerialNumber>{$device->serial_number}</SerialNumber>" .
            "<UserName>{$device->username}</UserName>" .
            "<UserPassword>{$device->password}</UserPassword>" .
            '<strDataList></strDataList>' .
            '</GetTransactionsLog>' .
            '</soap:Body>' .
            '</soap:Envelope>';

        // $response = Http::withHeaders([
        //     'Content-Type' => 'text/xml; charset=utf-8',
        //     'SOAPAction' => 'http://tempuri.org/GetTransactionsLog',
        // ])->timeout(60)->post($url, $body);

        $client = new Client(['timeout' => 20, 'connect_timeout' => 5]);
        $headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://tempuri.org/GetTransactionsLog'
        ];

        $response = $client->post($url, ['body' => $body, 'headers' => $headers]);

        return $response->getbody();
    }

    public static function parseSoapLogs(string $xml): array
    {
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xmlObj->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xmlObj->registerXPathNamespace('t', 'http://tempuri.org/');

        $node = $xmlObj->xpath('//t:GetTransactionsLogResponse/t:strDataList')[0] ?? null;
        if (!$node) return [];

        $raw = trim(preg_replace('/\s+/', ' ', (string)$node));
        $pattern = '/([A-Z0-9]+|\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/';
        preg_match_all($pattern, $raw, $matches, PREG_SET_ORDER);

        return collect($matches)->map(fn($m) => [
            'employee_code' => trim($m[1]),
            'punch_at' => Carbon::parse($m[2]),
        ])->sortBy('punch_at')->values()->toArray();
    }

    public static function sync($device, array $logs): array
    {
        $propertyCode = $device->property_code ?? 'DEFAULT';
        $lastPunch = Transaction::where('device_id', $device->id)
            ->where('property_code', $propertyCode)
            ->orderByDesc('punch_at')
            ->value('punch_at');
            
        $lastPunchTime = $lastPunch ? Carbon::parse($lastPunch) : null;

        // 🧩 Filter logs newer than last punch
        $newLogs = collect($logs)->filter(
            fn($log) =>
            !$lastPunchTime || $log['punch_at']->greaterThan($lastPunchTime)
        );

        // 🧩 Prepare insert data
        $insertData = $newLogs->map(fn($log) => [
            'device_id' => $device->id,
            'property_code' => $propertyCode,
            'employee_code' => $log['employee_code'],
            'punch_at' => $log['punch_at'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->toArray();

        if (!empty($insertData)) {
            Transaction::insert($insertData);
        }

        // Update device last sync
        $device->last_sync_at = $newLogs->isNotEmpty()
            ? $newLogs->max('punch_at')
            : now();
        $device->status = "online";
        $device->save();

        $summary = [
            'property_code' => $propertyCode,
            'device_id' => $device->id,
            'previous_punch' => optional($lastPunchTime)->toDateTimeString(),
            'inserted' => count($insertData),
            'total_from_soap' => count($logs),
            'latest_punch' => $device->last_sync_at->toDateTimeString(),
        ];

        Log::info('Device sync summary', $summary);

        return $summary;
    }
}
