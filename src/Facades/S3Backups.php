<?php

namespace Galatanovidiu\S3Backups\Facades;

use Illuminate\Support\Facades\Facade;

class S3Backups extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 's3-backups';
    }
}
