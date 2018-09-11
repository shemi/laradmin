<la-multiple-filter filter="{{ $key }}"
           label="{{ $label }}"
           :type="typeSlug"
           v-model="query.filters.{{ $key }}"
           :filter-data="controls.filters.{{ $key }}"
           @input="onFilter">
</la-multiple-filter>