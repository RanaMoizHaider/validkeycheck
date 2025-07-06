<?php

namespace App\Models;

use App\Database\CategoryData;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Category extends Model
{
    use Sushi;

    protected $hidden = [
        'id'
    ];

    public function getRows()
    {
        return CategoryData::all();
    }

    protected function sushiShouldCache()
    {
        return true;
    }

    protected function sushiCacheReferencePath()
    {
        return app_path('Database/CategoryData.php');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category', 'slug');
    }
}
