<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicMediaController extends Controller
{
    /**
     * Stream a submission photo stored on the public disk.
     */
    public function submissionPhoto(Request $request, int $id, string $file)
    {
        // Sanitize filename
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            abort(404);
        }

        $relativePath = "uploads/submissions/{$id}/{$file}";

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($relativePath) ?: 'application/octet-stream';

        return response()->stream(function () use ($relativePath) {
            $stream = Storage::disk('public')->readStream($relativePath);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Disposition' => 'inline; filename="' . basename($relativePath) . '"',
        ]);
    }
}
