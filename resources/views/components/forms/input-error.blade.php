@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-danger mt-2']) }}>
        @foreach ((array) $messages as $message)
            <small>{{ $message }}</small><br>
        @endforeach
    </div>
@endif
