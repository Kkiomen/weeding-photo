<?php

namespace Database\Seeders;

use App\Models\BingoCard;
use App\Models\BingoField;
use App\Models\BingoMark;
use App\Models\Guest;
use App\Models\Photo;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoPhotosSeeder extends Seeder
{
    private const NICKNAMES = [
        'Kasia', 'Bartek', 'Ania', 'Piotr', 'Ola', 'Marek', 'Zuzia', 'Tomek',
        'Ewa', 'Michał', 'Basia', 'Krzysiek', 'Magda', 'Wojtek', 'Justyna',
        'Adam', 'Weronika', 'Damian', 'Ciocia Halinka', 'Wujek Rysiek',
        'Babcia Zosia', 'Świadek Kuba', 'Świadkowa Iza', 'Grzegorz', 'Natalia',
        'Karolina', 'Mateusz', 'Julia', 'Paweł', 'Marta',
    ];

    private const PALETTES = [
        ['#f43f5e', '#fb7185', '#e11d48'], // rose
        ['#ec4899', '#f472b6', '#db2777'], // pink
        ['#f97316', '#fb923c', '#ea580c'], // orange
        ['#eab308', '#facc15', '#ca8a04'], // yellow
        ['#84cc16', '#a3e635', '#65a30d'], // lime
        ['#22c55e', '#4ade80', '#16a34a'], // green
        ['#14b8a6', '#2dd4bf', '#0d9488'], // teal
        ['#0ea5e9', '#38bdf8', '#0284c7'], // sky
        ['#8b5cf6', '#a78bfa', '#7c3aed'], // violet
        ['#d946ef', '#e879f9', '#c026d3'], // fuchsia
        ['#78716c', '#a8a29e', '#57534e'], // stone
        ['#f59e0b', '#fbbf24', '#d97706'], // amber
    ];

    public function run(): void
    {
        $this->command?->info('Tworzę zdjęcia demo…');

        // 1) Goście
        $guests = collect();
        foreach (self::NICKNAMES as $nick) {
            $g = Guest::firstOrCreate(['nickname' => $nick], ['xp' => random_int(20, 400)]);
            $guests->push($g);
        }
        $this->command?->info('Gości: '.$guests->count());

        // 2) Katalogi
        $disk = Storage::disk('public');
        $baseDir = 'photos/demo-'.date('Y-m');
        $thumbDir = 'thumbs';
        $absoluteBase = $disk->path($baseDir);
        $absoluteThumb = $disk->path($thumbDir);
        if (! is_dir($absoluteBase)) {
            mkdir($absoluteBase, 0755, true);
        }
        if (! is_dir($absoluteThumb)) {
            mkdir($absoluteThumb, 0755, true);
        }

        $tasks = Task::all();
        $bingoFields = BingoField::where('title', '!=', '❤️ Wolne pole')->get();

        $target = 120; // ile zdjęć demo
        $created = 0;

        for ($i = 0; $i < $target; $i++) {
            $guest = $guests->random();

            // Roll: 45% task photo, 15% bingo photo, 40% free photo
            $roll = random_int(1, 100);
            $task = null;
            $bingoField = null;

            if ($roll <= 45 && $tasks->count()) {
                $task = $tasks->random();
            } elseif ($roll <= 60 && $bingoFields->count()) {
                $bingoField = $bingoFields->random();
            }

            $palette = self::PALETTES[array_rand(self::PALETTES)];
            $label = $bingoField?->icon ?? $task?->icon ?? '📸';

            $uuid = (string) Str::uuid();
            $filename = $uuid.'.jpg';
            $absoluteFile = $absoluteBase.'/'.$filename;
            $absoluteThumbFile = $absoluteThumb.'/'.$filename;

            $this->generateImage($absoluteFile, 1200, 900, $palette, $label, $guest->nickname);
            $this->generateImage($absoluteThumbFile, 400, 300, $palette, $label, $guest->nickname);

            $relPath = $baseDir.'/'.$filename;
            $relThumb = $thumbDir.'/'.$filename;

            $photo = Photo::create([
                'guest_id' => $guest->id,
                'task_id' => $task?->id,
                'path' => $relPath,
                'thumb_path' => $relThumb,
                'file_hash' => hash('sha256', $uuid),
            ]);

            // Dla bingo — dopnij mark do karty gościa (jeśli ma)
            if ($bingoField) {
                $card = BingoCard::where('guest_id', $guest->id)->first();
                if ($card && in_array($bingoField->id, $card->field_ids, true)) {
                    $exists = BingoMark::where('card_id', $card->id)
                        ->where('field_id', $bingoField->id)
                        ->exists();
                    if (! $exists) {
                        BingoMark::create([
                            'card_id' => $card->id,
                            'field_id' => $bingoField->id,
                            'photo_id' => $photo->id,
                            'marked_at' => now(),
                        ]);
                    }
                }
            }

            $created++;
            if ($created % 20 === 0) {
                $this->command?->info("  … {$created}/{$target}");
            }
        }

        $this->command?->info("Gotowe: {$created} zdjęć demo.");
    }

    private function generateImage(string $path, int $w, int $h, array $palette, string $label, string $nick): void
    {
        $im = imagecreatetruecolor($w, $h);

        // Gradient tła (dwa kolory z palety, pionowo)
        [$c1, $c2] = [$this->hex($palette[0]), $this->hex($palette[2])];
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $r = (int) ($c1['r'] + ($c2['r'] - $c1['r']) * $t);
            $g = (int) ($c1['g'] + ($c2['g'] - $c1['g']) * $t);
            $b = (int) ($c1['b'] + ($c2['b'] - $c1['b']) * $t);
            $color = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $w, $y, $color);
        }

        // Kilka losowych "plam" (bokeh)
        $c3 = $this->hex($palette[1]);
        for ($i = 0; $i < 8; $i++) {
            $x = random_int(0, $w);
            $y = random_int(0, $h);
            $r = random_int((int) ($w * 0.05), (int) ($w * 0.15));
            $bokeh = imagecolorallocatealpha($im, $c3['r'], $c3['g'], $c3['b'], random_int(90, 115));
            imagefilledellipse($im, $x, $y, $r * 2, $r * 2, $bokeh);
        }

        // Podpis (built-in font, bez emoji bo GD nie renderuje unicode)
        $white = imagecolorallocatealpha($im, 255, 255, 255, 30);
        $text = mb_strtoupper($this->stripUnicode($nick));
        $fontSize = 5; // built-in max
        $textW = imagefontwidth($fontSize) * strlen($text);
        $textH = imagefontheight($fontSize);
        imagestring($im, $fontSize, (int) (($w - $textW) / 2), (int) (($h - $textH) / 2), $text, $white);

        imagejpeg($im, $path, 82);
        imagedestroy($im);
    }

    private function hex(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function stripUnicode(string $s): string
    {
        $map = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N',
            'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z',
        ];

        return strtr($s, $map);
    }
}
