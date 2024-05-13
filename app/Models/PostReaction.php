<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReaction extends Model
{
    use HasFactory;

    CONST UPDATED_AT = null;

    protected $fillable = [ 'type', 'user_id', 'post_id'];
}
