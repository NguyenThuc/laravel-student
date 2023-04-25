@extends('layouts.app')
@section('title', $title)
@section('content')
@if($errors->has('message'))
    <div class="error">{{ $errors->first('message') }}</div>
@endif
    <educational-institution-list-page></educational-institution-list-page>
@endsection
