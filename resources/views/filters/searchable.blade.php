<la-searchable-filter filter="{{ $key }}"
           label="{{ $label }}"
           :type="typeSlug"
           v-model="query.filters.{{ $key }}"
           :filter-data="controls.filters.{{ $key }}"
           @input="onFilter">
</la-searchable-filter>