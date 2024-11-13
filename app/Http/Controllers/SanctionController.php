<?php

namespace App\Http\Controllers;

use App\Events\UserSanctioned;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SanctionController extends Controller
{
    public function index(){
        return Sanction::with('user')->get();
    }

    public function store(Request $request, $userId){
        try {
            $user = User::findOrFail($userId);

            $data = $request->validate([
                'sanction_type' => 'required|in:ban,suspend',
                'reason' => 'nullable|string',
                'end_date' => 'nullable|date_format:Y-m-d H:i:s',
            ]);

            $sanction = $user->sanctions()->create($data);

            if ($data['sanction_type'] === 'ban') {
                $user->update(['status' => 'banned']);
            }

            event(new UserSanctioned($sanction));

            return response()->json($sanction, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }

    public function update(Request $request, Sanction $sanction){
        $data = $request->validate([
            'sanction_type' => 'required|in:ban,suspend',
            'reason' => 'nullable|string',
            'end_date' => 'nullable|date',
        ]);
        $sanction->update($data);
        $this->updateUserStatus($sanction->user);
        return response()->json($sanction, 200);
    }

    public function destroy(Sanction $sanction){
        $user = $sanction->user;
        $sanction->delete();
        $this->updateUserStatus($user);
        return response()->json(['message' => 'Sanction removed successfully.'], 204);
    }

    public function checkSanctionStatus($userId){
        try {
            $user = User::findOrFail($userId);
            $activeSanctions = $user->sanctions()->where('end_date', '>=', now())->orWhereNull('end_date')->exists();

            return response()->json(['is_sanctioned' => $activeSanctions]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }

    private function updateUserStatus(User $user){
        $hasActiveBan = $user->sanctions()
            ->where('sanction_type', 'ban')
            ->where(function ($query) {
                $query->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            })
            ->exists();

        $user->update(['status' => $hasActiveBan ? 'banned' : 'active']);
    }
}
