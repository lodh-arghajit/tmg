# Bootstrap 4 Modal

This project allows user to load bootstrap 4 modal
by [Drupal Ajax Dailog Boxes].

## Requirements

 - [Drupal Bootstrap] or any other theme that utilizes the
 Bootstrap framework modal and classes.

## Installation

Install as you would normally install a contributed Drupal module. See:
https://drupal.org/documentation/install/modules-themes/modules-8 for further
information.

## Configuration

No special configuration needed.

## Usage

```twig
<a
  href="[some-links]"
  class="use-ajax"
  data-dialog-type="bootstrap4_modal"
  data-dialog-options="{&quot;dialogClasses&quot;:&quot;modal-dialog-centered&quot;,&quot;dialogShowHeader&quot;:false}"
>
  Open in Bootstrap 4 Modal
</a>
```

## Buttons from dialog options
```twig
<a
  class="use-ajax"
  data-dialog-options="{&quot;dialogClasses&quot;:&quot;modal-dialog-centered&quot;,&quot;dialogShowHeader&quot;:false,&quot;buttons&quot;:[{&quot;text&quot;:&quot;Test&quot;}]}"
  data-dialog-type="bootstrap4_modal"
  href="[some-links]"
>
  Open in Bootstrap 4 Modal with buttons
</a>
```

## Buttons from form actions
All the buttons inside form #type "actions" will be added to the footer automatically.
```twig
...
$form['actions']['#type'] = 'actions';
$form['actions']['submit'] = [
  '#type' => 'submit',
  '#value' => t('Save'),
];
...
```
Give the form a route so we can link it to our ajax link
```twig
...
test_module.test_bootstrap4_modal_footer_buttons:
  path: '/test-modal-form'
  defaults:
    _form: 'Drupal\test_module\Form\TestModalForm'
  requirements:
    _permission: 'access content'
...
```
Ajax link
```twig
<a
  href="/test-modal-form"
  class="use-ajax"
  data-dialog-type="bootstrap4_modal"
  data-dialog-options="{&quot;dialogClasses&quot;:&quot;modal-dialog-centered&quot;,&quot;dialogShowHeader&quot;:false}"
>
  Open form in Bootstrap 4 Modal
</a>
```

See bootstrap4_modal_test.routing.yml and Drupal\bootstrap4_modal_test\Form\Bootstrap4ModalTestForm for an example

Convert all dialog type modal ajax links to bootstrap4_modal
```php
/**
 * Implements hook_link_alter().
 */
function my_module_link_alter(&$variables) {
  if (($variables['options']['attributes']['data-dialog-type'] ?? '') == 'modal') {
    $variables['options']['attributes']['data-dialog-type'] = 'bootstrap4_modal';
  }
}
```

## Maintainers

- Mark Quirvien Cristobal ([vhin0210](https://www.drupal.org/u/vhin0210))

[Drupal Ajax Dailog Boxes]:https://www.drupal.org/docs/drupal-apis/ajax-api/ajax-dialog-boxes
[Drupal Bootstrap]:https://www.drupal.org/project/bootstrap
