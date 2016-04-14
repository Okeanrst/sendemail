# Sendemail
Simple form for sending emails, sending emails in html format, with the possibility of embedding an unlimited number of files. Implemented WYSIWYG editor on the basic level.

## Note

To increase the number of uploaded files, edit the file .htaccess or php.ini. For NGINX with PHP-FPM you need to determinate appropriate fastcgi_param. For example:
	
``` bash
fastcgi_param PHP_VALUE max_file_uploads=100;
fastcgi_param PHP_VALUE max_input_time=-1;
```
## Install

Use command:

``` bash
$ git clone https://github.com/Okeanrst/sendemail
```

 or download ZIP-archive, unzip it and run

``` bash
$ composer update
```

In file handler.php change email.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
