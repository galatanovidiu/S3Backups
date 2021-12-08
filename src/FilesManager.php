<?php

namespace Galatanovidiu\S3Backups;


use Carbon\Carbon;

class FilesManager
{

    private $folder_files;
    private $folder;

    public function __construct( $folder )
    {
        $this->folder = $folder;
        if(!file_exists($this->folder) or !is_dir($this->folder)){
            throw new \Exception($this->folder . ' does not exists or is not a folder');
        }

        // add trailing slash
        if(substr($this->folder, -1) != '/'){
            $this->folder = $this->folder . '/';
        }

        $this->folder_files = $this->folderFiles();
        $this->folder = $folder;
    }

    /*
     * array[file]      array File path
     * array[filename]  array File name
     * array[type]      array File type: dir of file
     * array[timestamp] array File unix timestamp
     * array[date]      array File date
     * */
    public function folderFiles() :array
    {
        $files = scandir($this->folder);
        $sorted_folder_files = [];
        foreach($files as $file){
            if($file != '.' and $file != '..' and $file != '.DS_Store'){
                $stat = stat($this->folder . $file);
                $sorted_folder_files[] = [
                    'file' => $this->folder . $file,
                    'filename' => $file,
                    'type' => is_dir($this->folder . $file) ? 'dir' : 'file',
                    'timestamp' => $stat['mtime'],
                    'date' => date('Y-m-d H:i:s', $stat['mtime']),
                ];
            }
        }

        // sort by modification date
        usort($sorted_folder_files, fn($a, $b) => ($a['timestamp'] > $b['timestamp']) ? -1 : 1);

        if(count($sorted_folder_files) == 0){
            throw new \Exception($this->folder . ' is empty');
        }

        return $sorted_folder_files;
    }

    public function newestFile()
    {
        return $this->folder_files[0];
    }

    public function lastArchive()
    {
        $newest_file = $this->newestFile();
        if(is_dir($newest_file['file'])){
            // Create archive
            $newest_file_zip = (new ZipFolder($newest_file['file']))->zip();
            $newest_file = [
                'file' => $newest_file_zip,
                'filename' => basename($newest_file_zip),
                'type' => 'file',
                'timestamp' => time(),
                'date' => Carbon::now()->toDateTimeString(),
            ];
        }

        return $newest_file;
    }

}
