<?php
namespace Drupal\tmg_utility\Ajax\Command;

use Drupal\Core\Ajax\CommandInterface;

class AjaxRedirect implements CommandInterface {
  protected $url;
  // Constructs a ReadMessageCommand object.
  public function __construct($url) {
    $this->url = $url;
  }
  // Implements Drupal\Core\Ajax\CommandInterface:render().
  public function render() {
    return array(
      'command' => 'AjaxRedirect',
      'url' => $this->url,
    );
  }
}
