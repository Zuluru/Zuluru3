<?php
namespace App\View;

use Ajax\View\AjaxView as View;

/**
 * Ajax View class: extends the view from the Ajax plugin, adding helpers that our Ajax displays need
 */
class AjaxView extends View {

    /**
     * Initialization hook method.
     *
     * For e.g. use this method to load a helper for all views:
     * `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize() {
		$this->loadHelper('Authentication.Identity');
		$this->loadHelper('Authorize');
		$this->loadHelper('Number');
		$this->loadHelper('Text');
		$this->loadHelper('Html', ['className' => 'ZuluruHtml']);
		$this->loadHelper('Form', ['className' => 'ZuluruForm']);
		$this->loadHelper('Time', ['className' => 'ZuluruTime']);
		$this->loadHelper('ZuluruJquery.Jquery');
    }

}
