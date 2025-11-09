<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'created_by'];
    public function bintexes()
    {
        return $this->hasMany(Bintex::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
