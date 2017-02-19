<?php

/**
 * @file
 * Contains \Drupal\ui_components\Controller\UiComponentsController.
 */

namespace Drupal\ui_components\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
/**
 * UiComponentsController.
 */
class UiComponentsController extends ControllerBase {

  public function getCardBoard() {
    $image_path = base_path() . drupal_get_path('module', 'ui_components') . '/assets/images';
    $output = [];
    $output['card_card_1']  = [
      '#theme' => 'ui_card_board',
      '#items' => [
        [
          '#theme' => 'ui_card',
          '#title' => 'London tour',
          '#image' => [
            'src' => $image_path . '/london.jpg',
            'alt' => 'This should be an image',
          ],
          '#body' => 'The tour is suitable for anyone of any age and includes a lot of demonstrations that illustrate the maths behind what you see. If you choose to take your own tour of London and want to make it a bit more interactive, look at the ‘Demonstration’ sections listed in the full tour to see what materials you need to bring with you.',
          '#landscape' => FALSE,
          '#action' => [
            'url' => 'http://bbc.co.uk',
            'new_window' => TRUE,
            'text' => 'Checkout',
          ],
        ],
        [
          '#theme' => 'ui_card',
          '#title' => 'Paris is calling',
          '#image' => [
            'src' => $image_path . '/paris.jpg',
            'alt' => 'This should be an image',
          ],
          '#body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut nec blandit ex. Aenean eu viverra velit. Nam vitae arcu ut dui tincidunt pretium. Ut a erat imperdiet, lobortis sapien nec, rhoncus lectus. Proin ipsum leo, aliquam at leo sed, facilisis feugiat eros. Interdum et malesuada fames ac ante ipsum primis in faucibus.',
          '#landscape' => FALSE,
          '#action' => [
            'url' => 'http://bbc.co.uk',
            'new_window' => TRUE,
            'text' => 'Learn more',
          ],
        ],
        [
          '#theme' => 'ui_card',
          '#title' => 'Chennai beach',
          '#image' => [
            'src' => $image_path . '/chennai.jpg',
            'alt' => 'This should be an image',
          ],
          '#body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vel erat quam. Suspendisse tempor sem ac diam porttitor faucibus. In aliquam nec tortor id elementum. Integer finibus placerat dolor sed tincidunt. Cras quis aliquet metus, sed rhoncus magna. Quisque accumsan gravida sem, ac finibus quam aliquet porta.',
          '#landscape' => FALSE,
          '#action' => [
            'url' => 'http://bbc.co.uk',
            'new_window' => TRUE,
            'text' => 'Take me',
          ],
        ],
      ]
    ];

    return $output;
  }

}
