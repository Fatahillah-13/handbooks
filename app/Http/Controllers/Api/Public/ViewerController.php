<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class ViewerController extends Controller
{
    // GET /api/viewer/{document}
    public function show(Document $document)
    {
        // ambil semua halaman dokumen, urut per page_number
        $pages = $document->pages()
            ->orderBy('page_number')
            ->get();

        // setiap page → url API yang akan dipakai Flipbook
        $pageUrls = $pages->map(function (Page $page) {
            return route('viewer.page', ['page' => $page->id]);
        });

        return response()->json([
            'title' => $document->title,
            'pages' => $pageUrls,
        ]);
    }

    // GET /api/viewer/page/{page}
    public function page(Page $page)
    {
        $path = storage_path('app/' . $page->image_path); // private/pages/15/page_1.jpg

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="page_' . $page->page_number . '.jpg"',
        ]);
    }
}
