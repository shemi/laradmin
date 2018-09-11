<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Shemi\Laradmin\Exceptions\DataNotFoundException;
use Shemi\Laradmin\Filters\Filter;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Contracts\Repositories\CreateUpdateRepository;

class FiltersController extends Controller
{

    public function query($typeSlug, $filterName, Request $request)
    {
        $type = $this->getTypeBySlug($typeSlug);
        /** @var Filter $filter */
        $filter = $type->filters()->first(function(Filter $filter) use ($filterName) {
            return $filter->getName() === $filterName;
        });

        if(! $filter) {
            throw new DataNotFoundException($typeSlug);
        }

        return $this->response(
            $filter->getOptions($request)
        );
    }

}