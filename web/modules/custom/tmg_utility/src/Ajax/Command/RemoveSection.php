<?php
namespace Drupal\tmg_utility\Ajax\Command;

use Drupal\Core\Ajax\CommandInterface;

class RemoveSection implements CommandInterface {
  protected $selector;
  protected $section;
  // Constructs a ReadMessageCommand object.
  public function __construct($selector = NULL, $section = FALSE) {
    $this->selector = $selector;
    $this->section = $section;
  }
  // Implements Drupal\Core\Ajax\CommandInterface:render().
  public function render() {
    return array(
      'command' => 'RemoveSection',
      'selector' => $this->selector,
      'section' => $this->section,
    );
  }
}
