@extends('layouts.app')

@section('title', 'Gestion de Miembros')

@section('content')
<div id="members-app"></div>
@endsection

@push('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const { MemberList } = window.VueComponents;
        window.mountVueApp('#members-app', MemberList);
    });
</script>
@endpush
