<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasSlug;

    protected $fillable = [
        'name',
        'description',
        'auto_approval',
        'cover_path',
        'thumbnail_path',
        'user_id',
    ];

    public function currentUserGroup(): HasOne
    {
        return $this->hasOne(GroupUser::class)->where('user_id', '=', Auth::id());
    }

    public function isAdmin($userId): bool
    {
        return $this->currentUserGroup?->user_id === $userId;
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
}
