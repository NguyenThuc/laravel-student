@extends('layouts.app')
@section('title', $title)
@section('content')
  <seller-detail-page :seller="{{ $seller }}"></seller-detail-page>
@endsection
