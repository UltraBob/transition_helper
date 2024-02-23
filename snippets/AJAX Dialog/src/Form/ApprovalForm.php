<?php

namespace Drupal\dialog\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Our custom approvals form.
 */
final class ApprovalsForm extends FormBase {

  /**
   * The route match interface service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new self(
    // Load the service required to construct this class.
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "approval_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->routeMatch->getParameter('node');
    $form['rejected'] = [
        '#name' => 'Reject',
        '#type' => 'link',
        '#title' => $this->t('Reject'),
        '#url' => Url::fromRoute('dialog.open_modal_form', [
          'nid' => $node->id(),
        ]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
          ],
        ],
      ];
      return $form;
  }

}
