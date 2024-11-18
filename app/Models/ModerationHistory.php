<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationHistory extends Model
{
    use HasFactory;

    protected $fillable = ['moderator_id', 'user_id', 'action', 'details'];

    public function moderator(){
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
