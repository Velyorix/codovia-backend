<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request){
        $notification = $request->user()->notifications()->where('is_read', false)->get();
        return response()->json($notification);
    }

    public function markAsRead(Notification $notification){
        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function destroy(Notification $notification){
        $notification->delete();
        return response()->json(['message' => 'Notifications deleted.']);
    }
}
