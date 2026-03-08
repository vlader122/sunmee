@extends('layouts.app')

@section('title', 'Generar Cartas - Agenda Sacramental')

@section('content')
<div id="generar-cartas-app"></div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const { GenerarCartas } = window.VueComponents;
        window.mountVueApp('#generar-cartas-app', GenerarCartas);
    });
</script>
@endpush
