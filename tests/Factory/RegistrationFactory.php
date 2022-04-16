<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class RegistrationFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Registrations';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 * @return void
	 */
	protected function setDefaultTemplate()
	{
		$this->setDefaultData(function(Generator $faker) {
			return [
				// When we run tests with refunds, we need the registration to have been some time in the past
				'created' => FrozenTime::now()->subDay(),
			];
		});
	}

	/**
	 * @param string $payment
	 * @return $this
	 */
	public function setPayment(string $payment)
	{
		return $this->patchData(compact('payment'));
	}

	/**
	 * @return $this
	 */
	public function paid()
	{
		return $this->setPayment('Paid');
	}

	/**
	 * @return $this
	 */
	public function unpaid()
	{
		return $this->setPayment('Unpaid');
	}
}
