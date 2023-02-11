<?php
use Migrations\AbstractMigration;

class EnhanceCategories extends AbstractMigration {
	/**
	 * Change Method.
	 *
	 * @return void
	 */
	public function change() {
		$this->table('categories')
			->addColumn('type', 'string', [
				'default' => null,
				'limit' => 32,
				'null' => false,
			])
			->addColumn('slug', 'string', [
				'default' => null,
				'limit' => 100,
				'null' => false,
			])
			->addColumn('image_url', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('description', 'text', [
				'default' => null,
				'limit' => null,
				'null' => false,
			])
			->addColumn('sort', 'integer', [
				'default' => 1,
				'null' => false,
			])
			->update();

		$this->execute('UPDATE categories SET type = "Tasks"');

		$this->table('leagues_categories')
			->addColumn('league_id', 'integer', [
				'default' => null,
				'null' => false,
			])
			->addColumn('category_id', 'integer', [
				'default' => null,
				'null' => false,
			])
			->create();
	}
}
