<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuctionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuctionController extends Controller
{
    protected $auctionService;

    public function __construct(AuctionService $auctionService)
    {
        $this->auctionService = $auctionService;
    }

    /**
     * عرض قائمة المزادات النشطة
     */
    public function index(Request $request)
    {
        try {
            $auctions = $this->auctionService->getActiveAuctions($request);
            return response()->json(['status' => 'success', 'data' => $auctions]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * عرض المزادات الخاصة بالمستخدم
     */
    public function userAuctions(Request $request)
    {
        try {
            $auctions = $this->auctionService->getUserAuctions($request);
            return response()->json(['status' => 'success', 'data' => $auctions]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * عرض تفاصيل مزاد محدد
     */
    public function show($id, Request $request)
    {
        try {
            $auction = $this->auctionService->getAuctionDetails($id, $request);
            return response()->json(['status' => 'success', 'data' => $auction]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * إنشاء مزاد جديد
     */
    public function store(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_price' => 'required|numeric|min:0',
            'min_increment' => 'required|numeric|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $auction = $this->auctionService->createAuction($request, $propertyId);
            return response()->json(['status' => 'success', 'data' => $auction], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تحديث حالة المزاد
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:draft,active,ended,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $auction = $this->auctionService->updateAuctionStatus($request, $id);
            return response()->json(['status' => 'success', 'data' => $auction]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * تقديم عرض في المزاد
     */
    public function placeBid(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $bid = $this->auctionService->placeBid($request, $id);
            return response()->json(['status' => 'success', 'data' => $bid], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}