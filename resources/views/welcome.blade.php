<!-- welcome.blade.php -->
@extends('layouts.app')

@section('title', 'ManagerClin - Sistema para Clínicas')

@section('styles')
<!-- Seus estilos CSS aqui -->
@endsection

@section('content')
@include('sections.hero')
@include('sections.features')
@include('sections.pricing')
@include('sections.testimonials')
@include('sections.cta')
@endsection

@section('scripts')
<!-- Seus scripts JavaScript aqui -->
@endsection
