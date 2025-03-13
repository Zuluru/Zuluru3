<?php
/**
 * @var \App\View\AppView $this
 * @var string $download_file_name
 */

// Start the output, let the browser know what type it is
use Cake\I18n\FrozenTime;

@header('Content-type: text/directory; charset=UTF-8; profile=vCard');
@header("Content-Disposition: attachment; filename=\"$download_file_name.vcf\"");
// Prevent caching
@header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', false);
@header('Pragma: no-cache');
?>
BEGIN:VCARD
VERSION:2.1
<?= $this->fetch('content') ?>
REV:<?= $this->Time->iCal(FrozenTime::now()) ?>

END:VCARD
