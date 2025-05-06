<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionStatusLog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuctionService
{
    /**
     * إنشاء مزاد جديد
     */
    public function createAuction(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $user = $request->user();
        
        // التحقق من أن العقار متاح للمزاد
        if ($property->status !== 'active') {
            throw new \Exception('هذا العقار غير متاح للمزاد حاليًا', 422);
        }
        
        // التحقق من أن المستخدم هو مالك العقار أو مشرف
        if ($user->id !== $property->user_id && $user->role !== 'admin') {
            throw new \Exception('غير مصرح لك بإنشاء مزاد لهذا العقار', 403);
        }
        
        // التحقق من عدم وجود مزاد نشط للعقار
        $existingAuction = Auction::where('property_id', $propertyId)
            ->whereIn('status', ['draft', 'active'])
            ->first();
            
        if ($existingAuction) {
            throw new \Exception('يوجد مزاد قائم بالفعل لهذا العقار', 422);
        }
        
        DB::beginTransaction();
        
        try {
            // إنشاء المزاد
            $auction = new Auction([
                'property_id' => $propertyId,
                'title' => $request->title ?? $property->title,
                'description' => $request->description ?? $property->description,
                'start_price' => $request->start_price,
                'min_increment' => $request->min_increment,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'draft',
                'admin_notes' => $request->admin_notes,
            ]);
            
            $auction->save();
            
            // إنشاء سجل الحالة
            $statusLog = new AuctionStatusLog([
                'auction_id' => $auction->id,
                'status' => 'draft',
                'notes' => 'تم إنشاء المزاد',
                'changed_by' => $user->id,
            ]);
            
            $statusLog->save();
            
            // تحديث حالة العقار إلى معلق
            $property->status = 'pending';
            $property->save();
            
            DB::commit();
            
            return $auction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * تحديث حالة المزاد
     */
    public function updateAuctionStatus(Request $request, $auctionId)
    {
        $user = $request->user();
        $auction = Auction::findOrFail($auctionId);
        
        // التحقق من الصلاحيات
        if ($user->role !== 'admin' && $user->id !== $auction->property->user_id) {
            throw new \Exception('غير مصرح لك بتحديث هذا المزاد', 403);
        }
        
        // التحقق من أن الحالة الجديدة صالحة
        $validStatuses = ['draft', 'active', 'ended', 'cancelled'];
        if (!in_array($request->status, $validStatuses)) {
            throw new \Exception('حالة غير صالحة', 422);
        }
        
        DB::beginTransaction();
        
        try {
            // تحديث حالة المزاد
            $oldStatus = $auction->status;
            $auction->status = $request->status;
            
            if ($request->has('admin_notes')) {
                $auction->admin_notes = $request->admin_notes;
            }
            
            // إذا تم تنشيط المزاد، نتأكد من أن تاريخ البدء والانتهاء صالحين
            if ($request->status === 'active') {
                if (now()->gt($auction->end_date)) {
                    throw new \Exception('تاريخ انتهاء المزاد غير صالح', 422);
                }
            }
            
            // إذا تم إنهاء المزاد، نحدد الفائز
            if ($request->status === 'ended' && $oldStatus === 'active') {
                $highestBid = $auction->highestBid();
                
                if ($highestBid) {
                    $auction->winner_id = $highestBid->user_id;
                    $auction->winning_bid_amount = $highestBid->amount;
                    
                    // تحديث حالة العرض الفائز
                    $highestBid->status = 'winning';
                    $highestBid->save();
                }
            }
            
            $auction->save();
            
            // إنشاء سجل الحالة
            $statusLog = new AuctionStatusLog([
                'auction_id' => $auction->id,
                'status' => $request->status,
                'notes' => $request->notes ?? 'تم تحديث حالة المزاد من ' . $oldStatus . ' إلى ' . $request->status,
                'changed_by' => $user->id,
            ]);
            
            $statusLog->save();
            
            // تحديث حالة العقار إذا لزم الأمر
            $property = $auction->property;
            
            if ($request->status === 'active') {
                $property->status = 'in_auction';
            } else if ($request->status === 'ended') {
                if ($auction->winner_id) {
                    $property->status = 'sold';
                } else {
                    $property->status = 'active';
                }
            } else if ($request->status === 'cancelled') {
                $property->status = 'active';
            }
            
            $property->save();
            
            DB::commit();
            
            return $auction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * تقديم عرض في المزاد
     */
    public function placeBid(Request $request, $auctionId)
    {
        $user = $request->user();
        $auction = Auction::findOrFail($auctionId);
        
        // التحقق من أن المزاد نشط
        if (!$auction->isActive()) {
            throw new \Exception('المزاد غير نشط حاليًا', 422);
        }
        
        // التحقق من أن المستخدم ليس هو مالك العقار
        if ($user->id === $auction->property->user_id) {
            throw new \Exception('لا يمكنك تقديم عرض على مزاد لعقار تملكه', 422);
        }
        
        // التحقق من قيمة العرض
        $highestBid = $auction->highestBid();
        $minBidAmount = $highestBid ? $highestBid->amount + $auction->min_increment : $auction->start_price;
        
        if ($request->amount < $minBidAmount) {
            throw new \Exception('يجب أن يكون العرض أكبر من ' . $minBidAmount, 422);
        }
        
        DB::beginTransaction();
        
        try {
            // تحديث حالة العروض السابقة للمستخدم
            if ($highestBid) {
                AuctionBid::where('auction_id', $auctionId)
                    ->where('status', 'active')
                    ->update(['status' => 'outbid']);
            }
            
            // إنشاء العرض الجديد
            $bid = new AuctionBid([
                'auction_id' => $auctionId,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'active',
                'notes' => $request->notes,
            ]);
            
            $bid->save();
            
            // إنشاء سجل الحالة
            $statusLog = new AuctionStatusLog([
                'auction_id' => $auctionId,
                'status' => $auction->status,
                'notes' => 'تم تقديم عرض جديد بقيمة ' . $request->amount . ' من المستخدم ' . $user->name,
                'changed_by' => $user->id,
            ]);
            
            $statusLog->save();
            
            DB::commit();
            
            return $bid;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * الحصول على المزادات النشطة
     */
    public function getActiveAuctions(Request $request)
    {
        return Auction::with(['property', 'property.images', 'bids'])
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('end_date', 'asc')
            ->paginate(10);
    }
    
    /**
     * الحصول على تفاصيل المزاد
     */
    public function getAuctionDetails($auctionId, Request $request)
    {
        $auction = Auction::with(['property', 'property.images', 'bids', 'bids.user', 'statusLogs.changedByUser'])
            ->findOrFail($auctionId);
        
        // إضافة معلومات إضافية
        $auction->highest_bid = $auction->highestBid();
        $auction->total_bids = $auction->bids->count();
        $auction->is_ended = $auction->isEnded();
        
        // إذا كان المستخدم مسجل، نضيف معلومات عن عروضه
        if ($request->user()) {
            $auction->user_bids = $auction->bids->where('user_id', $request->user()->id);
            $auction->user_highest_bid = $auction->bids->where('user_id', $request->user()->id)->sortByDesc('amount')->first();
        }
        
        return $auction;
    }
    
    /**
     * الحصول على مزادات المستخدم
     */
    public function getUserAuctions(Request $request)
    {
        $user = $request->user();
        
        // البحث عن المزادات حسب دور المستخدم
        if ($user->role === 'admin') {
            // المشرف يرى جميع المزادات
            return Auction::with(['property', 'bids'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // المستخدم العادي يرى مزاداته كمالك أو كمشارك
            $propertyIds = Property::where('user_id', $user->id)->pluck('id');
            
            return Auction::with(['property', 'bids'])
                ->where(function ($query) use ($user, $propertyIds) {
                    $query->whereIn('property_id', $propertyIds)
                        ->orWhereHas('bids', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
    }
}