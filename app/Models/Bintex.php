<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bintex extends Model
{
    protected $fillable = ['storage_id', 'name', 'slug', 'description', 'created_by'];
    public function storage()
    {
        return $this->belongsTo(Storage::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
