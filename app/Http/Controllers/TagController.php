<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(){
        return response()->json(Tag::all());
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('manage tags')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['name' => 'required|string|unique:tags,name']);
        $tag = Tag::create(['name' => $request->name]);
        return response()->json($tag, 201);
    }

    public function destroy(Tag $tag)
    {
        try {
            $exists = Article::whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Tag cannot be deleted as it is associated with one or more articles.'
                ], 400);
            }

            $tag->delete();

            return response()->json([
                'message' => 'Tag deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the tag.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        try {
            $tag->update($data);

            return response()->json([
                'message' => 'Tag updated successfully.',
                'tag' => $tag,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the tag.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
