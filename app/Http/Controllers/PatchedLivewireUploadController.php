<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Replacement for Livewire's FileUploadController.
 *
 * On Windows + Laragon, `UploadedFile::getRealPath()` sometimes returns false
 * for valid uploads (the PHP-level temp file is unreachable to realpath()),
 * which makes Laravel's `putFileAs` call `fopen('', 'r')` and throw
 * "Path must not be empty". We sidestep that by streaming the uploaded file
 * via `getPathname()` directly, which always works.
 */
class PatchedLivewireUploadController extends Controller
{
    public function handle()
    {
        abort_unless(request()->hasValidSignature(), 401);

        $disk = FileUploadConfiguration::disk();
        $files = request('files', []);

        Validator::make(['files' => $files], [
            'files.*' => FileUploadConfiguration::rules(),
        ])->validate();

        $paths = collect($files)->map(function ($file) use ($disk) {
            $filename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
            $relativeDir = FileUploadConfiguration::path(); // e.g. "livewire-tmp"
            $relativePath = trim($relativeDir, '/') . '/' . $filename;

            // Open the uploaded file via getPathname() — this works even when
            // getRealPath() returns false on Windows.
            $source = $file->getPathname();
            if (!$source || !is_file($source)) {
                abort(422, 'Uploaded file is unreadable on the server.');
            }

            $stream = fopen($source, 'rb');
            if ($stream === false) {
                abort(500, 'Could not open uploaded file for reading.');
            }

            try {
                Storage::disk($disk)->put($relativePath, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            return $filename;
        })->all();

        return ['paths' => $paths];
    }
}
