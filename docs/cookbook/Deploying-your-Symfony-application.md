# Deploying your Symfony application
This guide provides you with the basic steps to install and deploy a Symfony application from a public or private VCS repository.

1. [Creating an Accompli configuration file](#1-creating-an-accompli-configuration-file).
2. [Adding SSH authentication for access to private repositories](#2-adding-ssh-authentication-for-access-to-private-repositories).
3. [Adding authentication to Composer dependency installation](#3-adding-authentication-to-composer-dependency-installation).
4. [Additional tasks (eg. database migrations)](#4-additional-tasks-eg-database-migrations).


## 1. Creating an Accompli configuration file
See the [configuration](../02-Configuration.md) documentation on how to create a basic `accompli.json` configuration file.

Accompli provides the following recipes to ease the configuration of a Symfony project deployment:

* [Symfony](../../src/Resources/recipe/symfony.json).
* [Symfony with assets](../../src/Resources/recipe/symfony-with-assets.json).

A minimal Accompli configuration for a Symfony project could look like this:

``` json
{
    "$extend": "accompli://recipe/symfony-with-assets.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "my-symfony-project.com",
            "path": "/var/www/my-symfony-project"
        }
    ],
    "events": {
        "subscribers": [
            {
                "class": "Accompli\\Task\\ExecuteCommandTask",
                "events": ["accompli.prepare_release"],
                "command": "ssh-keyscan github.com >> ~/.ssh/known_hosts"
            },
            {
                "class": "Accompli\\Task\\RepositoryCheckoutTask",
                "repositoryUrl": "git@github.com:my-username/my-symfony-project.git"
            },
            {
                "class": "Accompli\\Task\\ComposerInstallTask"
            },
            {
                "class": "Accompli\\Task\\YamlConfigurationTask",
                "configurationFile": "/app/config/parameters.yml",
                "configuration": {
                    "parameters": {
                        "database_name": "My_database_%stage%",
                        "database_password": "MyDatabasePassword"
                    }
                },
                "generateValueForParameters": [
                    "parameters.secret"
                ]
            },
            {
                "class": "Accompli\\Task\\DeployReleaseTask"
            }
        ]
    }
}
```


## 2. Adding SSH authentication for access to private repositories
The [`SSHAgentTask`](../tasks/SSHAgentTask.md) provides the functionality to create an SSH agent and add private keys on a (remote) host.

See the following documentation to create and configure SSH deployment keys:

* [GitHub - Generating an SSH key](https://help.github.com/articles/generating-an-ssh-key/).
* [GitHub - Deploy keys](https://developer.github.com/guides/managing-deploy-keys/#deploy-keys).
* [GitLab - Deploy keys](http://doc.gitlab.com/ce/ssh/README.html#deploy-keys).

The generated private key can then be added to the `SSHAgentTask` in your Accompli configuration:

``` json
            {
                "class": "Accompli\\Task\\SSHAgentTask",
                "keys": [
                    "YOUR\nPRIVATE\nKEY\n"
                ]
            }
```

The whitelines in the generated private key should be replaced by ```\n```.

**Warning!** Don't add private keys to public repositories.
The next release of Accompli will feature a credentials store which will make this possible without exposing your keys and passwords.


## 3. Adding authentication to Composer dependency installation
When you require authentication for Composer, when installing packages from private servers or reaching the GitHub rate-limit,
you can add an authentication configuration to the [`ComposerInstallTask`](../tasks/ComposerInstallTask.md).

The following is an example of how to configure an GitHub OAuth token:

``` json
            {
                "class": "Accompli\\Task\\ComposerInstallTask",
                "authentication": {
                    "github-oauth": {
                        "github.com": "your-oauth-token"
                    }
                }
            }
```


## 4. Additional tasks (eg. database migrations)
Additional tasks, like Doctrine migrations or other `bin/console` commands, can be executed through the [`ExecuteCommandTask`](../tasks/ExecuteCommandTask.md).

Doctrine database schema update example:
``` json
            {
                "class": "Accompli\\Task\\ExecuteCommandTask",
                "events": ["accompli.deploy_release"],
                "command": "php bin/console doctrine:schema:update --env=prod --force"
            }
```


Déploiement heureux! / Happy deploying!
