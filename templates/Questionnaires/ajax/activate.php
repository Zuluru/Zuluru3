<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', '?' => ['questionnaire' => $questionnaire->id]]]);
