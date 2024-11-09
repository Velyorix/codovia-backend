<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
