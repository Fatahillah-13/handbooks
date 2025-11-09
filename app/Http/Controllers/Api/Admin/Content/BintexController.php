<?php

namespace App\Http\Controllers\Api\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Bintex;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BintexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Bintex::with('storage')->withCount('documents')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'storage_id' => 'required|exists:storages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['created_by'] = $request->user()->id;
        $bintex = Bintex::create($data);

        return response()->json($bintex, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bintex $bintex)
    {
        return $bintex->load('documents');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bintex $bintex)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        if (isset($data['name'])) $data['slug'] = Str::slug($data['name']);
        $bintex->update($data);
        return $bintex;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bintex $bintex)
    {
        $bintex->delete();
        return response()->json(['message' => 'Bintex deleted']);
    }
}
