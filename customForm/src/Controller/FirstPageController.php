<?php

namespace Drupal\customForm\Controller;

class FirstPageController
{
    public function content()
    {
        $element = array(
            '#markup' => 'Hello World!',
        );
        return $element;
    }
}
