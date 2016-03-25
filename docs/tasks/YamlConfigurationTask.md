# YamlConfigurationTask

Creates or updates a YAML configuration file. When the same file with a .yml.dist extension is available the task will use that file as template.

## Generating unique SHA1 hash values for a key within the YAML configuration

To generate a unique SHA1 hash value for a key you need to specify that key in the 'generateValueForParameters' configuration option.
When the key is a child of another key you can use a dotted notation as lookup syntax.

For example:
``` yml
bar:
    baz: ef343a878da56b9be18eb1455ce35f052b421249
```

When you specify 'bar.baz' with 'generateValueForParameters' the task will generate a new unique SHA1 hash value.

# Configuration options

| Name | Type | Default value | Description |
|------|------|---------------|-------------|
| configurationFile | string |  | The location of the YAML configuration file within the release. |
| configuration | array |  | The configuration to be added to the YAML configuration file. |
| generateValueForParameters | string[] |  | An array with configuration keys that require generation of a SHA1 hash value. |

# Event flow
![Flowchart with highlighted events YamlConfigurationTask is listening to](../images/event-flows/YamlConfigurationTask.png)
