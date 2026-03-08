@php
    $fechaCarta   = \Carbon\Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $fechaReunion = isset($carta['fecha'])
        ? \Carbon\Carbon::parse($carta['fecha'])->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')
        : '_______________';
    $esDiscurso   = isset($carta['tipo']) && str_contains(strtolower($carta['tipo']), 'discurso');
    $ref          = $esDiscurso ? 'Asignacion de discurso' : 'Asignacion de oracion';
    $tema         = !empty($carta['tema']) ? $carta['tema'] : null;
    $tiempo       = !empty($carta['tiempo']) ? $carta['tiempo'] : null;

    $asignacionTexto = '';
    if ($esDiscurso) {
        $asignacionTexto = 'discursar en la Reunion Sacramental del dia <strong>' . $fechaReunion . '</strong>';
        if ($tema)   $asignacionTexto .= ', con el tema: <strong>' . e($tema) . '</strong>';
        if ($tiempo) $asignacionTexto .= ', con una duracion de <strong>' . e($tiempo) . '</strong>';
        $asignacionTexto .= '. Se requiere que este presente en el estrado con <strong>10 minutos de anticipacion</strong>.';
    } else {
        $asignacionTexto = 'ofrecer la <strong>' . e($carta['tipo']) . '</strong>'
            . ' en la Reunion Sacramental del dia <strong>' . $fechaReunion . '</strong>'
            . '. Se requiere que este presente con <strong>10 minutos de anticipacion</strong>.';
    }
@endphp

{{-- Encabezado institucional --}}
<div class="encabezado">
    <div class="iglesia">La Iglesia de Jesucristo de los Santos de los Ultimos Dias</div>
    <div class="unidad">Barrio Terminal</div>
    <div class="estaca">Estaca Oruro &middot; Bolivia</div>
</div>

{{-- Ciudad y fecha --}}
<div class="fecha-lugar">
    Oruro, {{ $fechaCarta }}
</div>

{{-- Destinatario --}}
<div class="destinatario">
    <div class="nombre-linea">Hno(a). <strong>{{ $carta['destinatario'] }}</strong></div>
    <div class="presente">Presente.-</div>
</div>

{{-- Referencia --}}
<div class="referencia">
    <strong>Ref.: {{ $ref }}</strong>
</div>

{{-- Cuerpo --}}
<div class="cuerpo">

    <p>El Obispado del Barrio Terminal, en la Estaca Oruro, le saluda cordialmente con la seguridad de que el Senor conoce su corazon y aprecia profundamente su disposicion para servir. Esperamos que la alegria del evangelio se refleje cada dia en usted y en toda su familia.</p>

    <p>Tenemos el privilegio de notificarle que ha sido asignado(a) para {!! $asignacionTexto !!}</p>

    @if($esDiscurso)
    <div class="escritura">
        <span class="escritura-texto">&laquo;... porque cuando un hombre habla por el poder del Espíritu Santo, el poder del Espíritu Santo lo lleva al corazón de los hijos de los hombres.&raquo;</span>
        <span class="escritura-ref">&mdash; 2 Nefi 33:1</span>
    </div>

    <p>Creemos firmemente que esta asignacion no es una coincidencia; el Senor le ha preparado para este momento. Su testimonio y las palabras que comparta tienen el poder de tocar corazones, fortalecer la fe de sus hermanos y traer el Espiritu Santo a nuestra reunion. Le invitamos a prepararse con oracion, estudio de las escrituras y fe, confiando en que el Espiritu le guiara en cada palabra.</p>
    @else
    <div class="escritura">
        <span class="escritura-texto">&laquo;Por consiguiente, harás todo cuanto hicieres en el nombre del Hijo, y te arrepentirás e invocarás a Dios en el nombre del Hijo para siempre jamás.&raquo;</span>
        <span class="escritura-ref">&mdash; Moisés 5:8</span>
    </div>

    <p>Una oracion sincera abre los cielos y prepara los corazones para recibir el Espiritu. Le invitamos a orar con fe y confianza, sabiendo que el Senor escucha y que sus palabras seran una bendicion para toda la congregacion.</p>
    @endif

    <p>Si tuviera algun inconveniente para cumplir con esta asignacion, o si desea orientacion para preparar su {{ $esDiscurso ? 'mensaje' : 'oracion' }}, le pedimos contactar al secretario de barrio al menos <strong>3 dias antes</strong> de la reunion, o comunicarse con el presidente de la Escuela Dominical, <strong>Hno. Melvin Aquino</strong>, al numero <strong>65436989</strong>, quien con mucho gusto le brindara el apoyo necesario.</p>

    <p>Rogamos que el Senor le prepare, le fortalezca y le llene de Su Espiritu para que esta reunion sea una experiencia de edificacion y gozo para todos sus hermanos.</p>

</div>

{{-- Pie y firma --}}
<div class="pie">
    <div class="despedida">Atentamente,</div>
    <div class="firma-nombre">Obispado Barrio Terminal</div>
</div>
