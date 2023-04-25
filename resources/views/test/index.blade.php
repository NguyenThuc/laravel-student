@extends('layouts.app')

@section('content')

  <test-index title='{{$title}}' :items='{{ $items }}'>page - test</test-index>

@endsection