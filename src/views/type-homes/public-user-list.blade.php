<div class="ef-grid-full">
    <form action="{{ route('profiled.add-user-type') }}" method="POST">
        {!! UIKit::textInput([
            'label' => 'The display name for the type'
            'hint' => 'Should be plural. Will be slugified.'
            'name' => 'display',
            'value' => old('display')
            'error' => ($errors->has('display'))
                ? $errors->get('display')
                : ''
        ]) !!}
        {!! UIKit::button([
            'label' => 'create type'
        ]) !!}
    </form>
@if ($registrations->count() == 0)
<p>We do not have any {{ str_plural($user_type) }} registered at this time.</p>
@else
@foreach ($registrations as $registration)
    @if($loop->iteration % 4 == 0)
    <div class="ef-width-one-fourth ef-end-row user-list">
    @else
    <div class="ef-width-one-fourth user-list">
    @endif
    <p><a href="{{ $registration->profilePath }}">{{ $registration->displayName }}</a></p>
    </div>
@endforeach
@endif
</div>
