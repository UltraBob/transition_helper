<?php

use Drupal\message\Entity\Message;
use Drupal\message_notify\MessageNotifier;

/**
   * Generic message sender.  Create, populate, and send an email using the Message stack.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node this is about.  Used for token replacement.
   * @param string $template
   *   The message template to use.
   * @param \Drupal\user\UserInterface|null $user
   *   The user to send to; defaults to Node owner. Used for token replacement.
   * @param array $reminder_message
   *   Value for the reminder message field.  Default none.
   * @param array $other_fields
   *   An array of other fields to populate.  Use field_name => value format.
   * @param string $target_email
   *   Email address to send to.  Defaults to none.
   * @param string $uid
   *   Uid to use for the message, defaults to node owner id.
   */
  public function MessageCreator(Node $node, string $template, UserInterface $user = NULL, array $reminder_message = [], array $other_fields = [], string $target_email = '', string $uid = '') {

    $user = $user ?? $node->getOwner();
    $uid = $uid ?: $user->id();
    $message = Message::create(
      [
        'template' => $template,
        'uid' => $uid,
      ]
    );
    $message->set('field_message_node_content', $node->id());
    if (!empty($target_email)) {
      $options = ['mail' => $target_email];
    }
    else {
      $options = [];
      $message->set('field_message_user', $user);
    }
    if (!empty($reminder_message)) {
      $message->set('field_message_reminder_notice', $reminder_message);
    }
    if (!empty($other_fields)) {
      foreach ($other_fields as $field => $value) {
        $message->set($field, $value);
      }
    }
    $message->save();
    $this->notifier->send($message, $options);
  }
