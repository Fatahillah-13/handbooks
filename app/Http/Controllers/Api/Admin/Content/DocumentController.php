<?php

namespace App\Http\Controllers\Api\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Document::with('bintex')->orderBy('id', 'desc')->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        return $document->load(['bintex.storage']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'is_published' => 'required|boolean',
            'allow_download' => 'required|boolean',
        ]);

        $document->update($data);
        return $document;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(['message' => 'Document deleted']);
    }
}
