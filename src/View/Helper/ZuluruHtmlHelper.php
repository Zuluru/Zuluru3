<?php
namespace App\View\Helper;

use App\Controller\AppController;
use Cake\Core\Configure;
use BootstrapUI\View\Helper\HtmlHelper as HtmlHelper;
use Cake\Routing\Router;

class ZuluruHtmlHelper extends HtmlHelper {
	public $helpers = ['Url', 'Text', 'Form'];

	/**
	 * List of help IDs that have already been included on the page.
	 *
	 * @var int[]
	 */
	private $__helpShown = [];

	/**
	 * Extend the default link function by allowing for shortening link titles.
	 */
	public function link($title, $url = null, array $options = [], $confirmMessage = false) {
		if ($confirmMessage !== false) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		if (is_array($options) && array_key_exists('max_length', $options)) {
			$max = $options['max_length'];
			unset($options['max_length']);
			if (strlen($title) > $max) {
				$options['title'] = $title;
				$title = $this->Text->truncate($title, $max);
			}
		}
		return parent::link($title, $url, $options);
	}

	/**
	 * Add a "buffer" option, which will result in the provided script being included in the jQuery ".ready" block
	 */
	public function scriptBlock($script, array $options = []) {
		if (!empty($options['buffer'])) {
			if ($options['buffer'] === true) {
				$options['buffer'] = 'footer_script';
			}
			$this->_View->append($options['buffer'], $script);
		} else {
			return parent::scriptBlock($script, $options);
		}
	}

	/**
	 * Create links from images.
	 */
	public function imageLink($img, $url, array $imgOptions = [], array $linkOptions = [], $todotesting = null) {
		if ($todotesting) {
			trigger_error('Confirm parameter given to imageLink', E_USER_WARNING);
			exit;
		}

		if (array_key_exists('class', $linkOptions)) {
			if (is_array($linkOptions['class'])) {
				$linkOptions[] = 'icon';
			} else {
				$linkOptions .= ' icon';
			}
		} else {
			$linkOptions['class'] = 'icon';
		}

		return $this->link(parent::image($img, $imgOptions),
			$url, array_merge(['escapeTitle' => false], $linkOptions));
	}

	/**
	 * Use local settings to select an icon.
	 */
	public function iconImg($img, Array $imgOptions = []) {
		$base_folder = Configure::read('App.paths.imgBase');

		$icon_pack = Configure::read('App.iconPack');
		if ($icon_pack == 'default') {
			$icon_pack = '';
		}

		if (!empty($icon_pack) && file_exists($base_folder . DS . $icon_pack . DS . $img)) {
			return parent::image("$icon_pack/$img", $imgOptions);
		}
		return parent::image($img, $imgOptions);
	}

	/**
	 * Create links from icons.
	 */
	public function iconLink($img, $url, array $imgOptions = [], array $linkOptions = [], $todotesting = null) {
		if ($todotesting) {
			trigger_error('Confirm parameter given to iconLink', E_USER_WARNING);
			exit;
		}
		$linkOptions['class'] = 'icon';
		return $this->link($this->iconImg($img, $imgOptions),
			$url, array_merge(['escapeTitle' => false], $linkOptions));
	}

