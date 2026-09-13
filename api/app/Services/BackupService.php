<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Full backup of the platform: the database and the uploaded files.
 *
 * They are two separate archives on purpose, but they belong together: a
 * database restored without its files leaves products pointing at images that
 * are no longer on the disk.
 *
 * Both restores replace, they do not merge. The database drops every table
 * before loading the dump, and the file archive wipes the directories it owns
 * before extracting. Half a restore is worse than none.
 */
class BackupService
{
    /**
     * Dumps the whole database to a temporary .sql file and returns its path.
     *
     * The caller is responsible for the file; response()->download() deletes it
     * after sending.
     */
    public function dumpDatabase(): string
    {
        $defaults = $this->defaultsFile();
        $dump = $this->tempFile('dump', '.sql');

        $result = Process::timeout(config('backup.timeout'))->env($this->environment())->run([
            $this->binary('mysqldump'),
            '--defaults-extra-file='.$defaults,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--add-drop-table',
            '--default-character-set=utf8mb4',
            '--result-file='.$dump,
            $this->database(),
        ]);

        File::delete($defaults);

        if ($result->failed()) {
            File::delete($dump);

            throw new RuntimeException('No pudimos exportar la base de datos: '.$this->reason($result->errorOutput()));
        }

        return $dump;
    }

    /**
     * Drops every table and view and loads the uploaded dump in their place.
     *
     * The tables are dropped first —and not only the ones the file recreates—
     * so that a table added after the backup does not survive the restore with
     * stale rows in it.
     */
    public function restoreDatabase(UploadedFile $file): void
    {
        $defaults = $this->defaultsFile();
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            File::delete($defaults);

            throw new RuntimeException('No pudimos leer el archivo .sql');
        }

        $this->dropEverything();

        $result = Process::timeout(config('backup.timeout'))
            ->env($this->environment())
            ->input($handle)
            ->run([
                $this->binary('mysql'),
                '--defaults-extra-file='.$defaults,
                '--default-character-set=utf8mb4',
                $this->database(),
            ]);

        fclose($handle);
        File::delete($defaults);

