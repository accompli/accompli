# LocalConnectionAdapter
The local connection adapter provides support for deploying to the same machine you are running Accompli from.

## Connection options
This adapter has no specific connection options.

## Configuring the connection adapter
The following host configuration shows a configuration for the local connection adapter:

``` json
{
    "$extend": "accompli://recipe/defaults.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "local",
            "hostname": "example.com",
            "path": "/var/www/example.com"
        }
    ]
}
```
