# FilePermissionTask

Updates the permissions of the configured files and directories.

## Configuration options

| Name | Type | Default value | Description |
|------|------|---------------|-------------|
| paths | array |  | The paths for which you want to update the permissions. |
| recursive | boolean | false | Set the same permissions in the subdirectories of the configured path. |
| permissions | string | | Configure the desired permissions for the owner, group and other users. For example: "-rwxrwx---" |
