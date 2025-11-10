<?php

namespace App\Jobs;

use App\Models\{Document, Page};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Exception;
use Illuminate\Support\Facades\Log;

class ConvertPdfToImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected Document $document;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $doc = $this->document;
        $pdfPath = storage_path('app/' . $doc->file_path_private);
        $outputDir = 'private/pages/' . $doc->id;

        Storage::makeDirectory($outputDir);

        try {

            if (!file_exists($pdfPath)) {
                throw new Exception("PDF file not found: {$pdfPath}");
            }

            $imagick = new Imagick();
            $imagick->setResolution(150, 150); // kualitas
            $imagick->readImage($pdfPath);

            $pageCount = $imagick->getNumberImages();
            $pageNum = 1;

            foreach ($imagick as $page) {
                $page->setImageFormat('jpeg');
                $page->setImageCompressionQuality(85);
                $imageBlob = $page->getImageBlob();
                $filename = $outputDir . "/page_{$pageNum}.jpg";
                Storage::put($filename, $imageBlob);
                if (!Storage::exists($filename)) {
                    Log::error("Failed to save page {$pageNum}");
                    continue;
                }
                Page::create([
                    'document_id' => $doc->id,
                    'page_number' => $pageNum,
                    'image_path' => $filename
                ]);
                $pageNum++;
            }

            $doc->update([
                'page_count' => $pageCount,
                'is_published' => true
            ]);

            // Bersihkan memory
            $imagick->clear();
        } catch (Exception $e) {
            Log::error('PDF convert error: ' . $e->getMessage());
        }
    }
}
