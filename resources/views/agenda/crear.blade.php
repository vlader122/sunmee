@extends('layouts.app')

@section('title', 'Crear Agenda Sacramental')

@section('content')
<div id="agenda-app"></div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const { AgendaForm } = window.VueComponents;
        window.mountVueApp('#agenda-app', AgendaForm, {
            mode: 'create'
        });
    });
</script>
@endpush
