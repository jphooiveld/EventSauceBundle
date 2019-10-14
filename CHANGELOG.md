1.2
- Add snapshotting configuration.

1.1
- Breaking changes in service configuration because of changes to serialization in EventSaucse 0.6.0 (https://eventsauce.io/docs/upgrading/to-0-6-0).
- Breaking changes in database schema because of storing aggregate version in EventSaucse 0.7.0 (https://eventsauce.io/docs/upgrading/to-0-7-0)
- Autoconfiguration of aggregate repositories is no longer possible due to private constructor in AggregateRootBehaviour trait. 
You must specify a list of aggregates that you want to create a default repository for because the symfony service container can no longer find them.


1.0
- First release based on EventSaucse 0.5.0