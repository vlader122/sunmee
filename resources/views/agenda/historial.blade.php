@extends('layouts.app')

@section('title', 'Historial de Agendas - Agenda Sacramental')

@section('content')
<div id="historial-app"></div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const { HistorialAgendas } = window.VueComponents;
        window.mountVueApp('#historial-app', HistorialAgendas);
    });
</script>
@endpush
