# Accompli

[![Latest version on Packagist][ico-version]][link-version]
[![Latest pre-release version on Packagist][ico-pre-release-version]][link-version]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-build]][link-build]
[![Coverage Status][ico-coverage]][link-coverage]
[![SensioLabsInsight][ico-security]][link-security]
[![StyleCI][ico-code-style]][link-code-style]

An easy to use and extendable deployment tool for PHP projects.

> *C'est fini. Accompli!*

## Installation using Composer

Run the following command to add the package to the composer.json of your project:

``` bash
$ composer require accompli/accompli:dev-master --dev
```

#### Versioning
Accompli uses [Semantic Versioning 2](http://semver.org/) for new versions.

## Usage
1. Create an accompli.json defining the hosts to deploy to and the tasks to run during install and deployment.

  *Note that this example might not work for your project.*

  ``` json
  {
    "$extend": "vendor/accompli/accompli/src/Resources/accompli-defaults.json",
    "hosts": [
      {
        "stage": "test",
        "connectionType": "ssh",
        "hostname": "example.com",
        "path": "/var/www/example.com"
      }
    ],
    "events": {
      "subscribers": [
        {
          "class": "Accompli\\Task\\CreateWorkspaceTask"
        },
        {
          "class": "Accompli\\Task\\RepositoryCheckoutTask",
          "repositoryUrl": "https://github.com/example.com/example.com.git"
        },
        {
          "class": "Accompli\\Task\\DeployReleaseTask"
        },
        {
          "class": "Accompli\\Task\\MaintenanceModeTask"
        }
      ]
    }
  }
  ```

2. Run Accompli to install a release of your project: `vendor/bin/accompli install-release <version>`

3. Run Accompli to deploy an installed release of your project: `vendor/bin/accompli deploy-release <version> <stage>`

For a more detailed description on how to use Accompli, please see the [getting started][link-documentation] page.

## Credits and acknowledgements

- [Niels Nijens][link-author]
- [Reyo Stallenberg][link-author-name] \(creator of the name 'Accompli'\)

Also see the list of [contributors][link-contributors] who participated in this project.

## License

Accompli is licensed under the MIT License. Please see the [LICENSE file](LICENSE.md) for details.

[ico-version]: https://img.shields.io/packagist/v/accompli/accompli.svg
[ico-pre-release-version]: https://img.shields.io/packagist/vpre/accompli/accompli.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-build]: https://travis-ci.org/accompli/accompli.svg?branch=master
[ico-coverage]: https://coveralls.io/repos/accompli/accompli/badge.svg?branch=master
[ico-security]: https://img.shields.io/sensiolabs/i/5b884e85-bb11-4847-b212-e3aaace39a26.svg
[ico-code-style]: https://styleci.io/repos/32416744/shield?style=flat

[link-version]: https://packagist.org/packages/accompli/accompli
[link-build]: https://travis-ci.org/accompli/accompli
[link-coverage]: https://coveralls.io/r/accompli/accompli?branch=master
[link-security]: https://insight.sensiolabs.com/projects/5b884e85-bb11-4847-b212-e3aaace39a26
[link-code-style]: https://styleci.io/repos/32416744
[link-documentation]: https://github.com/accompli/accompli/docs/Getting-started.md
[link-author]: https://github.com/niels-nijens
[link-author-name]: https://github.com/reyostallenberg
[link-contributors]: https://github.com/accompli/accompli/contributors

