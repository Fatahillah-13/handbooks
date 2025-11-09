<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['bintex_id', 'title', 'filename_original', 'file_path_private', 'page_count', 'is_published', 'allow_download', 'created_by'];

    public function bintex()
    {
        return $this->belongsTo(Bintex::class);
    }
    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
