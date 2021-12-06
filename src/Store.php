<?php

namespace Galatanovidiu\S3Backups;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Store
{

    private array $store;
    private string $file_path;

    public function __construct(
        public string $store_name
    )
    {
        $file_name = Str::snake($this->store_name);
        $this->file_path = storage_path('framework/store/' . $file_name . '.json');
        if(File::exists($this->file_path)) {
            $this->store = json_decode(File::get($this->file_path), true);
        }else{
            $this->store = [];
        }
    }

    public function exists($key)
    {
        return isset($this->store[$key]);
    }

    public function get($key, $default_value = null)
    {
        return $this->store[$key] ?? $default_value;
    }

    public function set($key, $value)
    {
        return $this->store[$key] = $value;
    }

    public function save()
    {
        if(!file_exists($this->file_path)){
            if(!file_exists(dirname($this->file_path))){
                mkdir(dirname($this->file_path));
            }
        }
        File::put($this->file_path, json_encode($this->store));
    }


}
