<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cartas de Asignación</title>
    <style>
        @@page {
            size: letter;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11.5pt;
            line-height: 1.55;
            color: #000;
        }

        .pagina {
            page-break-after: always;
        }
        .pagina:last-child {
            page-break-after: auto;
        }

        .carta {
            padding: 72pt;
        }

        .encabezado {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 7px;
            margin-bottom: 14px;
        }
        .encabezado .iglesia {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #333;
            margin-bottom: 2px;
        }
        .encabezado .unidad {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .encabezado .estaca {
            font-size: 9pt;
            color: #444;
            margin-top: 1px;
        }

        .fecha-lugar {
            text-align: right;
            font-size: 10.5pt;
            margin-bottom: 14px;
        }

        .destinatario {
            margin-bottom: 10px;
        }
        .destinatario .nombre-linea {
            font-size: 11.5pt;
        }
        .destinatario .presente {
            font-size: 11.5pt;
        }

        .referencia {
            margin: 10px 0 14px;
            font-size: 11pt;
        }

        .cuerpo {
            text-align: justify;
        }
        .cuerpo p {
            margin-bottom: 9px;
            text-indent: 0.4in;
        }

        .dato {
            font-weight: bold;
        }

        .escritura {
            margin: 12px 0.4in;
            padding: 8px 12px;
            border-left: 3px solid #555;
        }
        .escritura-texto {
            display: block;
            font-style: italic;
            font-size: 10.5pt;
            color: #222;
            line-height: 1.6;
        }
        .escritura-ref {
            display: block;
            font-size: 9.5pt;
            color: #555;
            margin-top: 4px;
            text-align: right;
        }

        .pie {
            margin-top: 18px;
            text-align: center;
        }
        .pie .despedida {
            margin-bottom: 26px;
        }
        .pie .firma-nombre {
            font-weight: bold;
            font-size: 11pt;
        }
        .pie .firma-cargo {
            font-size: 10pt;
            color: #333;
        }

        @@media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@foreach($paginas as $carta)
<div class="pagina">
    <div class="carta">
        @include('cartas._carta-contenido', ['carta' => $carta])
    </div>
</div>
@endforeach
</body>
</html>
