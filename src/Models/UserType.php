<?php

namespace Eightfold\Registered\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Eightfold\Registered\Models\UserRegistration;

class UserType extends Model
{
    protected $casts = [
        'can_delete' => 'boolean'
    ];

    protected $fillable = [
        'slug', 'display'
    ];

    static public function selectOptions()
    {
        return UserType::all()->pluck('display', 'slug');
    }

    static public function userTypesForRoutes()
    {
        if (Schema::hasTable('user_types')) {
            $types = DB::table('user_types')->get();
            $typeReturn = [];
            foreach ($types as $type) {
                $typeReturn[] = [
                    'slug' => $type->slug,
                    'display' => $type->display
                ];
            }
            return $typeReturn;
        }
        return [];
    }

    public function registrations()
    {
        return $this->belongsToMany(UserRegistration::class);
    }

    /** Scopes */
    public function scopeWithSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

}
