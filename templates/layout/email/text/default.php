<?php
/**
 * @var \App\View\AppView $this
 */

echo $this->element('email/text/common_header');
echo $this->fetch('content');
echo $this->element('email/text/common_footer');
