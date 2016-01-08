# Connection adapters

This chapter describes the available adapters and their specific options.

## Available connection adapters

Accompli provides the following connection adapters:

### LocalConnectionAdapter

The local connection adapter provides support for deploying to the same machine as you are running Accompli from.

#### Connection options

This adapter has no specific connection options.

### SSHConnectionAdapter

The SSH connection adapter provides support for connecting and deploying to a host through the SSH protocol.

#### Connection options

* authenticationType - password, publickey, agent (default is 'publickey').
* authenticationUsername - (default is the username used to run Accompli).
* authenticationCredentials - Expects different value depending on 'authenticationType'.

## How to create your own connection adapter

All connection adapters must implement the `Accompli\Deployment\Connection\ConnectionAdapterInterface`.

