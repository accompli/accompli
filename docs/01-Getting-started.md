# Getting started

Accompli is a tool to automate deployment of (PHP) projects. It allows you to easily configure (and create) a set of tasks
and run the task when deploying a release of your project.

We aim to provide support for [Testing-Acceptance-Production][link-wikipedia-dtap], [Continuous Delivery][link-wikipedia-continuous-delivery] and [Continuous Deployment][link-wikipedia-continuous-delivery] concepts.

## Why the name 'Accompli'?

Accompli is the French word for 'accomplished' or 'finished'. This fits well with deploying your project, as it is most likely to be done.
The French originates back to when Accompli was mainly meant to be a deployment tool for Symfony Framework projects which is created by SensioLabs, a French company.

## System Requirements

Accompli requires PHP 5.4.0+ to run.

The following extensions are suggested to speed up SSH related tasks:
* mcrypt
* openssl

In order to install releases from version control, you'll need to have git or subversion installed depending on how your project is version-controlled.

Accompli is multi-platform and we strive to make it run equally well on Windows, Linux and OSX.

## Installation using Composer

Run the following command to add the package to the composer.json of your project:

``` bash
$ composer require accompli/accompli --dev
```

## Using Accompli

In order for Accompli to run your project requires an accompli.json file in the root of the project. This will contain the configured hosts to deploy to and the tasks to run during deployment of a release.

An example accompli.json:
``` json
{
  "$extend": "accompli://recipe/defaults.json",
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

By running the following command, Accompli will guide you in creating a basic accompli.json configuration:
``` bash
$ vendor/bin/accompli init
```

For more detailed information on how to configure your accompli.json, please see the [configuration](02-Configuration.md) documentation.

See the [tasks](03-Tasks.md) documentation to learn what tasks are provided by Accompli and how to create your own tasks.

### Install a release for deployment
To install a release for deployment you'll need to run the following command from your project root:

``` bash
$ vendor/bin/accompli install-release <version>
```

Accompli will then run the configured installation tasks for all configured hosts.

Optionally, you can install a release on hosts with a specific stage configured:

``` bash
$ vendor/bin/accompli install-release <version> <stage>
```

### Deploy a release

To deploy a previously install release you'll need to run the following command from your project root:

``` bash
$ vendor/bin/accompli deploy-release <version> <stage>
```

During a deployment Accompli is able to determine whether the deployment is a rollback to a previous version or an increment to a new version.

*C'est fini! Accompli!*


[link-wikipedia-dtap]: https://en.wikipedia.org/wiki/Development,_testing,_acceptance_and_production
[link-wikipedia-continuous-delivery]: https://en.wikipedia.org/wiki/Continuous_delivery
[link-wikipedia-continuous-deployment]: https://en.wikipedia.org/wiki/Continuous_deployment
