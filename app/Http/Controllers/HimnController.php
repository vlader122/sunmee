<?php

namespace App\Http\Controllers;

use App\Models\Himn;
use Illuminate\Http\Request;

class HimnController extends Controller
{
    public function index() {
        return Himn::all();
    }

    public function show($numero) {
        $himn = Himn::where('numero', $numero)->firstOrFail();
        return $himn;
    }
}
