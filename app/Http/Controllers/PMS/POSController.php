<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\POSService;

/**
 * @OA\Tag(name="POS", description="Point of Sale (Restaurant / Room Service)")
 */
class POSController extends Controller
{
    protected $posService;
    public function __construct(POSService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * @OA\Get(
     *   path="/api/pms/pos",
     *   tags={"POS"},
     *   summary="List all POS sales for a property",
     *   @OA\Parameter(name="property_code",in="query",required=true,@OA\Schema(type="string")),
     *   @OA\Response(response=200,description="OK")
     * )
     */
    public function index(Request $request)
    {
        $request->validate(['property_code' => 'required|string']);
        return $this->posService->index($request->property_code);
    }

    /**
     * @OA\Post(
     *   path="/api/pms/pos",
     *   tags={"POS"},
     *   summary="Create POS sale (with Pay Later guest verification)",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"items","payment_mode"},
     *       @OA\Property(property="reservation_id",type="integer"),
     *       @OA\Property(property="customer_name",type="string"),
     *       @OA\Property(property="customer_email",type="string"),
     *       @OA\Property(property="customer_phone",type="string"),
     *       @OA\Property(property="items",type="array",
     *           @OA\Items(
     *               @OA\Property(property="pos_item_id",type="integer"),
     *               @OA\Property(property="name",type="string"),
     *               @OA\Property(property="unit_price",type="number"),
     *               @OA\Property(property="quantity",type="integer")
     *           )
     *       ),
     *       @OA\Property(property="tax",type="number"),
     *       @OA\Property(property="discount",type="number"),
     *       @OA\Property(property="payment_mode",type="string",enum={"Pay Later","Cash","Card","UPI"})
     *     )
     *   ),
     *   @OA\Response(response=201,description="Sale created")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_name' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|in:Pay Later,Cash,Card,UPI'
        ]);
        return $this->posService->createSale($validated);
    }

    /**
     * @OA\Post(
     *   path="/api/pms/pos/{id}/payment",
     *   tags={"POS"},
     *   summary="Add payment to Pay Later sale",
     *   @OA\Parameter(name="id",in="path",required=true,@OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"amount","payment_mode"},
     *       @OA\Property(property="amount",type="number"),
     *       @OA\Property(property="payment_mode",type="string",enum={"Cash","Card","UPI"}),
     *       @OA\Property(property="txn_reference",type="string")
     *     )
     *   ),
     *   @OA\Response(response=201,description="Payment recorded")
     * )
     */
    public function addPayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:Cash,Card,UPI',
            'txn_reference' => 'nullable|string'
        ]);
        return $this->posService->addPaymentToSale($id, $validated['property_code'], $validated);
    }
}
