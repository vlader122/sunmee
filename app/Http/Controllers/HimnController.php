<?php

namespace App\Http\Controllers;

use App\Models\Himn;
use Illuminate\Http\Request;

class HimnController extends Controller
{
    public function index() {
        return Himn::all();
    }

    public function show($id) {
        $himn = Himn::findOrFail($id);
        return $himn;
    }
}
