<?php
echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'answer' => $answer->id]]);
