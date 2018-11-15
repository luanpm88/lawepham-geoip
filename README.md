# Development environment

Add the following to the main `composer.json`

```json
"repositories": [
    {
        "type": "path",
        "version": "dev-master",
        "url": "/home/dev/lawepham-geoip"
    }
],
"require": [
    "louisitvn/log-viewer": "dev-master",
]

```

Then add the service to config/app.php

Then 
```sh
php artisan config:cache
```

# Production environment

Add the following to the main `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/luanpm88/lawepham-geoip"
    }
],
"require": [
    "louisitvn/log-viewer": "1.0",
]

```

Then add the service to config/app.php

Then
```sh
php artisan config:cache
```

