<?php

/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright 2017 Artur Neumann artur@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Session;
use Behat\Mink\Element\NodeElement;
use WebDriver\Exception as WebDriverException;
use WebDriver\Key;
use Page\OwncloudPageElement\OCDialog;

/**
 * Owncloud page.
 */
class OwncloudPage extends Page {

	protected $userNameDisplayId = "expandDisplayName";
	protected $ocDialogXpath = ".//*[@class='oc-dialog']";

	/**
	 * used to store the unchanged path string when $path gets changed
	 *
	 * @var string
	 */
	protected $originalPath = null;

	/**
	 * @param Session $session
	 * @param int $timeout_msec
	 * @return void
	 */
	public function waitTillPageIsLoaded(
		Session $session,
		$timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
		$currentTime = microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			$loadingIndicator = $this->find("css", '.loading');
			if (!is_null($loadingIndicator)) {
				$visibility = $this->elementHasCSSValue(
					$loadingIndicator, 'visibility', 'visible'
				);
				if ($visibility === false) {
					break;
				}
			}
			usleep(STANDARDSLEEPTIMEMICROSEC);
			$currentTime = microtime(true);
		}

		if ($currentTime > $end) {
			throw new \Exception(
				"OwncloudPage:waitTillPageIsLoaded:timeout waiting for page to load"
			);
		}

