<?php

namespace Shemi\Laradmin\Http\Controllers;

use Shemi\Laradmin\Database\Schema\SchemaManager;
use Shemi\Laradmin\Models\Type;

class TypesBuilderController extends Controller
{

    public function index()
    {
        $types = Type::all();

        return view('laradmin::typeBuilder.browse', compact('types'));
    }

    public function create()
    {
        $type = new Type;
        $tables = SchemaManager::listTables();

        return view('laradmin::typeBuilder.createEdit', compact('type', 'tables'));
    }

}