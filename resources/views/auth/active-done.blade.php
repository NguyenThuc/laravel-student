@extends('layouts.app', ['menu' => false ,'disableAuth' => 'true'])
@section('title', $title)
@section('content')
    <div class="text-center">
        <h1 class="heading">パスワードが設定されました</h1>
        <a href="/login" class="btn-return">ログインページ</a>
    </div>
@endsection
@section('style')
    <style>
        .btn-return {
            height: 55px;
            background: #ECECEC;
            color: #000000;
            padding: 15px 90px;
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
        }
        .heading {
            font-weight: bold;
        }
    </style>
@endsection
