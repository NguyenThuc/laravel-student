@extends('layouts.app')
@section('title', $title);
@section('content')
  <seller-profile-page :seller="{{ $seller }}"></seller-profile-page>
@endsection