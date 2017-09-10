<?php

namespace Eightfold\RegisteredLaravel\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Builder;

use Eightfold\RegisteredLaravel\Traits\RegisteredUser;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Schema;

class UserType extends Authenticatable
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

    /** Scopes */
    public function scopeWithSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

}