	/**
	 * Create pop-up help links.
	 */
	public function help(array $url, $duplicate = false) {
		$help = '';

		// Add "/help" to the beginning of whatever URL is provided
		$url = array_merge(['controller' => 'Help'], $url);

		// Prevent any given help from being added more than once
		$id = implode('_', array_values($url));
		if (!$duplicate && in_array($id, $this->__helpShown)) {
			return;
		}

		// Add the help image, with a link to a pop-up with the help
		$help .= $this->iconLink('help_16.png', $url, [
			'class' => 'zuluru_help_link',
			'alt' => __('[Help]'),
			'title' => __('Additional help'),
			'data-id' => $id,
		], ['target' => 'help']);

		if (!in_array($id, $this->__helpShown)) {
			$this->_View->append('help');
			$title = array_map(['\Cake\Utility\Inflector', 'humanize'], array_values($url));
			$title = array_map('__', array_values($title), array_fill(0, count($title), true));
?>
<div id="<?= $id ?>" class="help-dialog" title="<?= implode(' :: ', $title) ?>">
<?php
			echo $this->_View->element(implode('/', array_values($url)));
			echo $this->tag('hr');

			// Build the link for suggestions
			$body = htmlspecialchars('I have a suggestion for the Zuluru online help page at ' . implode(' : ', $url));
			echo $this->para(null, __('If you have suggestions for additions, changes or other improvements to this online help, please send them to {0}.',
				$this->link(Configure::read('email.support_email'), 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Online%20Help%20Suggestion&body=$body")
			));
?>
</div>
<?php
			$this->_View->end();

			$this->__helpShown[] = $id;
		}

		return $help;
	}

	public static function formatTextMessage($message) {
		return self::_formatMessage($message, null, null, true, false);
	}

	public function formatMessage($message, $tag = null, $text_reason = false, $absolute_url = false) {
		$message = $this->_formatMessage($message, $this, $tag, $text_reason, $absolute_url);

		/**
		 * Some strings to look for to allow links in reasons
		 */
		$link_tr = [
			'have an introductory membership' => ['controller' => 'Events', 'action' => 'wizard', 'membership'],
			'have a full membership' => ['controller' => 'Events', 'action' => 'wizard', 'membership'],
			'have a valid membership' => ['controller' => 'Events', 'action' => 'wizard', 'membership'],
		];

		// Maybe do link replacements to make the reason more easily understandable
		if (!$text_reason) {
			foreach ($link_tr as $text => $url) {
				if (stripos($message, $text) !== false) {
					if ($absolute_url) {
						$url = Router::url($url, true);
					} else {
						$url['return'] = AppController::_return();
					}
					$message = str_replace($text, $this->link($text, $url), $message);
				}
			}
		}

		return $message;
	}

	private static function _formatMessage($message, $htmlHelper, $tag, $text_reason, $absolute_url, $nested = false) {
		if (is_array($message)) {
			$tagOptions = [];
			if (!empty($message['class'])) {
				$tagOptions['class'] = $message['class'];
			}

			if (!empty($message['text'])) {
				$message = $message['text'];
			} else if (!empty($message['type'])) {
				switch ($message['type']) {
					case 'link':
						if ($text_reason) {
							$message = $message['link'];
						} else {
							if ($absolute_url) {
								$message = $htmlHelper->link($message['link'], Router::url($message['target'], true));
							} else {
								$message = $htmlHelper->link($message['link'], $message['target']);
							}
						}
						break;

					case 'postLink':
						if ($text_reason) {
							$message = $message['link'];
						} else {
							if ($absolute_url) {
								$message = $htmlHelper->Form->postLink($message['link'], Router::url($message['target'], true));
							} else {
								$message = $htmlHelper->Form->postLink($message['link'], $message['target']);
							}
						}
						break;

					default:
						trigger_error("Unknown replacement type '{$message['type']}'", E_USER_ERROR);
				}
			} else if (!empty($message['format'])) {
				if (empty($message['replacements'])) {
					trigger_error('\'format\' type messages must also include replacements.', E_USER_ERROR);
				}
				foreach ($message['replacements'] as $key => $replacement) {
					if (is_array($replacement)) {
						$message['replacements'][$key] = self::_formatMessage($replacement, $htmlHelper, null, $text_reason, $absolute_url);
					}
				}

				// TODOSECOND: Test that this works when the string is already translated by the caller. We want the translation done there, so that the i18n extractor picks it up.
				$message = __($message['format'], $message['replacements']);
			} else if (!empty($message['AND'])) {
				foreach ($message['AND'] as $key => $value) {
					$message['AND'][$key] = self::_formatMessage($value, $htmlHelper, null, $text_reason, $absolute_url, true);
				}

				$message = implode(__(' AND '), $message['AND']);
				if ($nested) {
					$message = __('({0})', $message);
				}
			} else if (!empty($message['OR'])) {
				foreach ($message['OR'] as $key => $value) {
					$message['OR'][$key] = self::_formatMessage($value, $htmlHelper, null, $text_reason, $absolute_url, true);
				}

				$message = implode(__(' OR '), $message['OR']);
				if ($nested) {
					$message = __('({0})', $message);
				}
			} else if (!empty($message['NOT'])) {
				$message = __(' NOT ') . self::_formatMessage($message['NOT'], $htmlHelper, null, $text_reason, $absolute_url);
			} else {
				pr($message);
				trigger_error('TODOTESTING', E_USER_WARNING);
				exit;
			}
		} else if (!is_string($message) && !empty($message)) {
			pr($message);
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		/**
		 * Common string replacements to make reasons more readable
		 */
		$tr = [
			'NOT not ' => '',
			'have a membership type of none' => 'not already have a valid membership',
			'have a membership type of intro' => 'have an introductory membership',
			'have a membership type of full' => 'have a full membership',
			'(have a valid membership)' => 'have a valid membership',

			'have an introductory membership OR have a full membership' => 'have a valid membership',
			'have a full membership OR have an introductory membership' => 'have a valid membership',

			'have a past membership type of none' => 'not have been a member in the past',
			'have a past membership type of intro' => 'have been an introductory member in the past',
			'have a past membership type of full' => 'have been a full member in the past',

			'have been an introductory member in the past OR have been a full member in the past' => 'have been a member in the past',
			'have been a full member in the past OR have been an introductory member in the past' => 'have been a member in the past',

			'have an upcoming membership type of none' => 'not have a membership for the upcoming year',
			'have an upcoming membership type of intro' => 'have an introductory membership for the upcoming year',
			'have an upcoming membership type of full' => 'have a full membership for the upcoming year',

			'have an introductory membership for the upcoming year OR have a full membership for the upcoming year' => 'have a valid membership for the upcoming year',
			'have a full membership for the upcoming year OR have an introductory membership for the upcoming year' => 'have a valid membership for the upcoming year',

			'have a People.birthdate greater than or equal to' => 'have been born on or after',
			'have a People.birthdate greater than' => 'have been born after',
			'have a People.birthdate less than or equal to' => 'have been born on or before',
			'have a People.birthdate less than' => 'have been born before',

			'have a People.gender of Woman' => 'be a woman',
			'have a People.gender of Man' => 'be a man',
			'have a People.roster_designation of Woman' => 'be a woman',
			'have a People.roster_designation of Open' => 'be a man',

			'have a People.has_dog of 0' => 'not have a dog',
			'have a People.has_dog of 1' => 'have a dog',

			'have a People.publish_email of 1' => 'publish your email address',
			'have a People.publish_home_phone of 1' => 'publish your home phone',
			'have a People.publish_work_phone of 1' => 'publish your work phone',
			'have a People.publish_mobile_phone of 1' => 'publish your mobile phone',

			'publish your home phone OR publish your work phone OR publish your mobile phone' => 'publish a phone number',
			'publish your email address OR publish a phone number' => 'publish your email address or a phone number',

			'have a team count of 0' => 'not be on another roster',
		];

		// Do string replacements to make the reason more easily understandable
		while (true) {
			$new_reason = strtr($message, $tr);
			if ($new_reason == $message) {
				break;
			}
			$message = $new_reason;
		}

		if ($tag) {
			return $htmlHelper->tag($tag, $message, $tagOptions);
		} else if (!empty($tagOptions)) {
			return $htmlHelper->tag('span', $message, $tagOptions);
		} else {
			return $message;
		}
	}

}
