<?php

namespace App\Models;

use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use Sushi;

    protected $casts = [
        'required_fields' => 'array',
    ];

    protected $hidden = [
        'id', 'class_name'
    ];

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