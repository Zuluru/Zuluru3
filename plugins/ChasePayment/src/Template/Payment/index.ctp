<?php
/**
 * @type bool $result
 * @type string[] $errors
 * @type \App\Model\Entity\RegistrationAudit $audit
 */

echo $this->element('Registrations/payment', compact('result', 'errors', 'audit'));
