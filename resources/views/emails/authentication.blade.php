@extends('emails.layouts.base')
@section('title', $title)
@section('content')
    <x-mail.heading>{{ $title }}</x-mail.heading>
    <p>{{ __('Hi :name,', ['name' => $user->name]) }}</p>
    <p>{{ $intro }}</p>
    <x-mail.action :url="$actionUrl" :button="true">{{ $actionText }}</x-mail.action>
    @foreach($outroLines as $line)
        <p style="color: #6b7280; font-size: 14px;">{{ $line }}</p>
    @endforeach
@endsection
