@extends('layouts.app')
@section('title', $title)
@section('content')
  <education-staff-list-page :roles = {{json_encode($roles)}}></education-staff-list-page>

@endsection
