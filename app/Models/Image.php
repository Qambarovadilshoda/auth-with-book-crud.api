<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\URL;

class Image extends Model
{
    protected $fillable = [
        'path'
    ] ;
    public function imageable(){
        return $this->morphTo();
    }

    public function url(): Attribute
    {
        return Attribute::make(fn(): string => URL::to('storage/' . $this->path));
    }

}
