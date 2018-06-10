<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Illuminate\Http\Request;
use Shemi\Laradmin\Models\SettingsPage;

interface SettingsRequestValidatorRepository extends Repository
{

    public function validate(Request $request, SettingsPage $page, $throw = true);

}