# SSHAgentTask

Initializes a SSH agent on the (remote) host and adds the configured private keys. This task is useful when you need to authenticate to clone your project repository.

**WARNING:** The SSH private keys currently need to be added to your accompli.json unencrypted. When you do so, **do not** add the configuration to a public repository.
Accompli will provide features to encrypt credentials in a future release. See issue [#11](https://github.com/accompli/accompli/issues/11).

# Configuration options

| Name | Type | Default value | Description |
|------|------|---------------|-------------|
| keys | string[] |  | An array with private keys to be added to the SSH agent. |

# Event flow
![Flowchart with highlighted events SSHAgentTask is listening to](../images/event-flows/SSHAgentTask.png)
