@extends('layouts.app')
@section('content')
  <home-index-page title='{{$title}}' :items='{{ $items }}'> Title : {{$title}}</home-index-page>
@endsection
