<?php

namespace Shemi\Laradmin\Tests\Controller\Fakes;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(TestUser::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_posts');
    }

}