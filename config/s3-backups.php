<?php

return [
    'local_folder' => env('LOCAL_BACKUPS_FOLDER') ?? '',
    'date_format' => env('LOCAL_BACKUPS_FOLDER_DATE_FORMAT') ?? 'Y-m-d H:i:s',
    's3-directory' => env('LOCAL_BACKUPS_S3_FOLDER') ?? 'backups',
    'check_backup_days' => (int) env('LOCAL_CHECK_BACKUPS_DAYS') ?? 7,
];
