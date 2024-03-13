<?php
/**
 * @var bool $result
 * @var string[] $errors
 * @var \App\Model\Entity\RegistrationAudit $audit
 */

echo $this->element('Registrations/payment', compact('result', 'errors', 'audit'));
