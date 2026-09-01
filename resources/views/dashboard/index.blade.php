@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <h1>Dashboard</h1>

@endsection

@push('scripts')

<script>
    console.log('Dashboard page loaded');
</script>

@endpush