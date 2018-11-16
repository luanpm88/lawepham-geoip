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
    "lawepham/geoip": "dev-master",
]

```
Then
```sh
rm -fr vendor/lawepham
php composer.phar require lawepham/geoip "dev-master"
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
    "lawepham/geoip": "1.0",
]

```
Then
```sh
rm -fr vendor/lawepham
php composer.phar require lawepham/geoip "1.0"
```

