<?php

namespace Galatanovidiu\S3Backups;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class S3Backups
{

    public function doBackup()
    {

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

        // TODO: translate date to match pattern
        $match_pattern = '';
        if(config('s3-backups.date_format') == 'm-d-y'){
            $match_pattern = '/\d{2}-\d{2}-\d{2}/i';
        }

        preg_match($match_pattern, $folder_basename, $matched_date);

        return Carbon::createFromFormat( config('s3-backups.date_format'), $matched_date[0]); // ! is used to return the time as 00:00:00
    }

    public function uploadFilesToS3(array $should_be_on_s3) :void
    {
        foreach ($should_be_on_s3 as $s3s) {
            $all_files = File::allFiles($s3s['dir']);
            echo "Backup directory {$s3s['dir']}\n";
            foreach ($all_files as $f) {
                $file_path = $f->getPathname();
                $s3filename = config('s3-backups.s3-directory') . '/' . substr($file_path, strlen(config('s3-backups.local_folder')));
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

        $should_be_on_s3 = [];
        foreach ($local_folders as $local_folder) {
            if ($local_folder['date']->gte($oldest_backup)) {
                $should_be_on_s3[] = $local_folder;
            }
        }
        return $should_be_on_s3;
    }

}
