<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Question $question
 */

echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'question' => $question->id]]);
