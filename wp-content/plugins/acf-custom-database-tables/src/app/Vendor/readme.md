These are third-party packages that, in a normal PHP app, we would pull in via Composer and get on with our day. In 
WordPress, however, we need to pull these in and namespace them to prevent possible conflicts between plugins. 

Aside from namespaces, all code in here should remain unchanged. If we come across a case where we need to modify a 
package, we should just fork it and maintain our own copy that we can then pull in and namespace to suit this plugin's 
context. 

## Current versions

| Name | Version | Notes |
|---|---|---|
| Mishterk\WP\Tools\DB | 0.1.0 | Table creation utility |
| Pimple | 3.2.3 | Dependency injection container |
| Psr\Container | 1.0.0 | Container interfaces required by Pimple |