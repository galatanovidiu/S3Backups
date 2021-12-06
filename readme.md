# S3 Glacier Backups

This is simple Laravel package that will add the newest backup inside a folder to S3 glacier. If the latest element inside the backups folder is a folder a zip file will be created and that zip file is uploaded to S3 Glacier.

## Installation

``` bash
$ composer require galatanovidiu/s3-backups
```

## Usage

Add to your `.env` file:

```dotenv
LOCAL_BACKUPS_FOLDER='Full path to the folder that contains the backups: /backus/...'
GLACIER_S3_VAULT='valut name'
```


## License

MIT. 

