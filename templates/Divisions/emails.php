<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add(__('Emails'));
$this->Breadcrumbs->add($division->full_league_name);
?>

<div class="divisions emails">
<h2><?= __('Coach/Captain Emails') . ': ' . $division->full_league_name ?></h2>

<?php
$people = collection($division->teams)->extract('people.{*}')->sortBy('last_name', SORT_DESC, SORT_STRING | SORT_FLAG_CASE)->toList();
echo $this->element('emails', ['people' => $people]);
?>

</div>
