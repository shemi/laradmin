<?php

namespace Shemi\Laradmin\Contracts\Repositories;

use Shemi\Laradmin\Models\SettingsPage;

interface TransformSettingsDataRepository extends Repository
{

    public function transform(SettingsPage $page);

}