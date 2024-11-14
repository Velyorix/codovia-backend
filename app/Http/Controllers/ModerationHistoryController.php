<?php

namespace App\Http\Controllers;

use App\Models\ModerationHistory;
use Illuminate\Http\Request;

class ModerationHistoryController extends Controller
{
    public function index(){
        $history = ModerationHistory::with('moderator', 'user')->get();
        return response()->json($history, 200);
    }
}
