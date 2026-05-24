@extends('layouts.admin')
@section('title', 'Nouveau produit')
@section('header', 'Nouveau produit')

@section('content')
@include('admin.products._form', ['product' => null])
@endsection
