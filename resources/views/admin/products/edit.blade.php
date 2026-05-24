@extends('layouts.admin')
@section('title', 'Modifier ' . $product->name)
@section('header', 'Modifier : ' . $product->name)

@section('content')
@include('admin.products._form', ['product' => $product])
@endsection
