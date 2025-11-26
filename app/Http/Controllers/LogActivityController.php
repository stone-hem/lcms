<?php

namespace App\Http\Controllers;

use App\Helpers\LogActivity;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function addToLog($message)
    {
       LogActivity::addToLog($message);
    }
}
