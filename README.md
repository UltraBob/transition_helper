# Transition Helper Module

This module was created to share code snippets for use in a presentation about
building approval systems on top of Workflows in Drupal.  This code will definitely
not completely work without modification.

## Index of snippets referenced in the presentation

1. Service To Get Transition Id When Saving Entity:
   * `src/TransitionHelper.php`
   * `transition_helper.services.yml`
2. Removing or Modifying a Workflow Button
   * `transition_helper.module`
3. Creating a mail message
   * `snippets/Message Stack/MessageCreator.php` -- A sample method for creating, populating, and sending an email message with the Message stack.
4. AJAX Dialog
   * `snippets/AJAX Dialog/dialog.routing.yml` The routing file to define the controller which responds to the AJAX trigger
   * `snippets/AJAX Dialog/src/Form/ApprovalForm.php` The Original form with an AJAX trigger on the reject button
   * `snippets/AJAX Dialog/src/Form/ModalForm.php` The Modal form served by the controller.
   * `snippets/AJAX Dialog/src/Controller/ModalFormApprovalsController.php` The controller
