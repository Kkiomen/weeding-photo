<?php

namespace App\Console\Commands;

use App\Models\BingoMark;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDemoPhotos extends Command
{
    protected $signature = 'demo:purge';

    protected $description = 'Usuń dane demo (zdjęcia, gości, bingo marks, cały katalog photos/demo-*)';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $count = 0;

        Photo::query()
            ->where('path', 'like', 'photos/demo-%')
            ->chunk(100, function ($chunk) use ($disk, &$count) {
                foreach ($chunk as $photo) {
                    BingoMark::where('photo_id', $photo->id)->delete();
                    $disk->delete([$photo->path, $photo->thumb_path]);
                    $photo->delete();
                    $count++;
                }
            });

        $this->info("Usunięto {$count} zdjęć demo.");

        // Wyczyść puste katalogi demo
        foreach ($disk->directories('photos') as $dir) {
            if (str_starts_with(basename($dir), 'demo-') && empty($disk->files($dir))) {
                $disk->deleteDirectory($dir);
            }
        }

        return self::SUCCESS;
    }
}
