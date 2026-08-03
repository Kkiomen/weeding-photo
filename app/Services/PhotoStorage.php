<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoStorage
{
    /**
     * @return array{path: string, thumb_path: string, hash: string}
     */
    public function store(UploadedFile $file, string $prefix = 'photos'): array
    {
        $hash = hash_file('sha256', $file->getRealPath());

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif']) ? $ext : 'jpg';
        $dir = $prefix.'/'.date('Y-m');
        $filename = (string) Str::uuid().'.'.$ext;
        $path = $file->storeAs($dir, $filename, 'public');

        $thumbPath = $this->makeThumbnail($path, $filename);

        return [
            'path' => $path,
            'thumb_path' => $thumbPath,
            'hash' => $hash,
        ];
    }

    public function delete(string ...$paths): void
    {
        Storage::disk('public')->delete(array_filter($paths));
    }

    private function makeThumbnail(string $originalPath, string $filename): string
    {
        $thumbDir = 'thumbs';
        $thumbPath = $thumbDir.'/'.$filename;

        $disk = Storage::disk('public');
        $absoluteSrc = $disk->path($originalPath);
        $absoluteDst = $disk->path($thumbPath);

        if (! is_dir(dirname($absoluteDst))) {
            mkdir(dirname($absoluteDst), 0755, true);
        }

        $this->resizeImage($absoluteSrc, $absoluteDst, 400);

        return $thumbPath;
    }

    private function resizeImage(string $src, string $dst, int $maxSize): void
    {
        $info = @getimagesize($src);
        if (! $info) {
            copy($src, $dst);

            return;
        }

        [$w, $h, $type] = $info;

        $ratio = min($maxSize / $w, $maxSize / $h, 1.0);
        $nw = max(1, (int) ($w * $ratio));
        $nh = max(1, (int) ($h * $ratio));

        $srcImg = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($src),
            IMAGETYPE_PNG => imagecreatefrompng($src),
            IMAGETYPE_WEBP => imagecreatefromwebp($src),
            default => null,
        };

        if (! $srcImg) {
            copy($src, $dst);

            return;
        }

        $dstImg = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $nw, $nh, $w, $h);

        switch ($type) {
            case IMAGETYPE_PNG:
                imagepng($dstImg, $dst, 8);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($dstImg, $dst, 82);
                break;
            default:
                imagejpeg($dstImg, $dst, 82);
        }

        imagedestroy($srcImg);
        imagedestroy($dstImg);
    }
}
