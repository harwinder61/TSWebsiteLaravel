@extends('fan::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('fan.name') !!}</p>
@endsection
