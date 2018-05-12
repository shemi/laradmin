<div class="la-counter-widget" @if($color)style="background-color: {{ $color }}"@endif>
    <a href="{{ $action }}">
        <b-icon icon="{{ $icon }}"></b-icon>
        <span class="text">
            <span class="title">{{ $title }}</span>
            <span class="count">{{ $count }}</span>
        </span>
    </a>
</div>