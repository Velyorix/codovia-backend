<?php

namespace App\Http\Controllers;

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
        if (!auth()->user()->can('manage tags')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully'], 204);
    }
}
