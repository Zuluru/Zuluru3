<?php
namespace App\Shell\Task;

use App\Controller\AppController;
use App\Core\ModuleRegistry;
use App\Exception\MissingModuleException;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * RunReport Task
 *
 * @property \App\Model\Table\ReportsTable $reports_table
 */
class RunReportTask extends Shell {

	public function main() {
		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		$this->reports_table = TableRegistry::get('Reports');
		$report = $this->reports_table->find()
			->contain(['People' => [Configure::read('Security.authModel')]])
			->order('Reports.id')
			->first();
		if (!$report) {
			return;
		}

		try {
			$runner = ModuleRegistry::getInstance()->load("Report:{$report->report}");
		} catch (MissingModuleException $ex) {
			// Bad report type. Shouldn't ever happen. Inform the user, and delete the record.
			AppController::_sendMail([
				'to' => $report->person,
				'subject' => __('{0} Report Failed', Configure::read('organization.short_name')),
				'content' => __('The {0} server has failed to run your requested "{1}" report.', Configure::read('organization.short_name'), $report->report) . ' ' .
					__('This is a fatal error, it will not be retried.'),
				'sendAs' => 'text',
			]);
			$this->reports_table->delete($report);
			return;
		}

		try {
			$runner->run(json_decode($report->params, true), $report->person);
			$this->reports_table->delete($report);
		} catch (\Exception $ex) {
			// Failed to run the report. Try again up to 3 times.
			if (++$report->failures == 3) {
				AppController::_sendMail([
					'to' => $report->person,
					'bcc' => 'admin@zuluru.org',
					'subject' => __('{0} Report Failed', Configure::read('organization.short_name')),
					'content' => __('The {0} server has failed to run your requested "{1}" report.', Configure::read('organization.short_name'), $report->report) . ' ' .
						__('The report has failed three times, so it will not be retried.'),
					'sendAs' => 'text',
				]);
				$this->reports_table->delete($report);
			} else {
				$this->reports_table->save($report);
			}
		}
	}

}
