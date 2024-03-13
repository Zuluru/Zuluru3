<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Questionnaire $questionnaire
 */

echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'questionnaire' => $questionnaire->id]]);
