<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupSQLiteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sqlite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup SQLite database and upload to Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqliteFilePath = database_path('database.sqlite');

        // Local backup destination path (optional for local backup before upload)
        $localBackupFilePath = storage_path('app/backups/database_' . now()->format('Y-m-d_H-i-s') . '.sqlite');

        // Cloudflare R2 backup path (in the bucket)
        $r2BackupFilePath = 'backups/database_' . now()->format('Y-m-d_H-i-s') . '.sqlite';

        // Check if the SQLite database file exists
        if (file_exists($sqliteFilePath)) {
            // Step 1: Copy the SQLite file locally (optional)
            if (copy($sqliteFilePath, $localBackupFilePath)) {

                $this->info('Local backup completed successfully: ' . $localBackupFilePath);
            } else {

                $this->error('Failed to copy the SQLite database locally.');

                return;
            }

            // Step 2: Upload the file to Cloudflare R2
            $fileStream = fopen($localBackupFilePath, 'r');  // Open the file for reading

            if (Storage::disk('r2')->put($r2BackupFilePath, $fileStream)) {
                $this->info('Backup uploaded to Cloudflare R2 successfully: ' . $r2BackupFilePath);
            } else {
                $this->error('Failed to upload the backup to Cloudflare R2.');
            }

            // delete  localBackupFilePath

            $this->info('Deleting local backup file: ' . $localBackupFilePath);

            unlink($localBackupFilePath);

            fclose($fileStream);  // Close the file stream
        } else {

            $this->error('SQLite database file not found.');
        }

    }
}
