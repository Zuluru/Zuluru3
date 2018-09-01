<?php
echo $this->Jquery->ajaxLink(__('Deactivate'), ['url' => ['action' => 'deactivate', 'questionnaire' => $questionnaire->id]]);
