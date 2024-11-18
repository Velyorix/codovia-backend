<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request){
        $notification = $request->user()->notifications()->where('is_read', false)->get();

        if ($notification->isEmpty()) {
            return response()->json(['message' => 'All notifications read.']);
        }

        return response()->json($notification);
    }

    public function markAsRead(Notification $notification){
        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function markAllAsRead(){
        $user = auth('api')->user();

        if (!$user){
            return response()->json(['message' => 'Not Authenticated.'], 401);
        }

        $user->notifications()->update(['is_read' => true]);

        return response()->json(['message' => 'All notifications marked as read.'], 200);
    }

    public function destroy(Notification $notification){
        $notification->delete();
        return response()->json(['message' => 'Notifications deleted.']);
    }
}
