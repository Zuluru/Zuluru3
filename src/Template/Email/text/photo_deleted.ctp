<?= __('Dear {0},', $person->first_name) ?>


<?= __('Your photo has been reviewed by an administrator and rejected as unsuitable. To be approved, photos must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face. Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of the sport.') ?>


<?= $this->element('Email/text/footer');
