@extends('laradmin::layouts.auth', ['bodyClass' => 'login-template', 'pageTitle' => trans('laradmin::login.title')])

@section('form')

    <login inline-template>
        <div>
            <form @submit.prevent="login" novalidate>
                <b-notification v-if="loginForm.errors.has('form')" type="is-danger">
                    @{{ loginForm.errors.get("form") }}
                </b-notification>

                <b-field label="@lang('laradmin::login.email')"
                         :type="loginForm.errors.has('email') ? 'is-danger' : ''"
                         :message="loginForm.errors.has('email') ? loginForm.errors.get('email') : ''">
                    <b-input placeholder="@lang('laradmin::login.email_placeholder')"
                             type="email"
                             ref="emailInput"
                             v-model="loginForm.email">
                    </b-input>
                </b-field>

                <b-field label="@lang('laradmin::login.password')"
                         :type="loginForm.errors.has('password') ? 'is-danger' : ''"
                         :message="loginForm.errors.has('password') ? loginForm.errors.get('password') : ''">
                    <b-input type="password"
                             placeholder="@lang('laradmin::login.password_placeholder')"
                             v-model="loginForm.password"
                             password-reveal>
                    </b-input>
                </b-field>

                <div class="field">
                    <b-checkbox v-model="loginForm.remember">
                        @lang('laradmin::login.remember')
                    </b-checkbox>
                </div>

                <div class="field">
                    <button type="submit" class="button is-primary"
                            :class="{'is-loading': loginForm.busy}">
                        @lang('laradmin::login.login')
                    </button>
                </div>
            </form>
        </div>
    </login>

@endsection

@section('content')

    <div class="content">
        <h1 class="title is-1">Welcome To Laradmin</h1>
        <p class="subtitle is-3">
            It gonna blow your mind
        </p>
    </div>

@endsection