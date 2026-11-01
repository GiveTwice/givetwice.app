@props(['label', 'gifts'])
@if($gifts->isNotEmpty())
    <h3 style="color: #374151; font-size: 14px; line-height: 1.6; font-weight: 600; margin: 16px 0 4px;">{{ $label }}</h3>
    <ul style="margin: 0; padding-left: 20px; color: #374151; font-size: 15px; line-height: 1.6;">
        @foreach($gifts as $gift)
            <li>{{ $gift->title ?: __('Untitled gift') }}</li>
        @endforeach
    </ul>
@endif
