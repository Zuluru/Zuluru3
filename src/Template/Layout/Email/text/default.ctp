<?php
/**
 * @var \App\View\AppView $this
 */

echo $this->element('Email/text/common_header');
echo $this->fetch('content');
echo $this->element('Email/text/common_footer');
