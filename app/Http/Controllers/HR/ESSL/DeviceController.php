<?php

namespace App\Http\Controllers\HR\ESSL;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\ESSL\StoreDeviceRequest;
use App\Http\Requests\HR\ESSL\UpdateDeviceRequest;
use App\Services\HR\ESSL\DeviceService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *   name="HRMS - ESSL Devices",
 *   description="Manage biometric (ESSL) devices, test connectivity, and sync logs for HRMS."
 * )
 */
class DeviceController extends Controller
{
    protected DeviceService $service;

    public function __construct(DeviceService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/devices",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="List all ESSL devices for a property",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="property_code",
     *     in="query",
     *     required=true,
     *     description="Current property code",
     *     @OA\Schema(type="string", example="PROP002")
     *   ),
     *   @OA\Response(response=200, description="List of devices returned")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $devices = $this->service->list($propertyCode);
        return response()->json(['success' => true, 'data' => $devices]);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/devices",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Add a new ESSL device",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="property_code",
     *     in="query",
     *     required=true,
     *     description="Current property code",
     *     @OA\Schema(type="string", example="PROP002")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"device_name","ip_address"},
     *       @OA\Property(property="device_name", type="string", example="Main Gate Device"),
     *       @OA\Property(property="serial_number", type="string", example="JJA1250300597"),
     *       @OA\Property(property="ip_address", type="string", example="103.159.10.130"),
     *       @OA\Property(property="port", type="integer", example=81),
     *       @OA\Property(property="username", type="string", example="Edureka"),
     *       @OA\Property(property="password", type="string", example="Edureka@123"),
     *       @OA\Property(property="location", type="string", example="Reception Gate")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Device created successfully"),
     *   @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(StoreDeviceRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();
        $dev = $this->service->create($propertyCode, $payload);
        return response()->json(['success' => true, 'data' => $dev], 201);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/devices/{id}",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Get specific ESSL device details",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, description="Device ID", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="Device details returned"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function show(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $dev = $this->service->get($propertyCode, $id);
        if (!$dev) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        return response()->json(['success' => true, 'data' => $dev]);
    }

    /**
     * @OA\Put(
     *   path="/api/hrms/devices/{id}",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Update ESSL device details",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, description="Device ID", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       @OA\Property(property="device_name", type="string", example="Back Gate Device"),
     *       @OA\Property(property="ip_address", type="string", example="103.159.10.131"),
     *       @OA\Property(property="port", type="integer", example=8080),
     *       @OA\Property(property="username", type="string", example="Edureka"),
     *       @OA\Property(property="password", type="string", example="Edureka@123")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Device updated successfully"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function update(UpdateDeviceRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $dev = $this->service->update($propertyCode, $id, $request->validated());
        if (!$dev) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        return response()->json(['success' => true, 'data' => $dev]);
    }

    /**
     * @OA\Delete(
     *   path="/api/hrms/devices/{id}",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Delete a device from HRMS",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="Device deleted"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        return response()->json(['success' => true, 'message' => 'Device deleted']);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/devices/{id}/ping",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Ping the device to test connectivity",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="Ping result with status online/offline"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function ping(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $dev = $this->service->get($propertyCode, $id);
        if (!$dev) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        $res = $this->service->pingDevice($dev);
        return response()->json(['success' => $res['success'], 'data' => $res]);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/devices/{id}/sync",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Sync logs from a specific ESSL device (SOAP API call)",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="Device logs synced successfully"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function sync(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $dev = $this->service->get($propertyCode, $id);
        if (!$dev) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        $res = $this->service->syncDevice($dev);
        return $res;
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/devices/sync-all",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Sync all ESSL devices under the current property",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="All devices synced successfully")
     * )
     */
    public function syncAll(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $res = $this->service->syncAllForProperty($propertyCode);
        return response()->json(['success' => true, 'data' => $res]);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/devices/{id}/logs",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="Fetch synced punch logs for a specific device",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string", example="PROP002")),
     *   @OA\Response(response=200, description="Paginated list of punch logs"),
     *   @OA\Response(response=404, description="Device not found")
     * )
     */
    public function logs(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $dev = $this->service->get($propertyCode, $id);
        if (!$dev) return response()->json(['success' => false, 'message' => 'Device not found'], 404);
        $logs = $dev->logs()->orderBy('log_datetime', 'desc')->paginate(50);
        return response()->json(['success' => true, 'data' => $logs]);
    }


    /**
     * @OA\Get(
     *   path="/api/hrms/devices/alllogs",
     *   tags={"HRMS - ESSL Devices"},
     *   summary="List all logs  for a pdevice",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="List of devices returned")
     * )
     */

    public function alllogs(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->service->getLogsByProperty($propertyCode);
    }
}
