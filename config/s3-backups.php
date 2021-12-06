<?php

return [
    'local_folder' => env('LOCAL_BACKUPS_FOLDER') ?? '',
    'date_format' => env('LOCAL_BACKUPS_FOLDER_DATE_FORMAT') ?? 'Y-m-d H:i:s',
    'date_format_match' => env('LOCAL_BACKUPS_FOLDER_DATE_MATCH') ?? '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/i',
    's3-directory' => env('LOCAL_BACKUPS_S3_FOLDER') ?? 'backups',
    'check_backup_days' => (int) env('LOCAL_CHECK_BACKUPS_DAYS') ?? 7,

    'glacier_vault' => env('GLACIER_S3_VAULT') ?? 'backups',
];
