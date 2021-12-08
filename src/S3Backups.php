<?php

namespace Galatanovidiu\S3Backups;

use Aws\Glacier\GlacierClient;
use Aws\Glacier\MultipartUploader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class S3Backups
{

    public function doBackup()
    {

        $store = new \Galatanovidiu\S3Backups\Store('backups_data');

        $last_backup = $store->get('last_backup');

        $backup_file = (new FilesManager(config('s3-backups.local_folder')))->lastArchive();

        if($backup_file['filename'] != $last_backup){
            $this->save_on_glacier($backup_file['file']);
        }
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

        $result = new MultipartUploader($client, $file, [
            'vault_name' => env('GLACIER_S3_VAULT'),
        ]);

//        $result = $client->uploadArchive(array(
//            'vaultName' => env('GLACIER_S3_VAULT'),
//            'body'      => fopen($file, 'r'),
//        ));

        $store = new \Galatanovidiu\S3Backups\Store('backups_log');
        $store->set(Carbon::now()->toDateTimeString(), $result);
        $store->save();

        $store = new \Galatanovidiu\S3Backups\Store('backups_data');
        $store->set('last_backup', basename($file));
        $store->save();

        return $result;
    }


}
