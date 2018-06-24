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
                            <la-field-renderer :value="{{ attr_json_encode($item[$image->key]) }}"
                                               type="{{ $image->type }}"
                                               empty-string=""
                                               image-size="64x64"
                                               :template-options="{{ attr_json_encode($image->template_options) }}"
                                               :browse-settings="{{ attr_json_encode($image->browse_settings) }}"
                                               form-key="{{ $image->browse_key }}">
                            </la-field-renderer>
                        </figure>
                    @endif
                    <div class="media-content">
                        <div class="content">
                            @foreach($fields as $field)
                                <div class="latest-label-row is-row-{{ $loop->index }}">
                                    @if(! $loop->first)
                                        <strong class="label">{{ $field->browse_label }}: </strong>
                                    @endif
                                    <la-field-renderer :value="{{ attr_json_encode(data_get($item, $field->browse_key)) }}"
                                                       type="{{ $field->type }}"
                                                       empty-string=""
                                                       :template-options="{{ attr_json_encode($field->template_options) }}"
                                                       :browse-settings="{{ attr_json_encode($field->browse_settings) }}"
                                                       form-key="{{ $field->browse_key }}">
                                    </la-field-renderer>
                                </div>
                            @endforeach
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