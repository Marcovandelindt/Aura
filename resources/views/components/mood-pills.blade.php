@if(!empty($moods))
    <span class="mood-pills{{ $compact ?? false ? '-compact' : '' }}">
        @foreach($moods as $mood)
            <span class="mood-pill" style="background-color: {{ $mood['color'] }};" title="{{ $mood['name'] }}">
                <i class="{{ $mood['icon'] }} mood-icon"></i>
                <span class="mood-text">{{ $mood['name'] }}</span>
            </span>
        @endforeach
    </span>
@endif