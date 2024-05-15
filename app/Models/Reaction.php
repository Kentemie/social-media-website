<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    use HasFactory;

    CONST UPDATED_AT = null;

    protected $fillable = [ 'type', 'user_id', 'object_id', 'object_type'];

    /**
     * Get the parent object model (post or comment)
     */
    public function object(): MorphTo
    {
        return $this->morphTo();
    }
}
