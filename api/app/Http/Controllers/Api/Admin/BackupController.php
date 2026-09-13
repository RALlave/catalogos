<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DatabaseRestoreRequest;
use App\Http\Requests\Admin\FilesRestoreRequest;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import and export of the whole platform. Superadmin only: a restore replaces
 * the data of every store at once.
 */
class BackupController extends Controller
{
    public function __construct(private readonly BackupService $backup) {}

    public function database(): BinaryFileResponse|JsonResponse
    {
        try {
            $dump = $this->backup->dumpDatabase();
        } catch (RuntimeException $error) {
            return $this->failed($error);
        }

        return response()
            ->download($dump, $this->backup->databaseFilename(), ['Content-Type' => 'application/sql'])
            ->deleteFileAfterSend();
    }

    public function restoreDatabase(DatabaseRestoreRequest $request): JsonResponse
    {
        try {
            $this->backup->restoreDatabase($request->file('file'));
        } catch (RuntimeException $error) {
            return $this->failed($error);
        }

        return response()->json(['message' => 'Base de datos restaurada.']);
    }

    public function files(): BinaryFileResponse|JsonResponse
    {
        try {
            $archive = $this->backup->archiveFiles();
        } catch (RuntimeException $error) {
            return $this->failed($error);
        }

        return response()
            ->download($archive, $this->backup->filesFilename(), ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }

    public function restoreFiles(FilesRestoreRequest $request): JsonResponse
    {
        try {
            $this->backup->restoreFiles($request->file('file'));
        } catch (RuntimeException $error) {
            return $this->failed($error);
        }

        return response()->json(['message' => 'Archivos restaurados.']);
    }

    private function failed(RuntimeException $error): JsonResponse
    {
        return response()->json(['message' => $error->getMessage()], 500);
    }
}
