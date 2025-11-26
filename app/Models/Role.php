<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * Permissions assigned to this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Users who belong to this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}