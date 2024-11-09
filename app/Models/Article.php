<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category_id',
        'user_id',
        'version',
    ];

    // Relation avec la catégorie
    public function category()
    {
        return $this->belongsTo(Category::class);
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
}
