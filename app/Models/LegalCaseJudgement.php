<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCaseJudgement extends Model
{
    use HasFactory;

    protected $casts = [
        'files_meta' => 'json',
    ];
}
