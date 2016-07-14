# Connection adapters
The connection adapters are the 'middleman' that executes the actual commands, sent by the various configured tasks, on the (remote) server.
This allows Accompli to support multiple connection types.

Accompli provides the following connection adapters for connecting to a (remote) server:

* [LocalConnectionAdapter](connection-adapters/LocalConnectionAdapter.md)
* [SSHConnectionAdapter](connection-adapters/SSHConnectionAdapter.md)

## Creating your own connection adapter
All connection adapters must implement the `Accompli\Deployment\Connection\ConnectionAdapterInterface`.

### Configuring your custom connection adapter
You configure your custom connection adapter by adding a unique key and the FQCN of the connection adapter to the `accompli.json` of your project or within a recipe.

You can then use the unique key of the connection adapter to use as `connectionType` with a host configuration.

The following configuration shows an example on how to configure a custom connection adapter:
``` json
{
    "$extend": "accompli://recipe/defaults.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "custom",
            "hostname": "example.com",
            "path": "/var/www/example.com"
        }
    ],
    "deployment": {
        "connection": {
            "custom": "My\\Custom\\ConnectionAdapter"
        }
    }
}
```
