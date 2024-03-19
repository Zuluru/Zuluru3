<?php
/**
 * @var \App\View\AppView $this
 */

// TODOSECOND: How does this compare to $this->withDisabledCache(); in the controller?
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');

echo $this->fetch('content');
