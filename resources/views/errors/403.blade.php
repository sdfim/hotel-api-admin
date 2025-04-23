@extends('errors.minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
@section('login')
    <a href="{{ route('clear.cookies.and.login') }}">Login again</a>
@endsection
