<?php

namespace Galatanovidiu\S3Backups;

use Aws\Glacier\GlacierClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class S3Backups
{

    public function doBackup()
    {

        $last_backup = Galatanovidiu\S3Backups\Store();

        $local_folders = $this->getBackupsFolders();
        $should_be_on_s3 = $this->files_that_should_be_on_s3($local_folders);
        $this->uploadFilesToS3($should_be_on_s3);
    }

    protected function getBackupsFolders(): array
    {
        $backup_dir_folders = File::directories(config('s3-backups.local_folder'));
        $folders = [];
        foreach ($backup_dir_folders as $backup_dir_folder) {
            $folder_basename = basename($backup_dir_folder);

            $folders[] = [
                'dir' => $backup_dir_folder . '/',
                'name' => $folder_basename,
                'date' => $this->folderDate($folder_basename)
            ];
        }

        return $folders;
    }

    protected function folderDate(string $folder_basename): \Carbon\Carbon
    {

        preg_match(config('s3-backups.date_format_match'), $folder_basename, $matched_date);

        return Carbon::createFromFormat( config('s3-backups.date_format'), $matched_date[0]); // ! is used to return the time as 00:00:00
    }

    public function uploadFilesToS3(array $should_be_on_s3) :void
    {
        foreach ($should_be_on_s3 as $s3s) {
            $all_files = File::allFiles($s3s['dir']);
            echo "Backup directory {$s3s['dir']}\n";
            foreach ($all_files as $f) {
                $file_path = $f->getPathname();
                $s3filename = config('s3-backups.s3-directory') . substr($file_path, strlen(config('s3-backups.local_folder')));
                if(!Storage::disk('s3')->exists($s3filename)){
                    Storage::disk('s3')->put($s3filename, $f);
                    echo "File '{$s3filename}' backed-up successfully\n";
                }else{
                    echo "File '{$s3filename}' is already on S3\n";
                }
            }
        }
    }

    public function files_that_should_be_on_s3(array $local_folders):array
    {
        $oldest_backup = Carbon::now()->subDays(config('s3-backups.check_backup_days'));

//        print_r($oldest_backup);

        $should_be_on_s3 = [];
        foreach ($local_folders as $local_folder) {
            if ($local_folder['date']->gte($oldest_backup)) {
                $should_be_on_s3[] = $local_folder;
            }
        }
        return $should_be_on_s3;
    }

    public function save_on_glacier($file)
    {
        $client = GlacierClient::factory(array(
            'version'     => 'latest',
            'region'      => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ));

        $result = $client->uploadArchive(array(
            'vaultName' => env('GLACIER_S3_VAULT'),
            'body'      => fopen($file, 'r'),
        ));

        return $result;
    }

    public function archive_folder($folder)
    {



        $zip_file = 'invoices.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = storage_path('invoices');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();

                // extracting filename with substr/strlen
                $relativePath = 'invoices/' . substr($filePath, strlen($path) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        return response()->download($zip_file);
    }

}
