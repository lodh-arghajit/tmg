# IEF Table View Mode

## Introduction

This module creates a new widget called **Inline entity form - Complex - Table
View Mode** that extends the original widget of the *Inline Entity Form* module.
This uses a view mode to configure the columns of the table shown in the widget.
With this, you could define which fields and properties are shown in the table.

## Requirements

This module requires the following modules:

 * [Inline Entity Form](https://www.drupal.org/project/inline_entity_form) >=
 8.x-1.0-rc3

## Installation
Install as you would normally install a contributed Drupal module. Visit
https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
for further information.

## Configuration
* Configure an entity reference field to use the "Inline entity form - Complex
- Table View Mode" widget.
* Go to *Manage Display* settings of the entity/bundle that would be referenced
from the previously configured field.
* In **Custom display settings** activate the checkbox "**Inline Entity Form
Table**" and press the *Save* button (If the checkbox does not appear, see the
**Troubleshooting** section and return to this point).
* A secondary tab called *Inline Entity Form Table* should appear. If is not,
clear the cache.
* Go to the secondary tab *Inline Entity Form Table*.
* Configure display of the fields and save.
* Done

Now the fields should appear in the table.

## Troubleshooting
If the view mode "Inline Entity Form Table" does not appear in **Custom display
settings**. Follow these steps:

* Go to **Structure > Display Modes > View modes** or go to the path
**/admin/structure/display-modes/view**
* Confirm that there is a view mode called "*Inline Entity Form Table*" that has
the machine name [your-entity-type].ief_table such as **node.ief_table**.
* If there isn't one there, create one called "Inline Entity Form Table" and be
sure the machine name is **[your-entity-type].ief_table**.

Now the view mode **Inline Entity Form Table** should appear in **Custom display
settings**.
