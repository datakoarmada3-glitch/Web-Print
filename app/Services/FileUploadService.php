<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileUploadService
{
    public function storeUploadedFile(UploadedFile $file): array
    {
        $this->validateMimeType($file);

        $originalName = $this->sanitizeFilename($file->getClientOriginalName());
        $extension = strtolower($file->getClientOriginalExtension());
        $directory = 'print-jobs/originals/' . now()->format('Y/m/d');
        $storedName = Str::uuid() . '.' . $extension;
        $path = $file->storeAs($directory, $storedName, 'local');

        if (!$path) {
            throw new InvalidArgumentException('Failed to store uploaded file.');
        }

        return [
            'original_filename' => $originalName,
            'stored_original_path' => $path,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    public function sanitizeFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $cleanName = Str::of($name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9\-_ ]/', '')
            ->squish()
            ->replace(' ', '-')
            ->lower();

        if ($cleanName->isEmpty()) {
            $cleanName = Str::of('document');
        }

        return $cleanName . '.' . strtolower($extension);
    }

    private function validateMimeType(UploadedFile $file): void
    {
        $allowedMimes = config('print.allowed_mimes', []);
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedMimes, true)) {
            throw new InvalidArgumentException('Unsupported MIME type: ' . $mimeType);
        }
    }

    public function absolutePath(string $storagePath): string
    {
        return Storage::disk('local')->path($storagePath);
    }
}
