<?php
namespace App\Module;

use App\Model\Entity\Person;

abstract class Report {
	abstract public function run($params, Person $recipient);
}
