<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use App\PasswordHasher\HasherTrait;
use App\Exception\RuleException;

/**
 * Newsletters Controller
 *
 * @property \App\Model\Table\NewslettersTable $Newsletters
 */
class NewslettersController extends AppController {

	use HasherTrait;

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$this->paginate = [
			'order' => ['target' => 'DESC'],
			'contain' => ['MailingLists' => ['Affiliates']],
			'conditions' => [
				'target >=' => FrozenDate::now()->subDays(30),
				'MailingLists.affiliate_id IN' => $affiliates,
			],
		];

		$this->set('newsletters', $this->paginate($this->Newsletters));
		$this->set('current', true);
		$this->set(compact('affiliates'));
	}

	public function past() {
		$this->Authorization->authorize($this, 'index');
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$this->paginate = [
			'order' => ['target' => 'DESC'],
			'contain' => ['MailingLists' => ['Affiliates']],
			'conditions' => [
				'MailingLists.affiliate_id IN' => $affiliates,
			],
		];

		$this->set('newsletters', $this->paginate($this->Newsletters));
		$this->set('current', false);
		$this->set(compact('affiliates'));
		$this->render('index');
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('newsletter');
		try {
			$newsletter = $this->Newsletters->get($id, [
				'contain' => ['MailingLists']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($newsletter);
		$this->Configuration->loadAffiliate($newsletter->mailing_list->affiliate_id);

		$this->set(compact('newsletter'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$newsletter = $this->Newsletters->newEntity();
		$this->Authorization->authorize($newsletter);

		if ($this->request->is('post')) {
			$newsletter = $this->Newsletters->patchEntity($newsletter, $this->request->data);
			if ($this->Newsletters->save($newsletter)) {
				$this->Flash->success(__('The newsletter has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The newsletter could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->Newsletters->MailingLists->affiliate($this->request->data['mailing_list_id']));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$mailingLists = $this->Newsletters->MailingLists->find()
			->contain(['Affiliates'])
			->where(['MailingLists.affiliate_id IN' => $affiliates])
			->order(['Affiliates.name', 'MailingLists.name'])
			->combine('id', 'name', 'affiliate.name')
			->toArray();
		if (count($affiliates) == 1) {
			$mailingLists = current($mailingLists);
		}
		$this->set(compact('newsletter', 'mailingLists'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('newsletter');
		try {
			$newsletter = $this->Newsletters->get($id, [
				'contain' => ['MailingLists']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($newsletter);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$newsletter = $this->Newsletters->patchEntity($newsletter, $this->request->data);
			if ($this->Newsletters->save($newsletter)) {
				$this->Flash->success(__('The newsletter has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The newsletter could not be saved. Please correct the errors below and try again.'));
				$affiliate = $this->Newsletters->MailingLists->affiliate($this->request->data['mailing_list_id']);
			}
		} else {
			$affiliate = $newsletter->mailing_list->affiliate_id;
		}
		$this->Configuration->loadAffiliate($affiliate);

		$this->set(compact('newsletter'));
		$this->set('mailingLists', $this->Newsletters->MailingLists->find('list', [
				'conditions' => ['affiliate_id' => $affiliate],
		])->toArray());
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('newsletter');
		try {
			$newsletter = $this->Newsletters->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($newsletter);

		$dependencies = $this->Newsletters->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this newsletter, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Newsletters->delete($newsletter)) {
			$this->Flash->success(__('The newsletter has been deleted.'));
		} else if ($newsletter->errors('delete')) {
			$this->Flash->warning(current($newsletter->errors('delete')));
		} else {
			$this->Flash->warning(__('The newsletter could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function delivery() {
		$id = $this->request->getQuery('newsletter');
		try {
			$newsletter = $this->Newsletters->get($id, [
				'contain' => ['MailingLists', 'Deliveries']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid newsletter.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($newsletter);
		$this->Configuration->loadAffiliate($newsletter->mailing_list->affiliate_id);

		$ids = collection($newsletter->deliveries)->extract('person_id')->toArray();
		if (!empty($ids)) {
			$people = TableRegistry::get('People')->find()
				->where(['id IN' => $ids])
				->order(['last_name', 'first_name'])
				->toArray();
		} else {
			$people = [];
		}

		$this->set(compact('newsletter', 'people'));
	}

	public function send() {
		$id = $this->request->getQuery('newsletter');
		$execute = $this->request->getQuery('execute');
		$test = $this->request->getQuery('test');
		$this->set(compact('execute', 'test'));

		$this->loadComponent('Lock');

		if ($execute) {
			// Read the newsletter, including lists of who has received it
			// and who has unsubscribed from this mailing list
			try {
				$newsletter = $this->Newsletters->get($id, [
					'contain' => [
						'MailingLists' => [
							'Affiliates',
							'Subscriptions' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['subscribed' => false]);
								},
							],
						],
						'Deliveries',
					]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid newsletter.'));
				return $this->redirect(['action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid newsletter.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Authorization->authorize($newsletter);
			$this->Configuration->loadAffiliate($newsletter->mailing_list->affiliate_id);

			// Handle the rule controlling mailing list membership
			$rule_obj = $this->moduleRegistry->load('RuleEngine');
			if (!$rule_obj->init($newsletter->mailing_list->rule)) {
				$this->Flash->warning(__('Failed to parse the rule: {0}', $rule_obj->parse_error));
				return $this->redirect(['action' => 'view', 'newsletter' => $id]);
			}

			$user_model = Configure::read('Security.authModel');
			$authenticate = TableRegistry::get($user_model);
			$email_field = $authenticate->emailField;
			try {
				$people = $rule_obj->query($newsletter->mailing_list->affiliate_id, [
					'OR' => [
						[
							"$user_model.$email_field !=" => '',
							'NOT' => ["$user_model.$email_field IS" => null],
						],
						[
							'People.alternate_email !=' => '',
							'NOT' => ['People.alternate_email IS' => null],
						],
						[
							"Related$user_model.$email_field !=" => '',
							'NOT' => ["Related$user_model.$email_field IS" => null],
						],
						[
							'Related.alternate_email !=' => '',
							'NOT' => ['Related.alternate_email IS' => null],
						],
					],
				]);
			} catch (RuleException $ex) {
				$this->Flash->info($ex->getMessage());
				return $this->redirect(['action' => 'view', 'newsletter' => $id]);
			}

			if (!empty($people)) {
				$sent_ids = collection($newsletter->deliveries)->extract('person_id')->toArray();
				$unsubscribed_ids = collection($newsletter->mailing_list->subscriptions)->extract('person_id')->toArray();
				$people = array_diff($people, $sent_ids, $unsubscribed_ids);

				if (!empty($people)) {
					$people = $this->People->find()
						->contain([Configure::read('Security.authModel')])
						->where([
							'People.id IN' => $people,
						])
						->limit($newsletter->batch_size)
						->order(['People.first_name', 'People.last_name', 'People.id'])
						->toArray();
				}
			}

			if (empty($people)) {
				$this->Flash->success(__('Finished sending newsletters.'));
				return $this->redirect(['action' => 'delivery', 'newsletter' => $id]);
			}

			if (!$this->Lock->lock('newsletter', $newsletter->mailing_list->affiliate_id, 'newsletter delivery')) {
				return $this->redirect(['action' => 'view', 'newsletter' => $id]);
			}

			$delay = $newsletter->delay * MINUTE;
			$this->set(compact('delay'));
		} else {
			try {
				$newsletter = $this->Newsletters->get($id, [
					'contain' => [
						'MailingLists' => [
							'Affiliates',
						],
						'Deliveries',
					]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid newsletter.'));
				return $this->redirect(['action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid newsletter.'));
				return $this->redirect(['action' => 'index']);
			}

			$this->Authorization->authorize($newsletter);
			$this->Configuration->loadAffiliate($newsletter->mailing_list->affiliate_id);

			if ($test) {
				$people = [$this->UserCache->read('Person')];
			} else {
				$this->set(compact('newsletter'));
				return;
			}
		}

		$emails = array_keys($this->_extractEmails($people));
		$this->set(compact('newsletter', 'people', 'emails'));
		$params = [
			'from' => $newsletter->from_email,
			'subject' => $newsletter->subject,
			'sendAs' => 'both',
			'template' => 'newsletter',
			'header' => [
				'Auto-Submitted' => 'auto-generated',
				'X-Auto-Response-Suppress' => 'OOF',
				'Precedence' => 'list',
			],
			'attachments' => Configure::read('App.email.newsletter_attachments'),
		];
		if (!empty($newsletter->reply_to)) {
			$params['replyTo'] = $newsletter->reply_to;
		}

		if ($newsletter->personalize || $test) {
			foreach ($people as $person) {
				$params['to'] = $person;
				$code = $this->_makeHash([$person->id, $newsletter->mailing_list->id]);
				$params['viewVars'] = compact('newsletter', 'person', 'code');

				if ($newsletter->mailing_list->opt_out) {
					$url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $newsletter->mailing_list->id, 'person' => $person->id, 'code' => $code], true);
					$params['header']['List-Unsubscribe'] = "<$url>";
				}

				if ($this->_sendMail($params) && !$test) {
					// Update the activity log
					$delivery = $this->Newsletters->Deliveries->newEntity([
						'type' => 'newsletter',
						'newsletter_id' => $id,
						'person_id' => $person->id,
					]);
					$this->Newsletters->Deliveries->save($delivery);
				}
			}
		} else {
			$params['bcc'] = $people;
			if (!empty($newsletter->to_email)) {
				$params['to'] = $newsletter->to_email;
			} else {
				$params['to'] = $newsletter->from_email;
			}
			if ($newsletter->mailing_list->opt_out) {
				$url = Router::url(['controller' => 'MailingLists', 'action' => 'unsubscribe', 'list' => $newsletter->mailing_list->id], true);
				$params['header']['List-Unsubscribe'] = "<$url>";
			}
			$params['viewVars'] = compact('newsletter');

			if ($this->_sendMail($params)) {
				foreach ($people as $person) {
					// Update the activity log
					$delivery = $this->Newsletters->Deliveries->newEntity([
						'type' => 'newsletter',
						'newsletter_id' => $id,
						'person_id' => $person->id,
					]);
					$this->Newsletters->Deliveries->save($delivery);
				}
			}
		}
	}

}
