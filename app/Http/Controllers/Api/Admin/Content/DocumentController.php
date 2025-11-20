<?php

namespace App\Http\Controllers\Api\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\{Document, Page};
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\ConvertPdfToImages;

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
        $request->validate([
            'bintex_id' => 'required|exists:bintexes,id',
            'title' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:20480',
            'is_confidential' => 'nullable',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';

        // Simpan file di storage privat
        $path = $file->storeAs('private/pdfs', $fileName);

        // Buat record dokumen
        $document = Document::create([
            'bintex_id' => $request->bintex_id,
            'title' => $request->title,
            'filename_original' => $originalName,
            'file_path_private' => $path,
            'page_count' => 0,
            'is_published' => false,
            'allow_download' => false,
            'is_confidential' => $request->boolean('is_confidential', false),
            'created_by' => $request->user()->id,
        ]);

        // Dispatch job konversi PDF → gambar
        ConvertPdfToImages::dispatch($document);

        return response()->json([
            'message' => 'Upload berhasil, file sedang dikonversi...',
            'document' => $document
        ]);
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
