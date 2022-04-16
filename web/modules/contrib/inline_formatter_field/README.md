# Inline Formatter Field


## Introduction

The Inline Formatter Field module allows site builders to template and styling 
entities with a field. This module will create a new field type called "Inline
Formatter" which is a boolean. When the boolean is checked the field will render
what is entered in the "HTML or Twig Format" field for the formatter's settings 
in the "Manage Display" screen of the entity. This module makes use of the ACE
Editor JavaScript library for ease. As the name suggests, any HTML or Twig can
be entered into this field.

Two Twig variables can be used to display dynamic content. First, the entity
itself accessed by the variable named by the entity type (ex 'node'), which can
be used for obtaining field information or any other entity information. Second,
the current user accessed by the variable 'current_user', which can be used to
display personalized information or check user roles.


## Requirements

This module requires Drupal core version 8, and at least 8.8.x for development
in order for the tests to pass.

This module requires the following module:
  - [Field](https://www.drupal.org/docs/8/core/modules/field)


## Recommended Modules

- [Token](https://www.drupal.org/project/token)
  When enabled, token replacement patterns can be entered into the formatter
  field. Also, this module gives an easy interface to get entity variables.
- [Twig Tweak](https://www.drupal.org/project/twig_tweak)
  This module makes more Drupal specific twig functions which can be used in the
  formatter field.
- [Devel](https://www.drupal.org/project/devel)
  This module is helpful for finding the right field and field variables when
  kint is enabled.


## Installation

1. Install as you would normally install a contributed Drupal module. Visit:
https://drupal.org/documentation/install/modules-themes/modules-7 for further
information.
2. Set up the Inline Formatter Field settings by going to 
admin/config/inline_formatter_field/settings.
    1. Ace Editor location should be set by either entering a CDN URL or by
    [downloading the library](https://github.com/ajaxorg/ace-builds/)
    and entering the path to the library relative to the Drupal root.
    2. Font Awesome location should be set by either entering a CDN URL or by
    [downloading the library](https://fontawesome.com/how-to-use/on-the-web/setup/hosting-font-awesome-yourself)
    and entering the path to the library relative to the Drupal root.
    3. Select the theme, mode, and extra options settings for Ace Editor.
3. Add the field to the desired entity(ies).
4. Edit the manage display of the entity for the inline formatter to display as
desired.


## Configuration

This module has configuration for the following items:
- ace_source - The path or URL to get the Ace Editor library.
- fa_source - The path or URL to get the Font Awesome library.
- ace_theme - The Ace Editor theme to be used by default.
- ace_mode - The Ace Editor mode to be used by default.
- available_themes - A list of key value pairs of available Ace Editor themes.
- available_modes - A list of key value pairs of available Ace Editor modes.
- extra_options - A list of key value pairs of Ace Editor extra settings.

These configuration values can be altered in the Inline Formatter Field Settings
Form (admin/config/inline_formatter_field/settings).

The Ace Editor theme, mode, and extra settings are the default values, and each
user can alter their preference on the manage display form. The user's values
are saved as a cookie for the site. If no cookie is found, then the default is
used.

---

This module creates a field with the following item:
- formatted_field - The HTML or Twig to render when the field is checked for 
display.

This configuration value can be altered in the manage display tab of the entity.

## Troubleshooting

If the field is not rendering, check to see if the boolean field is actually
checked in the edit form for the content, and check to make sure that valid
html and twig is entered.

---

If the ACE Editor fails to load:
- Make sure that JavaScript is allowed in your browser, and check for console
errors.
- Check that the library path set in the module's settings exists and is
correctly entered. If a CDN is used, make sure the URL is correct.

---

For reporting any errors or bugs, please
[create an issue](https://www.drupal.org/project/issues/inline_formatter_field).


## FAQ

Q: I checked the box in the content form and all that is rendering is a h1
"Hello World!". What am I missing?

A: The "Hello World!" message is the default template. In order to change this,
go to the manage display tab for the entity and click on the gear for the inline
formatter field. Then, replace the "Hello World!" with your own template.

---

Q: Can I use more than one of these on a single entity type?

A: Yes, you can use multiple inline formatter fields to a single entity. This
will allow you to have many different 'displays' or formats for a single entity
by checking which display you want, or you could display multiple parts of a
rendered entity with separate inline formatter fields.

---

Q: Will the format render if the checkbox is not checked?

A: No, the checkbox must be selected in order for the format to be rendered.
This will allow the ability for parts of an entities template that may or may
not be rendered based on the content creator.

---

Q: Can I use this to display dynamic content?

A: Yes, Twig can be entered into the 'HTML or Twig Format' manage display field.
The current user and entity along with all its fields can be used in this
display field.

---

Q: Can I hide the field and always make it checked?

A: Yes, when adding the field, you can check the box for the default value. Now,
you can move the field to the disabled section in the manage form tab. This will
force all new content with this entity to have this field checked by default.
Note, that all existing content won't change. 

This strategy can be used to always force a certain display, or to control the
whole display of an entity within this one field.


## MAINTAINERS

Current maintainers:
  - [Bobby Saul](https://www.drupal.org/u/bobbysaul)
