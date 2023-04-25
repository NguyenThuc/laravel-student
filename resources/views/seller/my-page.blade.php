@extends('layouts.app')
@section('title', $title);
@section('content')
  <my-page :seller="{{ $seller }}"></my-page>
@endsection
