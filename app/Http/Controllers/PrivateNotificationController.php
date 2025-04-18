<?php

namespace App\Http\Controllers;

use App\Events\PrivateNotification;
use Illuminate\Http\Request;

class PrivateNotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $data = [
            'message' => $request->message,
            'time' => now()
        ];
        
        event(new PrivateNotification($data, $request->user()->id));
        
        return response()->json(['status' => 'Notification sent!']);
    }
}