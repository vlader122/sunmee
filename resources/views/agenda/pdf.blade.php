<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda Sacramental</title>
    <style>
        @page {
            margin: 15px 56px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 5px 0;
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-content {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        .header-asistencia {
            display: table-cell;
            width: 90px;
            vertical-align: middle;
            text-align: center;
        }
        .asistencia-box {
            border: 2px solid #333;
            padding: 5px;
            text-align: center;
        }
        .asistencia-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .asistencia-value {
            border-bottom: 1px solid #333;
            height: 20px;
            margin: 0 5px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 4px;
        }
        .header .fecha {
            font-size: 14px;
            color: #444;
        }
        .header .tipo-reunion {
            font-size: 11px;
            background: #e8e8e8;
            display: inline-block;
            padding: 3px 12px;
            border-radius: 3px;
            margin-top: 5px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 10px;
            padding-top: 4px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e0e0e0;
            padding: 5px 8px;
            margin-bottom: 6px;
            border-left: 3px solid #333;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }
        .col-2 {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
        }
        .label {
            color: #555;
            font-size: 10px;
        }
        .value {
            font-weight: bold;
        }
        .bienvenida-lines {
            margin-top: 4px;
        }
        .line {
            border-bottom: 1px solid #888;
            height: 20px;
            margin-bottom: 2px;
        }
        .line-small {
            border-bottom: 1px solid #aaa;
            height: 16px;
            margin-bottom: 1px;
        }
        .santa-cena {
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            border: 1px solid #ccc;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 2px;
            margin: 8px 0;
        }
        .asuntos-estaca {
            display: table;
            width: 100%;
            border: 1px solid #999;
            margin-bottom: 8px;
        }
        .asuntos-estaca-label {
            display: table-cell;
            width: 70%;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
            vertical-align: middle;
        }
        .asuntos-estaca-options {
            display: table-cell;
            width: 30%;
            text-align: center;
            vertical-align: middle;
            padding: 5px;
        }
        .checkbox-group {
            display: inline-block;
            margin: 0 8px;
        }
        .checkbox {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #333;
            margin-right: 3px;
            vertical-align: middle;
        }
        .checkbox-label {
            font-size: 11px;
            vertical-align: middle;
        }
        .asuntos-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .asuntos-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 0 8px;
            border-right: 1px solid #ddd;
        }
        .asuntos-col:first-child {
            padding-left: 0;
        }
        .asuntos-col:last-child {
            padding-right: 0;
            border-right: none;
        }
        .asuntos-col-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #444;
            border-bottom: 1px solid #999;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .asuntos-col ul {
            list-style: none;
            font-size: 10px;
        }
        .asuntos-col li {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }
        .asuntos-empty-line {
            border-bottom: 1px solid #aaa;
            height: 18px;
            margin-bottom: 3px;
        }
        .anuncios-box {
            border: 1px solid #999;
            padding: 8px;
            min-height: 50px;
            background: #fafafa;
            font-size: 10px;
        }
        .testimonios-box {
            border: 1px solid #999;
            padding: 8px;
            min-height: 70px;
            background: #fffde7;
        }
        .testimonios-columns {
            column-count: 3;
            column-gap: 15px;
            column-rule: 1px solid #ddd;
        }
        .testimonio-item {
            break-inside: avoid;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .discurso-item {
            padding: 4px 0;
            border-bottom: 1px dotted #ccc;
        }
        .discurso-nombre {
            font-weight: bold;
        }
        .discurso-tema {
            font-size: 10px;
            color: #555;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>REUNION SACRAMENTAL</h1>
                <h3>BARRIO TERMINAL</H3>
                <div class="fecha">
                    @php
                        $fechaFormateada = \Carbon\Carbon::parse($agenda->fecha)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
                    @endphp
                    {{ ucfirst($fechaFormateada) }}
                </div>
                @if($agenda->tipo_agenda_id == 1)
                    <div class="tipo-reunion">Reunion de Ayuno y Testimonios</div>
                @elseif($agenda->tipo_agenda_id == 2)
                    <div class="tipo-reunion">Conferencia de Estaca</div>
                @elseif($agenda->tipo_agenda_id == 3)
                    <div class="tipo-reunion">Reunion Especial</div>
                @endif
            </div>
        </div>

        <!-- Presidencia -->
        <div class="section">
            <div class="section-title">Presidencia y Direccion</div>
            <div class="row">
                <div class="col-2">
                    <span class="label">Preside:</span>
                    <span class="value">{{ $agenda->presidido->nombre_completo ?? '________________________________' }}</span>
                </div>
                <div class="col-2">
                    <span class="label">Dirige:</span>
                    <span class="value">{{ $agenda->dirigido->nombre_completo ?? '________________________________' }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <span class="label">Director Musical:</span>
                    <span class="value">{{ $agenda->directorHimno->nombre_completo ?? '________________________________' }}</span>
                </div>
                <div class="col-2">
                    <span class="label">Pianista:</span>
                    <span class="value">{{ $agenda->pianista->nombre_completo ?? '________________________________' }}</span>
                </div>
            </div>
        </div>

        <!-- Bienvenida con lineas -->
        <div class="section">
            <div class="section-title">Bienvenida</div>
            <div class="bienvenida-lines">
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </div>

        <!-- Anuncios -->
        <div class="section">
            <div class="section-title">Anuncios</div>
            <div class="anuncios-box">
                @if($agenda->anuncios)
                    {!! nl2br(e($agenda->anuncios)) !!}
                @else
                    <div class="line-small"></div>
                    <div class="line-small"></div>
                    <div class="line-small"></div>
                    <div class="line-small"></div>
                @endif
            </div>
        </div>

        <!-- Himno de Apertura y Primera Oracion -->
        <div class="section">
            @php
                $himnoApertura = $agenda->himnos->firstWhere('tipo', 'apertura');
                $oracionPrimera = $agenda->oraciones->firstWhere('tipo', 'primera');
            @endphp
            <div class="row">
                <div class="col-2">
                    <span class="label">Himno de Apertura:</span>
                    @if($himnoApertura)
                        <span class="value">#{{ $himnoApertura->numero }}</span> {{ $himnoApertura->titulo }}
                    @else
                        <span class="value">_______________________________</span>
                    @endif
                </div>
                <div class="col-2">
                    <span class="label">Primera Oracion:</span>
                    <span class="value">{{ $oracionPrimera->miembro->nombre_completo ?? '________________________________' }}</span>
                </div>
            </div>
        </div>

        <!-- Asuntos del Barrio -->
        <div class="section">
            <div class="section-title">Asuntos del Barrio</div>
            @php
                $relevos = $agenda->asuntos->where('tipo_asunto', 'relevos');
                $sostenimientos = $agenda->asuntos->where('tipo_asunto', 'sostenimientos');
                $presentaciones = $agenda->asuntos->where('tipo_asunto', 'presentaciones');
            @endphp
            <div class="asuntos-grid">
                <div class="asuntos-col">
                    <div class="asuntos-col-title">Relevos</div>
                    <ul>
                        @forelse($relevos as $asunto)
                            <li>{{ $asunto->descripcion }}</li>
                        @empty
                        @endforelse
                        {{-- Agregar lineas en blanco --}}
                        @for($i = $relevos->count(); $i < 5; $i++)
                            <li class="asuntos-empty-line"></li>
                        @endfor
                    </ul>
                </div>
                <div class="asuntos-col">
                    <div class="asuntos-col-title">Sostenimientos</div>
                    <ul>
                        @forelse($sostenimientos as $asunto)
                            <li>{{ $asunto->descripcion }}</li>
                        @empty
                        @endforelse
                        {{-- Agregar lineas en blanco --}}
                        @for($i = $sostenimientos->count(); $i < 5; $i++)
                            <li class="asuntos-empty-line"></li>
                        @endfor
                    </ul>
                </div>
                <div class="asuntos-col">
                    <div class="asuntos-col-title">Presentaciones</div>
                    <ul>
                        @forelse($presentaciones as $asunto)
                            <li>{{ $asunto->descripcion }}</li>
                        @empty
                        @endforelse
                        {{-- Agregar lineas en blanco --}}
                        @for($i = $presentaciones->count(); $i < 5; $i++)
                            <li class="asuntos-empty-line"></li>
                        @endfor
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Asuntos de Estaca -->
        <div class="section">
            <div class="asuntos-estaca">
                <div class="asuntos-estaca-label">ASUNTOS DE ESTACA</div>
                <div class="asuntos-estaca-options">
                    <span class="checkbox-group">
                        <span class="checkbox"></span>
                        <span class="checkbox-label">SI</span>
                    </span>
                    <span class="checkbox-group">
                        <span class="checkbox"></span>
                        <span class="checkbox-label">NO</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Himno Sacramental y Santa Cena -->
        <div class="section">
            @php
                $himnoSacramental = $agenda->himnos->firstWhere('tipo', 'sacramental');
            @endphp
            <div style="margin-bottom: 5px;">
                <span class="label">Himno Sacramental:</span>
                @if($himnoSacramental)
                    <span class="value">#{{ $himnoSacramental->numero }}</span> {{ $himnoSacramental->titulo }}
                @else
                    <span class="value">_______________________________</span>
                @endif
            </div>
            <div class="santa-cena">SANTA CENA</div>
        </div>

        <!-- Discursos o Testimonios -->
        @if($agenda->tipo_agenda_id == 1)
            <!-- Testimonios (Reunion de Ayuno) -->
            <div class="section">
                <div class="section-title">Testimonios</div>
                <div class="testimonios-box">
                    @php
                        $testimoniosList = $agenda->testimonios ? array_filter(array_map('trim', explode("\n", $agenda->testimonios))) : [];
                        $testimoniosCount = count($testimoniosList);
                    @endphp
                    @if($testimoniosCount > 0)
                        <div class="testimonios-columns">
                            @foreach($testimoniosList as $index => $testimonio)
                                <div class="testimonio-item">{{ $index + 1 }}. {{ $testimonio }}</div>
                            @endforeach
                        </div>
                    @endif
                    {{-- Agregar lineas en blanco si hay pocos testimonios --}}
                    @if($testimoniosCount < 6)
                        @for($i = $testimoniosCount; $i < 6; $i++)
                            <div class="asuntos-empty-line"></div>
                        @endfor
                    @endif
                </div>
            </div>
        @else
            <!-- Himno Intermedio y Discursos (Reunion Regular) -->
            @php
                $himnoIntermedio = $agenda->himnos->firstWhere('tipo', 'intermedio');
            @endphp
            @if($himnoIntermedio)
            <div class="section">
                <span class="label">Himno Intermedio:</span>
                <span class="value">#{{ $himnoIntermedio->numero }}</span> {{ $himnoIntermedio->titulo }}
            </div>
            @endif

            <div class="section">
                <div class="section-title">Discursos</div>
                @forelse($agenda->mensajes as $index => $mensaje)
                    <div class="discurso-item">
                        <span style="color: #666; font-size: 10px;">{{ $index + 1 }}.</span>
                        <span class="discurso-nombre">
                            {{ $mensaje->discursante->nombre_completo ?? $mensaje->discursante_nombre ?? '____________________' }}
                            @if($mensaje->discursante_nombre && !$mensaje->discursante_id)
                                <span style="font-size: 8px; color: #888; font-weight: normal;">(Invitado)</span>
                            @endif
                        </span>
                        @if($mensaje->tema)
                            <span class="discurso-tema">- {{ $mensaje->tema }}</span>
                        @else
                            <span class="discurso-tema">- Tema: ____________________</span>
                        @endif
                    </div>
                @empty
                    {{-- Lineas vacias para discursos --}}
                    @for($i = 0; $i < 3; $i++)
                        <div class="discurso-item">
                            <span style="color: #666; font-size: 10px;">{{ $i + 1 }}.</span>
                            <span>____________________ - Tema: ____________________</span>
                        </div>
                    @endfor
                @endforelse
                {{-- Agregar lineas adicionales si hay menos de 3 discursos --}}
                @if($agenda->mensajes->count() > 0 && $agenda->mensajes->count() < 3)
                    @for($i = $agenda->mensajes->count(); $i < 3; $i++)
                        <div class="discurso-item">
                            <span style="color: #666; font-size: 10px;">{{ $i + 1 }}.</span>
                            <span>____________________ - Tema: ____________________</span>
                        </div>
                    @endfor
                @endif
            </div>
        @endif

        <!-- Himno de Cierre y Ultima Oracion -->
        <div class="section">
            @php
                $himnoCierre = $agenda->himnos->firstWhere('tipo', 'cierre');
                $oracionUltima = $agenda->oraciones->firstWhere('tipo', 'ultima');
            @endphp
            <div class="row">
                <div class="col-2">
                    <span class="label">Himno de Cierre:</span>
                    @if($himnoCierre)
                        <span class="value">#{{ $himnoCierre->numero }}</span> {{ $himnoCierre->titulo }}
                    @else
                        <span class="value">_______________________________</span>
                    @endif
                </div>
                <div class="col-2">
                    <span class="label">Ultima Oracion:</span>
                    <span class="value">{{ $oracionUltima->miembro->nombre_completo ?? '________________________________' }}</span>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
