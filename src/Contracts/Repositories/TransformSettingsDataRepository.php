<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Shemi\Laradmin\Models\SettingsPage;

interface TransformSettingsDataRepository
{

    public function transform(SettingsPage $page);

}