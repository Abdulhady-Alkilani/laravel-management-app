<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // <== هذا هو الأهم

class Controller extends BaseController // <== ويرث من BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}