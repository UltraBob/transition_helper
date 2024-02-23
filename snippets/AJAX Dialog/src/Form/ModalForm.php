<?php

namespace Drupal\dialog\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\message_notify\MessageNotifier;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Build the rejection ModalForm.
 */
final class ModalForm extends FormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AccountInterface $account,
    AliasManagerInterface $alias_manager,
    CurrentPathStack $current_path,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->account = $account;
    $this->aliasManager = $alias_manager;
    $this->currentPath = $current_path;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ModalForm {
    return new self(
      $container->get('current_user'),
      $container->get('path_alias.manager'),
      $container->get('path.current'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dialog_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Get the created time of the current node.
    $current_path = $this->currentPath->getPath();
    $result_url = $this->aliasManager->getAliasByPath($current_path);
    $parts = explode('/', $result_url);
    $nid = $parts[5];
    $form['#prefix'] = '<div id="dialog_modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    // Node nid field with hidden value used at the time of button action.
    $form['current_node_id'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    $form['reason'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Rejection Reason'),
      '#required' => TRUE,
      '#description' => $this->t('Please comment instead of rejecting if the deficiency can be readily remedied.'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Abort Rejection'),
      '#attributes' => [
        'class' => [
          'use-ajax cancel',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'cancelModalFormAjax'],
        'event' => 'click',
      ],
    ];
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm Rejection'),
      '#attributes' => [
        'class' => [
          'use-ajax confirm',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function cancelModalFormAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();

    $response->addCommand(new CloseModalDialogCommand());

    return $response;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state): AjaxResponse {
    $nid = $form_state->getValue('current_node_id');
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#dialog_modal_form', $form));
    }
    else {
      $currentURL = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
      $response->addCommand(new RedirectCommand($currentURL->toString()));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nid = $form_state->getValue('current_node_id');
    $reason = $form_state->getValue('reason');
    /** @var \Drupal\user\Entity\User $current_user */
    $current_user = $this->entityTypeManager->getStorage('user')
      ->load($this->account->id());
    $display_name = $this->account->getDisplayName();
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    // Send notification to the requestor of the rejection, including $reason from above.
    // Add an entry in the approval log that the entry was rejected by $display_name
    $node->save();

  }

}
