<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task[] $tasks
 */

use Cake\Core\Configure;

$fp = fopen('php://output','w+');
$headers = [
	__('Category'),
	__('Task'),
	__('Reporting To'),
	__('Date'),
	__('Start'),
	__('End'),
	__('Assignee'),
	__('Approved By'),
	__('Email'),
];
if (Configure::read('profile.home_phone')) {
	$headers[] = __('Home Phone');
}
if (Configure::read('profile.work_phone')) {
	$headers[] = __('Work Phone');
	$headers[] = __('Work Ext');
}
if (Configure::read('profile.mobile_phone')) {
	$headers[] = __('Mobile Phone');
}

fputcsv($fp, $headers);

foreach ($tasks as $category) {
	foreach ($category->tasks as $task) {
		foreach ($task->task_slots as $slot) {
			$row = [
					$category->name,
					$task->name,
					$task->person->full_name,
					$slot->task_date,
					$slot->task_start,
					$slot->task_end,
			];
			if (!empty($slot->person)) {
				$row = array_merge($row, [
						$slot->person->full_name,
						$slot->approved ? $slot->approved_by->full_name : '',
						$slot->person->email,
				]);
				if (Configure::read('profile.home_phone')) {
					$row[] = $slot->person->home_phone;
				}
				if (Configure::read('profile.work_phone')) {
					$row[] = $slot->person->work_phone;
					$row[] = $slot->person->work_ext;
				}
				if (Configure::read('profile.mobile_phone')) {
					$row[] = $slot->person->mobile_phone;
				}
			}
			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
