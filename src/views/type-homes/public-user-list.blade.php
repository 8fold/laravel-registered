<div class="ef-grid-full">
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
