@extends('layouts.app')

@section('title', 'Dashboard - Agenda Sacramental')

@section('content')
<div id="dashboard-app"></div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const { Dashboard } = window.VueComponents;
        window.mountVueApp('#dashboard-app', Dashboard);
    });
</script>
@endpush
