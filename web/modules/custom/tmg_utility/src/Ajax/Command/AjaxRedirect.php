<?php
namespace Drupal\tmg_utility\Ajax\Command;

use Drupal\Core\Ajax\CommandInterface;

class AjaxRedirect implements CommandInterface {
  protected $url;
  // Constructs a ReadMessageCommand object.
  public function __construct($click_to_call_attributes) {
    $this->callToActionAttributes = $click_to_call_attributes;
  }
  // Implements Drupal\Core\Ajax\CommandInterface:render().
  public function render() {
    return array(
      'command' => 'clickToCall',
      'click_to_call_attributes' => json_encode($this->callToActionAttributes),
    );
  }
}
