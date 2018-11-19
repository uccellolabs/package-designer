# Package Designer

This package adds a command to ```php artisan``` to generate new packages.

## Installation

Via Composer

``` bash
$ composer require uccello/package-designer
```

## Make a new package

To make a new package just run:

``` bash
$ php artisan make:package [name] # name = <vendor>/<package>
```

After asking you some information, this command will create a new package into ```packages/vendor/package``` folder.

## Install the new package
To install the new package just run:

``` bash
$ composer require <vendor>/<package>
```

## Security

If you discover any security related issues, please email jonathan@uccellolabs.com instead of using the issue tracker.

## Credits

- [Uccello Labs][link-organization]
- [Jonathan SARDO][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT).

[link-organization]: https://github.com/uccellolabs
[link-uccello]: https://github.com/uccellolabs/uccello
[link-author]: https://github.com/sardoj
[link-contributors]: ../../contributors
