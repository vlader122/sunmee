<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index() {
        return Member::all();
    }

    public function show($id) {
        $himn = Member::findOrFail($id);
        return $himn;
    }
    public function buscar(Request $request)
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:100'
        ]);

        $terminoBusqueda = $request->query('nombre');

        $query = Member::query();

        if ($terminoBusqueda) {
            // ILIKE para PostgreSQL (case-insensitive)
            $query->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($terminoBusqueda) . '%']);
        }

        $miembros = $query->limit(5)->get();

        return response()->json($miembros);
    }
}
