<?php

namespace App\Http\Controllers\Api\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Storage::withCount('bintexes')->orderBy('id', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['created_by'] = $request->user()->id;
        $storage = Storage::create($data);

        return response()->json($storage, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Storage $storage)
    {
        return $storage->load('bintexes');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Storage $storage)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (isset($data['name'])) $data['slug'] = Str::slug($data['name']);
        $storage->update($data);
        return $storage;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Storage $storage)
    {
        $storage->delete();
        return response()->json(['message' => 'Storage deleted']);
    }
}
