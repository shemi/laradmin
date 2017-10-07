@extends('laradmin::crud.createEdit')

@section('main-form')

    @component('laradmin::components.input', [
        'label' => 'Name',
        'model' => 'name'
    ])@endcomponent

    @component('laradmin::components.select', [
        'label' => 'Guard',
        'model' => 'guard_name',
        'properties' => ['placeholder=Select Guard', 'expanded']
    ])

        @foreach(config('auth.guards') as $name => $settings)
            <option value="{{ $name }}">{{ $name }}</option>
        @endforeach

    @endcomponent



@endsection

@section('side-form')

    @component('laradmin::components.meta-box', ['model' => $model])

        @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.created_at'])
            form.created_at
        @endcomponent

        @component('laradmin::components.meta-line', ['langKey' => 'laradmin::template.updated_at'])
            form.updated_at
        @endcomponent

    @endcomponent

@endsection