# Entity Redirect
## DESCRIPTION
Adds a configurable redirect after saving a node or other entity. The redirect
is configurable per bundle. Also, given sufficient permissions (and presuming it
is enabled for that specific content/bundle), individual users can configure
their own redirects (on their *profile edit page*).

Provides four different types of redirection:

- Default: This will not impact the entity but will just go to the default.
- Add Form: Redirect to a new add form for the content type/entity.
- Local Url: provide a local url in the form of `/about` to go to any page on
the site.
- External Url: Same as local url but to an external location. *Note*: this is
only available to users with the permission `set external entity redirects`.

You can also control whether this occurs only on saving a new entity or for both
creating and editing an entity.

*Note*: depending on permissions, the redirect will also occur for anonymous
users so if using the `Local Url` option make sure they have permission to
access the destination if they can add/edit the content type/entity. This is a
relatively rare site configuration so in most cases you can safely ignore that.

##  MOTIVATION/USE CASES
Sometimes the best workflow is to add a lot of entities in a row, so you want to
return directly to the `add entity` form after each one. Another use case is
taking users to a thank-you page after contributing something.

## REQUIREMENTS
Drupal 8 or 9 is required - Drupal 9 suggested.

## INSTALLATION
Install as you would normally install a contributed Drupal module. See the
[Drupal 8 module install instructions](https://drupal.org/documentation/install/modules-themes/modules-8)
if required.

## CONFIGURATION
Configuration can be accessed for each supported entity bundle on the edit page
for that entity type. For example for the Node type Article that would be at
`/admin/structure/types/manage/article`. The configuration will be in the
publishing options section if available.

With sufficient permissions' per user personalization can done on the users'
profile edit pages.

## FAQ
Any questions? Ask away on the issue queue. Alternatively feel free to contact
Nick via Twitter (@NDickinsonWilde), or email (nick@nickdickinsonwilde.ca).
