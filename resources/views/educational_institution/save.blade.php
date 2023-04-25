@extends('layouts.app')
@section('title', $title)
@section('content')
    <edu-institution-create-page
    :sellers="{{$sellers}}"
    :categories="{{$categories}}"
    @if(isset($eduInstitution))
    :edu_institution="{{$eduInstitution}}"
    @endif
    ></edu-institution-create-page>
@endsection
