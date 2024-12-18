<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;


class Article extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'content',
        'category_id',
        'user_id',
        'version',
        'status'
    ];

    // Relation avec la catégorie
    public function category()
    {
        return $this->belongsTo(Category::class)->with('parent');
    }

    // Relation avec l'utilisateur (auteur de l'article)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation pour l'historique des versions
    public function versions()
    {
        return $this->hasMany(ArticleVersion::class);
    }

    // Relation pour les commentaires
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Configure les champs indexés pour la recherche
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'category_id' => $this->category_id,
            'category' => $this->category?->name,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'tags' => $this->tags->pluck('name')->toArray(),
        ];
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'article_id', 'user_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class);
    }
}
