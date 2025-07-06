<?php

namespace App\Models;

use App\Database\ServiceData;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Service extends Model
{
    use Sushi;

    protected $casts = [
        'required_fields' => 'array',
    ];

    protected $hidden = [
        'class_name'
    ];

    public function getRows()
    {
        return ServiceData::all();
    }

    protected function sushiShouldCache()
    {
        return true;
    }

    protected function sushiCacheReferencePath()
    {
        return app_path('Database/ServiceData.php');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category', 'slug');
    }

    public function createInstance()
    {
        $className = $this->class_name;
        return new $className($this);
    }
} 