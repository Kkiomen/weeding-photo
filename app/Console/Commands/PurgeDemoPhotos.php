<?php

namespace App\Console\Commands;

use App\Models\BingoCard;
use App\Models\BingoMark;
use App\Models\Guest;
use App\Models\Message;
use App\Models\Photo;
use App\Models\Quote;
use App\Models\QuoteLike;
use App\Models\RewardClaim;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeDemoPhotos extends Command
{
    protected $signature = 'demo:purge {--all : Wyczyść WSZYSTKO (gości, karty, wiadomości, cytaty) — nie tylko demo-photos}';

    protected $description = 'Usuń dane demo. Bez --all: tylko zdjęcia photos/demo-*. Z --all: kompletny reset.';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        if ($this->option('all')) {
            return $this->purgeEverything($disk);
        }

        return $this->purgeDemoOnly($disk);
    }

    private function purgeDemoOnly(\Illuminate\Contracts\Filesystem\Filesystem $disk): int
    {
        $count = 0;

        Photo::query()
            ->where('path', 'like', 'photos/demo-%')
            ->chunkById(100, function ($chunk) use ($disk, &$count) {
                foreach ($chunk as $photo) {
                    BingoMark::where('photo_id', $photo->id)->delete();
                    $disk->delete([$photo->path, $photo->thumb_path]);
                    $photo->delete();
                    $count++;
                }
            });

        $this->info("Usunięto {$count} zdjęć demo.");

        foreach ($disk->directories('photos') as $dir) {
            if (str_starts_with(basename($dir), 'demo-') && empty($disk->files($dir))) {
                $disk->deleteDirectory($dir);
            }
        }

        return self::SUCCESS;
    }

    private function purgeEverything(\Illuminate\Contracts\Filesystem\Filesystem $disk): int
    {
        $this->warn('Kasuję WSZYSTKO — gości, zdjęcia, wiadomości, cytaty, karty bingo, zdrapki.');

        // 1. Photos + pliki (dowolna ścieżka)
        $photoCount = 0;
        Photo::query()->chunkById(100, function ($chunk) use ($disk, &$photoCount) {
            foreach ($chunk as $photo) {
                $disk->delete([$photo->path, $photo->thumb_path]);
                $photo->delete();
                $photoCount++;
            }
        });

        // 2. Message photos
        $msgCount = 0;
        Message::query()->chunkById(100, function ($chunk) use ($disk, &$msgCount) {
            foreach ($chunk as $m) {
                if ($m->photo_path) {
                    $disk->delete([$m->photo_path, $m->thumb_path]);
                }
                $m->delete();
                $msgCount++;
            }
        });

        BingoMark::query()->delete();
        BingoCard::query()->delete();
        QuoteLike::query()->delete();
        Quote::query()->delete();
        RewardClaim::query()->delete();
        $guestCount = Guest::query()->count();
        Guest::query()->delete();

        // Wyczyść całe katalogi photos/ thumbs/ messages/ bingo/
        foreach (['photos', 'thumbs', 'messages', 'bingo'] as $dir) {
            if ($disk->exists($dir)) {
                $disk->deleteDirectory($dir);
            }
        }

        $this->info("Usunięto: {$photoCount} zdjęć, {$msgCount} wiadomości, {$guestCount} gości.");
        $this->info('Gotowe — apka jest w stanie dziewiczym, można ogłaszać wesele.');

        return self::SUCCESS;
    }
}
