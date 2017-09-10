@extends('layouts.app')

@section('content')
<section>
    @foreach ($registrations as $registration)
    <form action="{{ url($registration->profilePath) }}/account/type" method="POST">
        {{ csrf_field() }}
        {!! UIKit::select([
            'label' => 'User type for '. $registration->displayName,
            'name' => 'type',
            'options' => $registration->userTypeSelectOptions(),
            'selected' => $registration->type->slug
        ]) !!}
        {!! UIKit::button(['label' => 'change type']) !!}
    </form>
    @endforeach
</section>
@endsection
