<?php
/**
 * @var \App\View\AppView $this
 */
?>
<p><?= __('When you first create a {0} profile, you set your skill level by answering a short questionnaire. Many players then just leave this alone, and it becomes increasingly inaccurate; we get better with experience, but we slow down as we age. It is recommended to revisit this questionnaire every year or two, so that your skill level remains an accurate representation of your abilities. The questionnaire is accessed through the {1} page, in the "{2}" section.',
	ZULURU,
	$this->Html->link(__('My Profile') . ' -> ' . __('Edit'), ['controller' => 'People', 'action' => 'edit']),
	__('Your Player Profile')
);
?></p>
<p><?= __('There is a misconception in some areas that your skill level in some way affects your team\'s standings or the way that you move up and down the ladder. This is <strong>not</strong> the case. Average team skill levels may be used, in the absence of any other data (such as previous season results or other personal knowledge of the team), to determine the initial placement of a team. However, this is generally immaterial in a round-robin league, and will very quickly be corrected in a ladder league, so there is no benefit to falsifying your skill level.') ?></p>
