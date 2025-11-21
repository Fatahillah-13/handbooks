<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Page;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ViewerController extends Controller
{
    // GET /api/viewer/{document}
    public function show(Document $document)
    {
        // load relasi bintex & storage
        $document->load(['bintex.storage']);

        AuditLog::record('document.viewed', $document, [
            'title'   => $document->title,
            'bintex'  => $document->bintex?->name,
            'storage' => $document->bintex?->storage?->name,
        ]);

        $pages = $document->pages()
            ->orderBy('page_number')
            ->get();

        $pageUrls = $pages->map(function (Page $page) {
            return URL::temporarySignedRoute(
                'viewer.page',                   // nama route
                now()->addMinutes(5),            // masa berlaku URL (bisa kamu ubah)
                ['page' => $page->id]            // parameter route
            );
        });

        return response()->json([
            'title' => $document->title,
            'pages' => $pageUrls,
            'bintex' => $document->bintex ? [
                'name' => $document->bintex->name,
                'slug' => $document->bintex->slug,
            ] : null,
            'storage' => ($document->bintex && $document->bintex->storage) ? [
                'name' => $document->bintex->storage->name,
                'slug' => $document->bintex->storage->slug,
            ] : null,
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
