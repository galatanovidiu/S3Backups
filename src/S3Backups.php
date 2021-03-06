<?php

namespace Galatanovidiu\S3Backups;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;


class S3Backups
{

    public function doBackup()
    {

        $store = new \Galatanovidiu\S3Backups\Store('backups_data');

        $last_backup = $store->get('last_backup');

        $backup_file = (new FilesManager(config('s3-backups.local_folder')))->lastArchive();

        if ($backup_file['filename'] != $last_backup) {
            $this->saveToS3GlacierStorageClass($backup_file['file']);
        }
    }

    public function saveToS3GlacierStorageClass($file)
    {
        $response = Storage::disk('s3')->putFileAs(env('AWS_S3_FOLDER'), $file, basename($file) , [
            'StorageClass' => 'GLACIER'
        ]);

        ScreenLog::log($response);

        $backups_data = new \Galatanovidiu\S3Backups\Store('backups_data');
        $backups_data->set('last_backup', basename($file));
        $backups_data->save();
    }

}
