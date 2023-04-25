@extends('layouts.app')
@section('title', $title)
@section('content')

  <educational-institution-detail-page :edu_institution="{{ $eduInstitution }}"></educational-institution-detail-page>

@endsection
