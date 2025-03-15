<p style="margin-bottom: 2rem !important;">{!! $url !!}</p>
<p style="margin-bottom: 2rem !important;">
    <strong>{{ translate('Name') }}:</strong> {{ $name }}<br>
    <strong>{{ translate('Email') }}:</strong> {{ $email }}
    @if ($phone != null)
    <br>
    <strong>{{ translate('Phone') }}:</strong> {{ $phone }}
    @endif
    <strong>{{ translate('Pincode') }}:</strong> {{ $pincode }}
</p>
<a href="{{ env('APP_URL') }}">{{ translate('Go to the website') }}</a>