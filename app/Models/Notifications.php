<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;
    protected $casts = [
        "content" => "json",
    ];

    protected $fillable = [
        'user_id',
        'task_id',
        'event_id',
        'is_case_activity',
        'message',
        'activity_date',
        'notification_date',
        'email_sent_at',
        'read_at',
        'sms_sent_at',
        'type',
        'content',
        'thread_lock_id',
        'created_at',
        'updated_at',
        'subject'
    ];

    public function event()
    {
        return $this->belongsTo(CalenderEvents::class, 'event_id', 'id')->with("participants");
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id')->with("assignees", "attachments", "legal_case");
    }
}
