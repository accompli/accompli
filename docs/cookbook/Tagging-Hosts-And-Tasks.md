# Tagging hosts and tasks
This guide provides you with the basic steps to tag hosts and tasks, resulting in tasks to be performed on specific hosts only.

1. [Creating an Accompli configuration file](#1-creating-an-accompli-configuration-file).
2. [Tag some hosts](#2-tag-some-hosts).
3. [Tag some tasks](#3-tag-some-tasks).

## 1. Creating an Accompli configuration file
See the [configuration](../02-Configuration.md) documentation on how to create a basic `accompli.json` configuration file.

An Accompli configuration for a project could look like this:

``` json
{
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "my-project.com",
            "path": "/var/www/my-project"
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
                "repositoryUrl": "git@github.com:my-username/my-project.git"
            },
            {
                "class": "Accompli\\Task\\ComposerInstallTask"
            },
            {
                "class": "Accompli\\Task\\DeployReleaseTask"
            }
        ]
    },
    "deployment": {
        "strategy": "Accompli\\Deployment\\Strategy\\RemoteInstallStrategy"
    }
}
```

## 2. Tag some hosts
Add a host with a tag, to indicate it's *special*.

``` json
{
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "my-project.com",
            "path": "/var/www/my-project"
        },
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "special.my-project.com",
            "path": "/var/www/my-project",
            "tags": ["special"]
        }
    ]
}
```

You can add as many tags as you like.

## 3. Tag some tasks
Now you can also add tasks (subscribers), and that task will be executed only on hosts with corresponding tags.

So, add you *special* task:

``` json
            {
                "class": "My\\Special\\SpecificTask",
                "tags": ["special"]
            }
```

You can add as many tags as you like. A tagged task will be executed on each host that matches at least one tag.