# LinkTask

Creates symlinks to the configured directories.

## Configuration options

| Name | Type | Default value | Description |
|------|------|---------------|-------------|
| links | array |  | The links to create. |

### Configure the permissions for directories
Example configuration:
```json
{
    "class": "Accompli\\Task\\LinkTask",
    "links": {
        "location/within/release": "../../../data/%stage%/target/to/point/to",
        "another/location/within/release": "%data%/another/target/to/point/to"
    }
}
```

Make sure that the targets configured exist.
The option otherDirectories in [CreateWorkspace](CreateWorkspaceTask.md) task can help with that.

### Variables
There are some predefined variables that are replaced before targeting the link.  
These are:

| Variable | Description |
|----------|-------------|
| %data% | The data directory |
| %stage% | The stage currently being installed to |
| %version% | The version currently being installed |

# Event flow
![Flowchart with highlighted events the LinkTask is listening to](../images/event-flows/LinkTask.png)