		$this->waitForOutstandingAjaxCalls($session);
	}

	/**
	 *
	 * @param string $xpath
	 * @param int $timeout_msec
	 * @return void
	 */
	public function waitTillElementIsNull(
		$xpath,
		$timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
		$currentTime = microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			try {
				$element = $this->find("xpath", $xpath);
			} catch (WebDriverException $e) {
				break;
			}
			if ($element === null) {
				break;
			}
			usleep(STANDARDSLEEPTIMEMICROSEC);
			$currentTime = microtime(true);
		}
	}

	/**
	 *
	 * @param string $xpath
	 * @param int $timeout_msec
	 * @return NodeElement|null
	 */
	public function waitTillElementIsNotNull(
		$xpath, $timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
		$currentTime = microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			try {
				/**
				 * @var NodeElement $element
				 */
				$element = $this->find("xpath", $xpath);
				if ($element === null || !$element->isValid()) {
					usleep(STANDARDSLEEPTIMEMICROSEC);
				} else {
					return $element;
				}
			} catch (WebDriverException $e) {
				usleep(STANDARDSLEEPTIMEMICROSEC);
			}
			$currentTime = microtime(true);
		}

		return null;
	}

	/**
	 * Get the text of the first notification
	 *
	 * @throws ElementNotFoundException
	 * @return string
	 */
	public function getNotificationText() {
		$notificationElement = $this->findById("notification");

		if ($notificationElement === null) {
			throw new ElementNotFoundException(
				"getNotificationText:could not find notification element"
			);
		}

		return $notificationElement->getText();
	}

	/**
	 * Get the text of any notifications
	 *
	 * @throws ElementNotFoundException
	 * @return array
	 */
	public function getNotifications() {
		$notificationsText = array();
		$notifications = $this->findById("notification");

		if ($notifications === null) {
			throw new ElementNotFoundException(
				"getNotifications:could not find notification element"
			);
		}

		foreach ($notifications->findAll("xpath", "div") as $notification) {
			array_push($notificationsText, $notification->getText());
		}
		return $notificationsText;
	}

	/**
	 * Get all open oc dialogs
	 *
	 * @return OCDialog[]
	 */
	public function getOcDialogs() {
		$ocDialogs = [];
		$ocDialogElements = $this->findAll("xpath", $this->ocDialogXpath);
		foreach ($ocDialogElements as $element) {
			/**
			 *
			 * @var \Page\OwncloudPageElement\OCDialog $ocDialog
			 */
			$ocDialog = $this->getPage("OwncloudPageElement\\OCDialog");
			$ocDialog->setElement($element);
			$ocDialogs[] = $ocDialog;
		}
		return $ocDialogs;
	}

	/**
	 * Open the settings menu
	 *
	 * @throws ElementNotFoundException
	 * @return Page
	 */
	public function openSettingsMenu() {
		$userNameDisplayElement = $this->findById($this->userNameDisplayId);

		if ($userNameDisplayElement === null) {
			throw new ElementNotFoundException(
				"openSettingsMenu:could not find userNameDisplay element"
			);
		}

		$userNameDisplayElement->click();

		return $this->getPage("OwncloudPageElement\\SettingsMenu");
	}
	/**
	 * finds the logged-in username displayed in the top right corner
	 *
	 * @throws ElementNotFoundException
	 * @return string
	 */
	public function getMyUsername() {
		$userNameDisplayElement = $this->findById($this->userNameDisplayId);

		if ($userNameDisplayElement === null) {
			throw new ElementNotFoundException(
				"getMyUsername:could not find userNameDisplay element"
			);
		}

		return $userNameDisplayElement->getText();
	}

	/**
	 * return the path to the relevant page
	 *
	 * @return string
	 */
	public function getPagePath() {
		return $this->getPath();
	}

	/**
	 *
	 * @param string $path
	 * @return void
	 */
	public function setPagePath($path) {
		if ($this->originalPath === null) {
			$this->originalPath = $this->path;
		}
		$this->path = $path;
	}

	/**
	 * returns the unchanged path
	 *
	 * @return string
	 */
	public function getOriginalPath() {
		if ($this->originalPath !== null) {
			return $this->originalPath;
		} else {
			return $this->getPath();
		}
	}

	/**
	 * Gets the Coordinates of a Mink Element
	 *
	 * @param Session $session
	 * @param NodeElement $element
	 * @return array
	 */
	public function getCoordinatesOfElement($session, $element) {
		$elementXpath = str_replace('"', '\"', $element->getXpath());

		return $session->evaluateScript(
			'return document.evaluate( "' .
			$elementXpath .
			'",document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null)' .
			'.singleNodeValue.getBoundingClientRect();'
		);
	}

	/**
	 * Gets the Window Height
	 *
	 * @param Session $session
	 * @return int
	 */
	public function getWindowHeight($session) {
		return $session->evaluateScript(
			'return $(window).height();'
		);
	}

	/**
	 * scrolls to a position in a specified element
	 *
	 * @param string $jQuerySelector e.g. "#app-content"
	 * @param int|string $position number or JS function that returns a number
	 * @param Session $session
	 * @return void
	 */
	public function scrollToPosition($jQuerySelector, $position, Session $session) {
		$session->executeScript(
			'jQuery("' . $jQuerySelector . '").scrollTop(' . $position . ');'
		);
	}

	/**
	 * waits till all ajax calls are finished (jQuery.active === 0)
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 * @return void
	 */
	public function waitForOutstandingAjaxCalls(
		Session $session,
		$timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
		$timeout_msec = (int) $timeout_msec;
		if ($timeout_msec <= 0) {
			throw new \InvalidArgumentException("negative or zero timeout");
		}
		$currentTime = microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			try {
				$waitingResult = $session->wait(
					STANDARDSLEEPTIMEMILLISEC,
					"(typeof jQuery != 'undefined' && (0 === jQuery.active))"
				);
				if ($waitingResult === true) {
					break;
				}
			} catch (\Exception $e) {
				//show Exception message, but do not throw it
				echo $e->getMessage() . "\n";
			} finally {
				usleep(STANDARDSLEEPTIMEMICROSEC);
				$currentTime = microtime(true);
			}
		}
		if ($currentTime > $end) {
			$message = "INFORMATION: timed out waiting for outstanding ajax calls";
			echo $message;
			error_log($message);
		}
	}

	/**
	 * waits till at least one Ajax call is active
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 * @return void
	 */
	public function waitForAjaxCallsToStart(Session $session, $timeout_msec = 1000) {
		$timeout_msec = (int) $timeout_msec;
		if ($timeout_msec <= 0) {
			throw new \InvalidArgumentException("negative or zero timeout");
		}
		$currentTime = microtime(true);
		$end = $currentTime + ($timeout_msec / 1000);
		while ($currentTime <= $end) {
			if ((int) $session->evaluateScript("jQuery.active") > 0) {
				break;
			}
			usleep(STANDARDSLEEPTIMEMICROSEC);
			$currentTime = microtime(true);
		}
	}

	/**
	 * waits till at least one Ajax call is active and
	 * then waits till all outstanding ajax calls finish
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 * @return void
	 */
	public function waitForAjaxCallsToStartAndFinish(
		Session $session,
		$timeout_msec = STANDARDUIWAITTIMEOUTMILLISEC
	) {
		$start = microtime(true);
		$this->waitForAjaxCallsToStart($session);
		$end = microtime(true);
		$timeout_msec = $timeout_msec - (($end - $start) * 1000);
		$timeout_msec = max($timeout_msec, MINIMUMUIWAITTIMEOUTMILLISEC);
		$this->waitForOutstandingAjaxCalls($session, $timeout_msec);
	}

	/**
	 * Determine if a Mink NodeElement contains a specific
	 * css rule attribute value.
	 *
	 * @param NodeElement $element
	 *   NodeElement previously selected with
	 *   $this->getSession()->getPage()->find().
	 * @param string $property
	 *   Name of the CSS property, such as "visibility".
	 * @param string $value
	 *   Value of the specified rule, such as "hidden".
	 *
	 * @return NodeElement|bool
	 *   The NodeElement selected if true, FALSE otherwise.
	 */
	protected function elementHasCSSValue($element, $property, $value) {
		$exists = false;
		$style = $element->getAttribute('style');
		if ($style) {
			if (preg_match(
				"/(^{$property}:|; {$property}:) ([a-z0-9]+);/i",
				$style, $matches
			)
			) {
				$found = array_pop($matches);
				if ($found == $value) {
					$exists = $element;
				}
			}
		}

		return $exists;
	}

	/**
	 * sends an END key and then BACKSPACEs to delete the current value
	 * then sends the new value
	 * checks the set value and sends the Escape key + throws an exception
	 * if the value is not set correctly
	 *
	 * @param NodeElement $inputField
	 * @param string $value
	 * @throws \Exception
	 * @return void
	 */
	protected function cleanInputAndSetValue(NodeElement $inputField, $value) {
		$resultValue = $inputField->getValue();
		$existingValueLength = strlen($resultValue);
		$deleteSequence
			= Key::END . str_repeat(Key::BACKSPACE, $existingValueLength);
		$inputField->setValue($deleteSequence);
		$inputField->setValue($value);
		$resultValue = $inputField->getValue();
		if ($resultValue !== $value) {
			$inputField->keyUp(27); //send escape
			throw new \Exception("value of input field is not what we expect");
		}
	}

	/**
	 * Surround the text with single or double quotes, whichever does not
	 * already appear in the text. If the text contains both single and
	 * double quotes, then throw an InvalidArgumentException.
	 *
	 * The returned string is intended for use as part of an xpath (v1).
	 * xpath (v1) has no way to escape the quote character within a string
	 * literal. So there is no way to directly use a string containing
	 * both single and double quotes.
	 *
	 * @param string $text
	 * @return string the text surrounded by single or double quotes
	 * @throws \InvalidArgumentException
	 */
	public function quotedText($text) {
		if (strstr($text, "'") === false) {
			return "'" . $text . "'";
		} else if (strstr($text, '"') === false) {
			return '"' . $text . '"';
		} else {
			// The text contains both single and double quotes.
			// With current xpath v1 there is no way to encode that.
			throw new \InvalidArgumentException(
				"mixing both single and double quotes is unsupported - '"
				. $text . "'"
			);
		}
	}
}
