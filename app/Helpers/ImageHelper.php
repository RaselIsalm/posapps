<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Upload an image to the specified path.
     *
     * @param  UploadedFile  $image
     * @param  string  $path
     * @return string
     */
    public static function uploadImage($name, $image, $path)
    {
        if (! $image instanceof UploadedFile || ! $image->isValid()) {
            throw new \RuntimeException('The uploaded file is missing or invalid.');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'heic', 'heif', 'svg'];
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: '');

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new \Exception('Invalid image format! Only JPG, JPEG, PNG, GIF, WEBP, AVIF, BMP, HEIC, HEIF and SVG are allowed.');
        }

        $path = trim(str_replace('\\', '/', (string) $path), '/');
        if ($path === '') {
            $path = 'uploads';
        }

        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[^\w.\-]+/', '_', $name) ?: '';
        if ($name === '' || $name === '.' || $name === '..') {
            $name = Str::random(20).'.'.$extension;
        }

        $source = $image->getRealPath();
        if ($source === false || $source === '') {
            $source = $image->getPathname();
        }

        if ($source === '' || ! is_readable($source)) {
            throw new \RuntimeException('Uploaded file path is empty. Check PHP upload_tmp_dir and folder permissions.');
        }

        $contents = file_get_contents($source);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read the uploaded file.');
        }

        Storage::disk('public')->makeDirectory($path);
        Storage::disk('public')->put($path.'/'.$name, $contents);

        // Laragon/Windows often has public/storage as a real folder (not a symlink).
        // Mirror the file so the web server can serve /storage/...
        self::mirrorToPublicStorage($path, $name, $contents);

        return $name;
    }

    /**
     * Mirror a public-disk file into public/storage when that path is a real directory.
     */
    protected static function mirrorToPublicStorage(string $path, string $name, string $contents): void
    {
        $publicRoot = public_path('storage');
        if (! is_dir($publicRoot)) {
            return;
        }

        // If public/storage is a real symlink/junction to storage/app/public, skip (already written).
        $realPublic = realpath($publicRoot);
        $realDisk = realpath(storage_path('app/public'));
        if ($realPublic && $realDisk && $realPublic === $realDisk) {
            return;
        }

        $dir = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dir.DIRECTORY_SEPARATOR.$name, $contents);
    }

    /**
     * Delete an existing image from the storage.
     *
     * @param  string|null  $image
     * @param  string  $path
     * @return void
     */
    public static function deleteImage($image, $path)
    {
        if (! empty($image)) {
            $filePath = trim($path.'/'.$image, '/');

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $publicFile = public_path('storage/'.str_replace('/', DIRECTORY_SEPARATOR, $filePath));
            if (is_file($publicFile)) {
                @unlink($publicFile);
            }
        }
    }

    /**
     * Update an image by deleting the old one and uploading a new one.
     *
     * @param  UploadedFile  $image
     * @param  string  $path
     * @param  string|null  $oldImage
     * @return string
     */
    public static function updateImage($name, $image, $path, $oldImage = null)
    {
        if (! empty($oldImage)) {
            self::deleteImage($oldImage, $path);
        }

        return self::uploadImage($name, $image, $path);
    }

    /**
     * Public URL for a file on the public disk (e.g. settings/logo.png).
     * Returns null if missing. Appends ?v=filemtime for cache busting.
     */
    public static function publicUrl(?string $filename, string $path = 'settings'): ?string
    {
        if (! is_string($filename)) {
            return null;
        }

        $filename = trim($filename);
        if ($filename === '') {
            return null;
        }

        $filename = basename(str_replace('\\', '/', $filename));
        $path = trim(str_replace('\\', '/', $path), '/');
        $relative = ($path !== '' ? $path.'/' : '').$filename;

        $onDisk = Storage::disk('public')->exists($relative);
        $publicFile = public_path('storage/'.str_replace('/', DIRECTORY_SEPARATOR, $relative));
        $onPublic = is_file($publicFile);

        if (! $onDisk && ! $onPublic) {
            return null;
        }

        // Heal out-of-sync public/storage (common when it is not a symlink).
        if ($onDisk && ! $onPublic) {
            try {
                self::mirrorToPublicStorage($path, $filename, Storage::disk('public')->get($relative));
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $url = Storage::disk('public')->url($relative);
        try {
            $mtime = $onDisk
                ? Storage::disk('public')->lastModified($relative)
                : (@filemtime($publicFile) ?: time());
            $url .= (str_contains($url, '?') ? '&' : '?').'v='.$mtime;
        } catch (\Throwable $e) {
            // ignore
        }

        return $url;
    }
}
