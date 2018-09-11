@foreach($type->filters() as $filter)
    @if(! $filter->isDiffered() && ! $filter->isMultiple())
        <b-field label="{{ $filter->getLabel() }}">
            <b-select placeholder="---"
                      v-model="query.filters['{{ $filter->getName() }}']"
                      @input="onFilter"
                      expanded
            >

                <option :value="null">
                    ---
                </option>

                @foreach($filter->options() as $option)
                    <option value="{{ $option['key'] }}">
                        {{ $option['label'] }}
                    </option>
                @endforeach

            </b-select>
        </b-field>
    @endif
@endforeach