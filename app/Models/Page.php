<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['document_id', 'page_number', 'image_path'];
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
