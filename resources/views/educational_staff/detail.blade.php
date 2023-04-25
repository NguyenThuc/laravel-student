@extends('layouts.app')
@section('title', $title)
@section('content')
    <educational-staff-detail-page :educational_staff="{{ $educationalStaff }}" :is_change="@json($isChange)"></educational-staff-detail-page>
@endsection
