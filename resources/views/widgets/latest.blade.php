<div class="la-latest-widget">

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                {{ $title }}
            </p>
        </header>
        <div class="card-content">
            @foreach($data as $item)
                <article class="media">
                    @if($image)
                        <figure class="media-left">
                            @if($item[$image->key])
                                <p class="image is-64x64">
                                    {!! $item[$image->key] !!}
                                </p>
                            @else
                                <p class="image is-64x64">
                                    <img src="https://bulma.io/images/placeholders/128x128.png">
                                </p>
                            @endif
                        </figure>
                    @endif
                    <div class="media-content">
                        <div class="content">
                            <p>
                                @foreach($fields as $field)

                                    @if($loop->first)
                                        <strong>{!! $item[$field->key] !!}</strong>
                                    @else
                                        <br><strong>{{ $field->browse_label }}: </strong>{!! $item[$field->key] !!}
                                    @endif

                                @endforeach
                            </p>
                        </div>
                    </div>
                    <div class="media-right">
                        @isset($item['la_edit_link'])
                            <a href="{{ $item['la_edit_link'] }}">
                                <b-icon icon="edit"></b-icon>
                            </a>
                        @endisset
                    </div>
                </article>
            @endforeach
        </div>
    </div>

</div>