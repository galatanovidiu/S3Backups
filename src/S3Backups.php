<?php

namespace Galatanovidiu\S3Backups;

use Aws\Glacier\GlacierClient;
use Aws\Glacier\TreeHash;
use Illuminate\Support\Carbon;


class S3Backups
{

    public function doBackup()
    {

        $store = new \Galatanovidiu\S3Backups\Store('backups_data');

        $last_backup = $store->get('last_backup');

        $backup_file = (new FilesManager(config('s3-backups.local_folder')))->lastArchive();

        if ($backup_file['filename'] != $last_backup) {
            $this->save_on_glacier($backup_file['file']);
        }
    }


    public function save_on_glacier($file)
    {
        $chunkSize = 16777216; //16Mb
        $fileSize = filesize($file);
        $vaultName = env('GLACIER_S3_VAULT');

        $glacier = GlacierClient::factory(array(
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ));

        $initiate_multipart = $glacier->initiateMultipartUpload([
            'archiveDescription' => 'Backup upload multipart: ' . $file,
            'vaultName' => $vaultName,
            'partSize' => $chunkSize
        ]);

        $uploadId = $initiate_multipart['uploadId'];

        // we need to generate the SHA256 tree hash
        // open the file so we can get a hash from its contents
        $fp = fopen($file, 'r');
        // This class can generate the hash
        $th = new TreeHash();
        // feed in all of the data
        $th->update(fread($fp, $fileSize));
        // generate the hash (this comes out as binary data)...
        $hash = $th->complete();
        // but the API needs hex (thanks). PHP to the rescue!
        $hash = bin2hex($hash);

        // reset the file position indicator
        fseek($fp, 0);

        // the part counter
        $partNumber = 0;

        ScreenLog::log("Uploading: '" . $file
            . "' (" . $fileSize . " bytes) in "
            . (ceil($fileSize / $chunkSize)) . " parts...\n");

        while ($partNumber * $chunkSize < ($fileSize + 1)) {
            // while we haven't written everything out yet
            // figure out the offset for the first and last byte of this chunk
            $firstByte = $partNumber * $chunkSize;
            // the last byte for this piece is either the last byte in this chunk, or
            // the end of the file, whichever is less
            // (watch for those Obi-Wan errors)
            $lastByte = min((($partNumber + 1) * $chunkSize) - 1, $fileSize - 1);

            // upload the next piece
            $result = $glacier->uploadMultipartPart(array(
                'body' => fread($fp, $chunkSize),  // read the next chunk
                'uploadId' => $uploadId,          // the multipart upload this is for
                'vaultName' => $vaultName,
                'range' => 'bytes ' . $firstByte . '-' . $lastByte . '/*' // weird string
            ));

            // this is where one would check the results for error.
            // This is left as an exercise for the reader ;)

            // onto the next piece
            $partNumber++;
            ScreenLog::log("\tpart " . $partNumber . " uploaded...\n");
        }
        ScreenLog::log("...done\n");

        // and now we can close off this upload
        $result = $glacier->completeMultipartUpload(array(
            'archiveSize' => $fileSize,         // the total file size
            'uploadId' => $uploadId,            // the upload id
            'vaultName' => $vaultName,
            'checksum' => $hash                 // here is where we need the tree hash
        ));

        // this is where one would check the results for error.
        // This is left as an exercise for the reader ;)


        // get the archive id.
        // You will need this to refer to this upload in the future.
        $archiveId = $result->get('archiveId');

        ScreenLog::log("The archive Id is: " . $archiveId . "\n");


        $backups_log = new \Galatanovidiu\S3Backups\Store('backups_log');
        $backups_log->set(Carbon::now()->toDateTimeString(), ScreenLog::getMessages());
        $backups_log->save();

        $backups_data = new \Galatanovidiu\S3Backups\Store('backups_data');
        $backups_data->set('last_backup', basename($file));
        $backups_data->save();

        return $result;
    }


}
