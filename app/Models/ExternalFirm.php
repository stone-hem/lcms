<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalFirm extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firm_name',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'postal_address',
        'kra_pin',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bank_name' => 'string',
            'bank_branch' => 'string',
            'bank_account_number' => 'string',
            'postal_address' => 'string',
            'kra_pin' => 'string',
        ];
    }

    // Relationships

    /**
     * Get the user (counsel) that owns this firm.
     * withTrashed() allows fetching even if user is soft-deleted
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}