<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCaseNotes extends Model
{
    use HasFactory;
    protected $casts = [
        "attachments" => "json",
    ];
}
