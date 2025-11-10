<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;

class ViewerController extends Controller
{
    public function show($slug)
    {
        $document = Document::where('is_published', true)
            ->where('title', 'like', "%$slug%")
            ->with(['pages' => function ($q) {
                $q->orderBy('page_number');
            }])->firstOrFail();

        return response()->json([
            'title' => $document->title,
            'pages' => $document->pages->map(fn($p) => asset('storage/' . $p->image_path)),
        ]);
    }
}
