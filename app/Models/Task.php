<?php

namespace App\Models;

use App\Models\LegalCase\LegalCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $casts = [
        "tags" => "json",
        "faved_by"=>"json",
    ];

    public function assignees()
    {
        return $this->hasMany(TaskAssignees::class)->with(
            "user:users.id,first_name,middle_name,last_name,calling_code,email,phone"
        );
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachments::class)->with(
            "user:users.id,first_name,middle_name,last_name,calling_code,email,phone"
        );
    }

    public function legal_case()
    {
        return $this->belongsTo(LegalCase::class)->select(
            "id",
            "serial_number",
            "title",
            "court_name",
            "case_number",
            "year",
            "description"
        );
    }
}
