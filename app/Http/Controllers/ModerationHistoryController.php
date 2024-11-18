<?php

namespace App\Http\Controllers;

use App\Models\ModerationHistory;
use Illuminate\Http\Request;

class ModerationHistoryController extends Controller
{
    public function index(Request $request){
        $perPage = $request->input('perPage', 10);
        $history = ModerationHistory::with('moderator', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($history, 200);
    }

    public function exportHistory(){
        $history = ModerationHistory::with('moderator', 'user')->get();

        $csv = "Moderator,User,Action,Details,Date\n";

        foreach($history as $history){
            $csv .= "{$entry->moderator->name},{$entry->user->name},{$entry->action},{$entry->details},{$entry->created_at}\n";
        }

        return response($csv, 200, ['Content-Type', 'text/csv', 'Content-Disposition', 'attachment; filename="moderation_history.csv"']);
    }
}
