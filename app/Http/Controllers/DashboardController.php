<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Auction;
use App\Models\PurchasePayment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * الحصول على الإحصائيات العامة
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_properties' => Property::count(),
            'active_auctions' => Auction::where('status', 'active')->count(),
            'total_transactions' => PurchasePayment::count(),
            'total_revenue' => PurchasePayment::where('status', 'completed')->sum('amount'),
            'recent_activities' => $this->getRecentActivities()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * الحصول على إحصائيات العقارات
     */
    public function getPropertyStats(Request $request)
    {
        $query = Property::query();

        if ($request->period) {
            $query->where('created_at', '>=', $this->getStartDate($request->period));
        }

        $stats = [
            'total_properties' => $query->count(),
            'properties_by_type' => [
                'sale ' => $query->where('type', 'sale')->count(),
                'auction' => $query->where('type', 'auction')->count(),
                'rent ' => $query->where('type', 'rent')->count()
            ],
            'properties_by_status' => [
                'active' => $query->where('status', 'active')->count(),
                'sold' => $query->where('status', 'sold')->count(),
                'pending' => $query->where('status', 'pending')->count()
            ],
            'recent_properties' => Property::latest()->take(5)->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * الحصول على إحصائيات المزادات
     */
    public function getAuctionStats(Request $request)
    {
        $query = Auction::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total_auctions' => $query->count(),
            'active_auctions' => $query->where('status', 'active')->count(),
            'completed_auctions' => $query->where('status', 'completed')->count(),
            'total_bids' => $query->sum('total_bids'),
            'average_bid_amount' => $query->avg('current_price'),
            'auctions_by_status' => [
                'active' => $query->where('status', 'active')->count(),
                'completed' => $query->where('status', 'completed')->count(),
                'cancelled' => $query->where('status', 'cancelled')->count()
            ],
            'recent_auctions' => Auction::with('property')->latest()->take(5)->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * الحصول على إحصائيات المستخدمين
     */
    public function getUserStats(Request $request)
    {
        $query = User::query();

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total_users' => $query->count(),
            'active_users' => $query->where('is_active', '1')->count(),
            'users_by_role' => [
                'user' => $query->where('role', 'user')->count(),
                // 'sellers' => $query->where('role', 'seller')->count(),
                'admins' => $query->where('role', 'admin')->count()
            ],
            'new_users_today' => $query->whereDate('created_at', Carbon::today())->count(),
            'new_users_this_week' => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'recent_users' => User::latest()->take(5)->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * الحصول على إحصائيات المدفوعات
     */
    public function getPaymentStats(Request $request)
    {
        $query = PurchasePayment::query();

        if ($request->period) {
            $query->where('created_at', '>=', $this->getStartDate($request->period));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->where('status', 'completed')->sum('amount'),
            'transactions_by_status' => [
                'completed' => $query->where('status', 'completed')->count(),
                'pending' => $query->where('status', 'pending')->count(),
                'cancelled' => $query->where('status', 'cancelled')->count()
            ],
            'revenue_by_period' => $this->getRevenueByPeriod($request->period),
            'recent_transactions' => PurchasePayment::latest()->take(5)->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * الحصول على الأنشطة الأخيرة
     */
    private function getRecentActivities()
    {
        // يمكن تعديل هذه الدالة لتجميع الأنشطة من مختلف النماذج
        return [];
    }

    /**
     * الحصول على الإيرادات حسب الفترة
     */
    private function getRevenueByPeriod($period)
    {
        // يمكن تعديل هذه الدالة لتجميع الإيرادات حسب الفترة المطلوبة
        return [];
    }

    /**
     * الحصول على تاريخ البداية حسب الفترة
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'daily':
                return Carbon::today();
            case 'weekly':
                return Carbon::now()->startOfWeek();
            case 'monthly':
                return Carbon::now()->startOfMonth();
            case 'yearly':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }
}