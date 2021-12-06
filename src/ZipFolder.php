<?php

namespace Galatanovidiu\S3Backups;

class ZipFolder
{

    private $zip_file_name;
    private $folder_name;
    private $parent_folder;

    public function __construct(
        public string $folder
    )
    {
        $this->folderName();
        $this->parentFolder();
        $this->zipFileName();
    }

    public function zip() :string
    {
        $zip = new \ZipArchive();
        $zip->open($this->zip_file_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->folder));

        foreach ($files as $name => $file)
        {
            // We're skipping all subfolders
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();

                // extracting filename with substr/strlen
                $relativePath = $this->folder_name .'/' . substr($filePath, strlen($this->folder) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        return $this->zip_file_name;
    }

    private function folderName(): void
    {
        $this->folder_name = basename($this->folder);
    }
    private function parentFolder(): void
    {
        $this->parent_folder = dirname($this->folder);
    }
    private function zipFileName() :void
    {
        $this->zip_file_name = $this->parent_folder .'/'. $this->folder_name . '.zip';
    }
}
