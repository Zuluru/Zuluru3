<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 */

if (isset($league)) {
	if (!empty($division->name)) {
		echo __('{0} division of the ', $division->name);
	}
	echo __('{0} league', $league->name);
	if (!empty($division->days)) {
		echo __(', which operates on {0}', implode(__(' and '), collection($division->days)->extract('name')->toList()));
	}
}
