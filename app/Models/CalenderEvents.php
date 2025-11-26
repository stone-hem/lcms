<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalenderEvents extends Model
{
    use HasFactory;

    protected $casts = [
        "attachments" => "json",
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipants::class, 'calender_event_id', 'id');
    }
}
