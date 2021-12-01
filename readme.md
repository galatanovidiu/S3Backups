# S3Backups

This is simple Laravel package that will copy backups folders on AWS S3. It will only keep the folders from the last month 

## Installation

``` bash
$ composer require galatanovidiu/s3-backups
```

## Usage

Add to your `.env` file:

```dotenv
LOCAL_BACKUPS_FOLDER='Full path to the folder that contains the backups: /backus/...'
LOCAL_BACKUPS_FOLDER_DATE_FORMAT='Folder date format: !m-d-y...'
LOCAL_BACKUPS_S3_FOLDER='the folder inside the bucket where to store the backups'
LOCAL_CHECK_BACKUPS_DAYS='number of days to very the backups'
```

## Change log


## Testing

``` bash
$ composer test
```

## Contributing


## Security

If you discover any security related issues, please email galatanovidiu@gmail.com instead of using the issue tracker.

## Credits

- [Galatan Ovidiu](https://rogio.com)

## License

MIT. 

