# ComposerInstallTask

Installs the Composer binary into the workspace and executes 'composer install'.

## Configuration options

| Name | Type | Default value | Description |
|------|------|---------------|-------------|
| authentication | array |  | The authentication configuration used by Composer. See [HTTP basic authentication](https://getcomposer.org/doc/articles/http-basic-authentication.md). |

## Event flow
![Flowchart with highlighted events ComposerInstallTask is listening to](../images/event-flows/ComposerInstallTask.png)
