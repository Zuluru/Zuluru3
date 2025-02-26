<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

// We intentionally do not echo the result of the create call. It is just to set up some defaults in the form helper.
$this->Form->create($questionnaire, ['align' => 'horizontal']);
echo $this->element('Questionnaires/edit_question');
