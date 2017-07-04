<?php

namespace Shemi\Laradmin\Http\Controllers;


use Shemi\Laradmin\Data\Data;
use Shemi\Laradmin\Facades\Laradmin;

class MenusController extends Controller
{

    public function index()
    {
        /** @var Data $icons */
        $icons = Laradmin::data()->load('md-icons2', 'defaults');

//        $icons->filter(function($icon) {
//            $inName = str_contains(strtolower($icon['name']), strtolower('gps'));
//            $inKeywords = array_filter($icon['keywords'], function($keyword) {
//                return str_contains(strtolower($keyword), strtolower('gps'));
//            });
//
//            return $inName || count($inKeywords) > 0;
//        })->save();

        dd($icons);

    }

}