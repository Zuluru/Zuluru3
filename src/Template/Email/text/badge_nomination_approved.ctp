<?= __('Dear {0},', $nominator->first_name) ?>


<?= __('Your nomination of {0} for the {1} badge has been approved and is now visible to other members who are logged in to this site.',
	$person->full_name,
	$badge->name
) ?>


<?= $this->element('Email/text/footer');
