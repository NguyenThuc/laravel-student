@extends('layouts.app')
@section('title', $title)
@section('content')
    <seller-create-page :seller='@json($seller)'
                        @if(isset($seller->id)):seller_educational_ins='@json($sellerEducationalIns)' @endif>
    </seller-create-page>
@endsection
