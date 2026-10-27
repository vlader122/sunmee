<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class ImportarMiembrosController extends Controller
{
    /**
     * Parsea el PDF y devuelve los miembros extraídos para previsualización.
     */
    public function parsear(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $parser = new Parser;
        $pdf = $parser->parseFile($request->file('pdf')->getPathname());
        $texto = $pdf->getText();

        $miembros = $this->extraerMiembros($texto);

        if (empty($miembros)) {
            return response()->json([
                'error' => 'No se pudieron extraer miembros del PDF. Verifica que sea el formato correcto.',
            ], 422);
        }

        // Marcar cuáles ya existen en la BD
        foreach ($miembros as &$m) {
            $m['existe'] = Member::whereRaw('LOWER(nombre_completo) = ?', [strtolower($m['nombre_completo'])])->exists();
        }

        return response()->json([
            'miembros' => $miembros,
            'total' => count($miembros),
            'nuevos' => count(array_filter($miembros, fn ($m) => ! $m['existe'])),
        ]);
    }

    /**
     * Importa los miembros confirmados al sistema.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'miembros' => 'required|array|min:1',
            'miembros.*.nombre_completo' => 'required|string|max:255',
            'miembros.*.edad' => 'nullable|integer|min:0|max:120',
            'miembros.*.telefono' => 'nullable|string|max:30',
        ]);

        $agregados = 0;
        $omitidos = 0;

        foreach ($request->miembros as $datos) {
            $existe = Member::whereRaw('LOWER(nombre_completo) = ?', [strtolower($datos['nombre_completo'])])->exists();

            if ($existe) {
                $omitidos++;
            } else {
                Member::create([
                    'nombre_completo' => $datos['nombre_completo'],
                    'edad' => $datos['edad'] ?? null,
                    'telefono' => $datos['telefono'] ?? null,
                ]);
                $agregados++;
            }
        }

        return response()->json([
            'agregados' => $agregados,
            'omitidos' => $omitidos,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Parser para el formato de la Iglesia SUD:
    // "Apellidos, Nombres\tV/M EdadTeléfono"
    // Ejemplo: "Mamani Quispe, Juan Carlos\tV 3572345678"
    // ─────────────────────────────────────────────────────────────

    private function extraerMiembros(string $texto): array
    {
        $miembros = [];
        $lineas = preg_split('/\r?\n/', $texto);

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (strlen($linea) < 5) {
                continue;
            }

            $miembro = $this->parsearLinea($linea);
            if ($miembro) {
                $miembros[] = $miembro;
            }
        }

        return $miembros;
    }

    /**
     * Formato real del PDF:
     *   "Apellido Apellido, Nombre Nombre\tV 32<teléfono>"
     *
     * El texto extraído une edad y teléfono sin espacio, por eso usamos
     * la posición después de los dígitos de edad para separar.
     */
    private function parsearLinea(string $linea): ?array
    {
        // Separar por tab: [nombre] [tab] [sexo edad telefono]
        $partes = explode("\t", $linea, 2);

        $nombreRaw = trim($partes[0]);
        $resto = isset($partes[1]) ? trim($partes[1]) : '';

        // El nombre debe contener una coma (Apellido, Nombre)
        if (! str_contains($nombreRaw, ',')) {
            return null;
        }

        // Descartar encabezados y pies de página
        if ($this->esEncabezado($nombreRaw)) {
            return null;
        }

        $nombre = $this->limpiarNombre($nombreRaw);
        if (strlen($nombre) < 8) {
            return null;
        }

        // Parsear "V 32<teléfono>" o "M 19" o "V 3272345678"
        $edad = null;
        $telefono = null;

        if ($resto) {
            // Patrón: V/M + 1-2 dígitos de edad + teléfono concatenado sin separador
            // Ej: "V 3262761246-75411455" → edad=32, tel=62761246-75411455
            if (preg_match('/^[VM]\s*(\d{1,2})([\d\+\-\s\.]+)?$/u', $resto, $m)) {
                $edadNum = (int) $m[1];
                if ($edadNum > 0 && $edadNum < 120) {
                    $edad = $edadNum;
                }
                $telRaw = trim($m[2] ?? '');
                $telefono = $this->limpiarTelefono($telRaw);
            }
        }

        return [
            'nombre_completo' => $nombre,
            'edad' => $edad,
            'telefono' => $telefono,
        ];
    }

    private function limpiarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s{2,}/', ' ', $nombre));
    }

    private function limpiarTelefono(string $tel): ?string
    {
        $tel = trim($tel);
        if (preg_match('/^[\s\-\.]+$/', $tel) || strlen($tel) < 5) {
            return null;
        }

        return $tel ?: null;
    }

    private function esEncabezado(string $texto): bool
    {
        $palabras = ['nombre', 'apellido', 'telefono', 'teléfono', 'sexo', 'edad',
            'lista de', 'barrio', 'estaca', 'iglesia', 'reservados',
            'personas seleccionadas', 'uso exclusivo'];
        $lower = strtolower($texto);
        foreach ($palabras as $p) {
            if (str_contains($lower, $p)) {
                return true;
            }
        }

        return false;
    }
}
