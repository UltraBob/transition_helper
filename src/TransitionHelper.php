<?php

namespace Drupal\transition_helper;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Service description.
 */
class TransitionHelper {

  /**
   * The content_moderation.moderation_information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * Constructs a TransitionHelper object.
   *
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The content_moderation.moderation_information service.
   */
  public function __construct(ModerationInformationInterface $moderation_information) {
    $this->moderationInformation = $moderation_information;
  }

  /**
   * Giving a revisionable content entity, in the process of being saved, return the transition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return string
   */
  public function getTransition(ContentEntityInterface $entity): string {
    if (!$this->moderationInformation->isModeratedEntity($entity)) {
      throw new \InvalidArgumentException('Entity type does not support moderation.');
    }

    if (!$entity->hasField('moderation_state') || $entity->get('moderation_state')->isEmpty()) {
      throw new \InvalidArgumentException('The given entity has no moderation state.');
    }
    $newState = $entity?->moderation_state?->first()?->value;
    $originalState = $this->moderationInformation->getOriginalState($entity)->id();
    $workflow = $this->moderationInformation->getWorkflowForEntity($entity);
    $transitions = $workflow->getTypePlugin()->getTransitions();

    foreach ($transitions as $transition) {
      if ($transition->to()->id() == $newState && in_array($originalState, array_keys($transition->from()))) {
        return $transition->id();
      }
    }

    throw new \RuntimeException('Transition not found.');
  }

}
