<?php

return [
    'local_folder' => env('LOCAL_BACKUPS_FOLDER') ?? '',
    'glacier_vault' => env('GLACIER_S3_VAULT') ?? 'backups',
];