        if ($result->failed()) {
            throw new RuntimeException('No pudimos importar la base de datos: '.$this->reason($result->errorOutput()));
        }
    }

    /**
     * Zips the uploaded files —every store's images plus the platform logos—
     * and returns the path of the temporary archive.
     */
    public function archiveFiles(): string
    {
        $this->assertZipIsAvailable();

        $zip = new ZipArchive;
        $path = $this->tempFile('files', '.zip');

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No pudimos crear el archivo .zip');
        }

        foreach ($this->directories() as $directory) {
            $root = $this->diskPath($directory);

            if (! File::isDirectory($root)) {
                continue;
            }

            $zip->addEmptyDir($directory);

            foreach (File::allFiles($root) as $file) {
                $zip->addFile($file->getRealPath(), $directory.'/'.str_replace('\\', '/', $file->getRelativePathname()));
            }
        }

        $zip->close();

        return $path;
    }

    /**
     * Replaces the uploaded files with the ones in the archive.
     *
     * The directories are emptied first, so what is left is exactly what the
     * .zip carries. Entries outside those directories are ignored: an archive
     * from somewhere else cannot write over the rest of the disk.
     */
    public function restoreFiles(UploadedFile $file): void
    {
        $this->assertZipIsAvailable();

        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('El archivo no es un .zip válido');
        }

        $entries = $this->entries($zip);

        if ($entries === []) {
            $zip->close();

            throw new RuntimeException('El .zip no contiene ninguna de las carpetas del respaldo ('.implode(', ', $this->directories()).')');
        }

        foreach ($this->directories() as $directory) {
            File::deleteDirectory($this->diskPath($directory));
        }

        foreach ($entries as $entry) {
            $target = $this->diskPath($entry);

            if (str_ends_with($entry, '/')) {
                File::ensureDirectoryExists($target);

                continue;
            }

            File::ensureDirectoryExists(dirname($target));

            $stream = $zip->getStream($entry);

            if ($stream === false) {
                continue;
            }

            file_put_contents($target, $stream);
            fclose($stream);
        }

        $zip->close();
    }

    public function databaseFilename(): string
    {
        return $this->database().'_'.now()->format('Y-m-d_Hi').'.sql';
    }

    public function filesFilename(): string
    {
        return $this->database().'_archivos_'.now()->format('Y-m-d_Hi').'.zip';
    }

    /**
     * Only the database half of this service works without ext-zip, so the
     * missing extension is reported as what it is instead of as a fatal error.
     */
    private function assertZipIsAvailable(): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('El servidor no tiene habilitada la extensión zip de PHP');
        }
    }

    /**
     * Entries of the archive that belong to one of our directories.
     *
     * This is also what stops a zip slip: an entry climbing out with `..`, an
     * absolute path or a backslash never matches a prefix and is dropped.
     *
     * @return list<string>
     */
    private function entries(ZipArchive $zip): array
    {
        $prefixes = array_map(fn (string $directory): string => $directory.'/', $this->directories());
        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if ($name === false || str_contains($name, '..') || str_contains($name, '\\')) {
                continue;
            }

            foreach ($prefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $entries[] = $name;

                    break;
                }
            }
        }

        return $entries;
    }

    /**
     * Drops every table and view of the current database.
     *
     * Foreign keys are switched off while it runs: dropping in the right order
     * would mean resolving the whole dependency graph first.
     */
    private function dropEverything(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach (DB::select('SHOW FULL TABLES') as $row) {
            $row = (array) $row;
            $name = $row['Tables_in_'.$this->database()] ?? array_values($row)[0];

            if (($row['Table_type'] ?? 'BASE TABLE') === 'VIEW') {
                DB::statement("DROP VIEW IF EXISTS `{$name}`");
            } else {
                DB::statement("DROP TABLE IF EXISTS `{$name}`");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Credentials in a file instead of on the command line, where any other
     * user of the server could read them with `ps`. MySQL warns about it too.
     */
    private function defaultsFile(): string
    {
        $connection = $this->connection();
        $path = $this->tempFile('dbcfg', '.cnf');

        File::put($path, implode("\n", [
            '[client]',
            'host='.($connection['host'] ?? '127.0.0.1'),
            'port='.($connection['port'] ?? 3306),
            'user='.($connection['username'] ?? ''),
            'password="'.($connection['password'] ?? '').'"',
            '',
        ]));

        File::chmod($path, 0600);

        return $path;
    }

    /**
     * The binary to run: the configured path, then Laragon's on Windows, then
     * the bare name for a server that has it on the PATH.
     */
    private function binary(string $name): string
    {
        $configured = config('backup.'.$name);

        if ($configured && is_file($configured)) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $found = glob('C:\\laragon\\bin\\mysql\\*\\bin\\'.$name.'.exe');

            if ($found) {
                return $found[0];
            }
        }

        return $name;
    }

    /**
     * Variables that the MySQL binaries need on Windows.
     *
     * Under the CLI they are inherited and nobody notices, but the environment
     * a web server hands to PHP can arrive without them, and then winsock dies
     * with "Can't create TCP/IP socket (10106)" before it ever connects.
     *
     * @return array<string, string>
     */
    private function environment(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        return [
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'SystemDrive' => getenv('SystemDrive') ?: 'C:',
            'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
            'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function connection(): array
    {
        return config('database.connections.'.config('database.default'));
    }

    private function database(): string
    {
        return $this->connection()['database'];
    }

    /**
     * @return list<string>
     */
    private function directories(): array
    {
        return config('backup.directories');
    }

    private function diskPath(string $path): string
    {
        return Storage::disk('public')->path($path);
    }

    /**
     * tempnam() cannot reserve a name with an extension, so the file it creates
     * is deleted and the name with the extension is the one that is kept.
     */
    private function tempFile(string $prefix, string $extension): string
    {
        $reserved = tempnam(sys_get_temp_dir(), $prefix);

        File::delete($reserved);
        File::put($reserved.$extension, '');

        return $reserved.$extension;
    }

    /**
     * The last line of the process output. mysql repeats the whole command on
     * failure and only the tail says what actually went wrong.
     */
    private function reason(string $output): string
    {
        $lines = array_filter(array_map('trim', explode("\n", $output)));

        return $lines === [] ? 'sin detalle' : (string) array_pop($lines);
    }
}
