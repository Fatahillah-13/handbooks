<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bintex_id')->constrained('bintexes')->cascadeOnDelete();
            $table->string('title');
            $table->string('filename_original');
            $table->string('file_path_private'); // path PDF disimpan private
            $table->integer('page_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('allow_download')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
