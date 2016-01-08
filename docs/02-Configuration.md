# Configuration
This chapter will explain all the available sections of the accompli.json configuration. 

## Using a recipe
A recipe is a set of pre-configured tasks defined in a separate accompli.json file.

You can include a recipe by referencing it in the '$extend' key:
``` json
{
  "$extend": "vendor/accompli/accompli/src/Resources/accompli-defaults.json"
}
```

The path to the recipe can be relative to the location of the accompli.json in your project.

## Configuring a host

Hosts are configured in an array within the 'hosts' key.

A host object requires the following keys:
* stage - The deployment stage a host is part of (test, acceptance, production).
* connectionType - The identifier of a connection adapter to communicate with the host.
* hostname - The IP address or DNS hostname of the host.
* path - The absolute path to the workspace on the host.

``` json
{
  "hosts": [
    {
      "stage": "test",
      "connectionType": "ssh",
      "hostname": "example.com",
      "path": "/var/www/example.com"
    }
  ]
}
```

Optionally, a 'connectionOptions' key can be configured within a host object with an array of connection adapter specific configuration parameters.

For more information on the specific connection options, please see the [connection adapter](04-Connection-adapters.md) documentation.

## Configuring a task

Tasks are mostly configured as an event subscriber, but can also be configured as event listener for a specific event.

To configure a task as an event subscriber add your class to the 'events.subscribers' keys:

``` json
{
  "events": {
    "subscribers": [
      {
        "class": "My\\Namespaced\\Task"
      }
    ]
  }
}
```

If your task has constructor arguments to configure it's behavior, you're able to configure them next to the class name of the task:

``` json
{
  "events": {
    "subscribers": [
      {
        "class": "My\\Namespaced\\Task",
        "argument1": "value",
        "argument2": 2
      }
    ]
  }
}
```

For more information about tasks and a list of available Accompli tasks, see the [tasks](03-Tasks.md) documentation.

## Configuring the deployment strategy

A deployment strategy is configured in the 'deployment.strategy' keys by setting the class name:

``` json
{
  "deployment": {
    "strategy": "Accompli\\Deployment\\Strategy\\RemoteInstallStrategy"
  }
}
```

## Configuring connection adapters

Connection adapters are configured in the 'deployment.connection' keys with a unique identifier and the class name of the connection adapter:

``` json
{
  "deployment": {
    "connection": {
      "uniqueIdentifier": "My\\Namespaced\\ConnectionAdapter"
    }
  }
}
```

The identifier can then be used to configure the 'connectionType' key of a host.
