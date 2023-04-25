@extends('layouts.app', ['menu' => false, 'disableAuth' => 'true'])
@section('title', $title)
@section('content')
    <change-password-page></change-password-page>
@endsection

