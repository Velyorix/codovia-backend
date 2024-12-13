<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function getPublicProfile($username)
    {
        $user = User::where('name', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        $articles = Article::where('user_id', $user->id)
            ->where('status', 'public')
            ->orderBy('created_at', 'desc')
            ->select(['id', 'title', 'content', 'created_at'])
            ->get();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'bio' => $user->bio,
                'role' => $user->role,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at->format('d M Y'),
            ],
            'articles' => $articles,
        ]);
    }
    public function getPrivateProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $articles = Article::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->select(['id', 'title', 'content', 'status', 'created_at'])
            ->get();

        $favorites = $user->favoriteArticles()
            ->select('articles.id as article_id', 'articles.title', 'articles.created_at')
            ->get();

        $ratings = $user->ratings()
            ->with('article:id,title')
            ->get();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at->format('d M Y'),
            ],
            'articles' => $articles,
            'favorites' => $favorites,
            'ratings' => $ratings,
        ]);
    }


    public function updatePrivateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'avatar_url' => 'nullable|string|url',
            'current_password' => 'nullable|string|min:8',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['current_password']) && !Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Le mot de passe actuel est incorrect.'], 400);
        }

        if (!empty($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
        }

        unset($data['current_password'], $data['new_password']);

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->only(['name', 'email', 'bio', 'avatar_url']),
        ]);
    }
}
