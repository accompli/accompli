# SSHConnectionAdapter
The SSH connection adapter provides support for connecting and deploying to a host through the SSH protocol.

## Connection options
| Name                      | Type   | Default value        | Description                                                   |
|---------------------------|--------|----------------------|---------------------------------------------------------------|
| authenticationType        | string | publickey            | The type of authentication with the server. Possible values are: password, publickey and agent. |
| authenticationUsername    | string | The current username | The username to connect to the remote host.                                                     |
| authenticationCredentials | string |                      | The credentials required to connect to the remote host. This option expects a different value depending on 'authenticationType'. See the explained options below. |


## Configuring the connection adapter
The SSHConnectionAdapter has three different types of authenticating with a remote host.
The following examples show the different types and how to configure them.


### Public key authentication
Public/private key authentication is the default authentication type.
Without the `connectionOptions` key set it will by default try to authenticate using the private key located at `~/.ssh/id_rsa`.

When your private key has a different name, it can be configured by defining it's location in `authenticationCredentials`:

``` json
{
    "$extend": "accompli://recipe/defaults.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "example.com",
            "path": "/var/www/example.com",
            "connectionOptions": {
                "authenticationType": "publickey",
                "authenticationCredentials": "~/.ssh/another_id_rsa"
            }
        }
    ]
}
```

Optionally, you can authenticate with a different username by configuring `authenticationUsername`.


### Username and password
With username and password authentication you configure the password in the `authenticationCredentials` key:

``` json
{
    "$extend": "accompli://recipe/defaults.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "example.com",
            "path": "/var/www/example.com",
            "connectionOptions": {
                "authenticationType": "password",
                "authenticationUsername": "anotheruser",
                "authenticationCredentials": "mySecretPassword"
            }
        }
    ]
}
```

Please note that this method is considered unsafe as you're exposing your password inside the Accompli configuration.
In an upcoming version of Accompli you will be able to store credentials inside a credentials store.


### Local SSH agent
Besides the public/private key authentication, you can also authenticate using public/private keys through a local SSH agent.

To use a local SSH agent you configure 'agent' within the `authenticationType` key:
``` json
{
    "$extend": "accompli://recipe/defaults.json",
    "hosts": [
        {
            "stage": "test",
            "connectionType": "ssh",
            "hostname": "example.com",
            "path": "/var/www/example.com",
            "connectionOptions": {
                "authenticationType": "agent"
            }
        }
    ]
}
```

#### Initializing a local SSH agent
To initialize a local SSH agent you need to execute the following command:
```
$ eval $(ssh-agent)
```

This will start a SSH agent daemon and create the environment variables required by Accompli (and other programs) to detect and use the agent.

Adding SSH keys to the SSH agent is done by executing:
```
$ ssh-add <location-of-ssh-key>
```
