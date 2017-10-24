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

namespace Page\FilesPageElement;

use Page\OwncloudPage;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use Behat\Mink\Element\NodeElement;

/**
 * Object of a row on the FilesPage
 */
class FileRow extends OwnCloudPage {
	/**
	 * @var NodeElement of this row
	 */
	protected $rowElement;

	/**
	 * name of the file
	 *
	 * @var string
	 */
	protected $name;
	protected $fileActionMenuBtnXpath = "//a[@data-action='menu']";
	protected $shareBtnXpath = "//a[@data-action='Share']";
	protected $loadingIndicatorXpath = ".//*[@class='loading']";
	protected $fileRenameInputXpath = "//input[contains(@class,'filename')]";
	protected $fileBusyIndicatorXpath = ".//*[@class='thumbnail' and contains(@style,'loading')]";
	protected $fileTooltipXpath = ".//*[@class='tooltip-inner']";
	protected $thumbnailXpath = "//*[@class='thumbnail']";
	protected $fileLinkXpath = "//span[@class='nametext']";

	/**
	 * sets the NodeElement for the current file row
	 * a little bit like __construct() but as we access this "sub-page-object"
	 * from an other Page Object by $this->getPage("FilesPageElement\\FileRow")
	 * there is no real __construct() that can take arguments
	 *
	 * @param \Behat\Mink\Element\NodeElement $rowElement
	 * @return void
	 */
	public function setElement(NodeElement $rowElement) {
		$this->rowElement = $rowElement;
	}

	/**
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * finds the Action Button
	 *
	 * @return \Behat\Mink\Element\NodeElement
	 * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
	 */
	public function findFileActionButton() {
		$actionButton = $this->rowElement->find(
			"xpath", $this->fileActionMenuBtnXpath
		);
		if ($actionButton === null) {
			throw new ElementNotFoundException(
				"could not find actionButton in fileRow"
			);
		}
		return $actionButton;
	}

	/**
	 * finds and clicks the file actions button
	 *
	 * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
	 * @return void
	 */
	public function clickFileActionButton() {
		$this->findFileActionButton()->click();
	}

	/**
	 * opens the file action menu
	 *
	 * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
	 * @return FileActionsMenu
	 */
	public function openFileActionsMenu() {
		$this->clickFileActionButton();
		$filesPage = $this->getPage('FilesPage');
		$actionMenuElement = $filesPage->findFileActionMenuElement();
		$actionMenu = $this->getPage('FilesPageElement\\FileActionsMenu');
		$actionMenu->setElement($actionMenuElement);
		return $actionMenu;
	}

	/**
	 * finds and returns the share button element
	 *
	 * @throws ElementNotFoundException
	 * @return \Behat\Mink\Element\NodeElement
	 */
	public function findSharingButton() {
		$shareBtn = $this->rowElement->find("xpath", $this->shareBtnXpath);
		if (is_null($shareBtn)) {
			throw new ElementNotFoundException(
				"could not find sharing button in fileRow"
			);
		}
		return $shareBtn;
	}

	/**
	 * opens the sharing dialog
	 *
	 * @throws ElementNotFoundException
	 * @return SharingDialog
	 */
	public function openSharingDialog() {
		$this->findSharingButton()->click();
		$this->waitTillElementIsNull($this->loadingIndicatorXpath);
		return $this->getPage("FilesPageElement\\SharingDialog");
	}

	/**
	 * finds the input field to rename the file/folder
	 *
	 * @throws ElementNotFoundException
	 * @return \Behat\Mink\Element\NodeElement
	 */
	public function findRenameInputField() {
		$inputField = $this->rowElement->find(
			"xpath", $this->fileRenameInputXpath
		);
		if ($inputField === null) {
			throw new ElementNotFoundException("could not find input field");
		}
		return $inputField;
	}

	/**
	 * renames the file
	 *
	 * @param string $toName
	 * @return void
	 */
	public function rename($toName) {
		$actionMenu = $this->openFileActionsMenu();
		$actionMenu->rename();
		$this->waitTillElementIsNotNull($this->fileRenameInputXpath);
		$inputField = $this->findRenameInputField();
		$this->cleanInputAndSetValue($inputField, $toName);
		$inputField->blur();
		$this->waitTillElementIsNull($this->fileBusyIndicatorXpath);
	}

	/**
	 * deletes the file
	 *
	 * @return void
	 */
	public function delete() {
		$actionMenu = $this->openFileActionsMenu();
		$actionMenu->delete();
		$this->waitTillElementIsNull($this->fileBusyIndicatorXpath);
	}

	/**
	 * finds and returns the tooltip element
	 *
	 * @throws ElementNotFoundException
	 * @return \Behat\Mink\Element\NodeElement
	 */
	public function findTooltipElement() {
		$element = $this->rowElement->find("xpath", $this->fileTooltipXpath);
		if ($element === null) {
			throw new ElementNotFoundException(
				"could not find tooltip element of file " . $this->name
			);
		}
		return $element;
	}

	/**
	 * returns the tooltip text
	 *
	 * @return string
	 */
	public function getTooltip() {
		return $this->findTooltipElement()->getText();
	}

	/**
	 * finds and returns the thumbnail of the file
	 *
	 * @throws ElementNotFoundException
	 * @return \Behat\Mink\Element\NodeElement
	 */
	public function findThumbnail() {
		$thumbnail = $this->rowElement->find("xpath", $this->thumbnailXpath);
		if (is_null($thumbnail)) {
			throw new ElementNotFoundException(
				"could not find thumbnail of file " . $this->name
			);
		}
		return $thumbnail;
	}

	/**
	 * selects this row for batch action e.g. download or delete
	 *
	 * @return void
	 */
	public function selectForBatchAction() {
		$this->findThumbnail()->click();
	}

	/**
	 * find and return the link to the file/folder
	 *
	 * @throws ElementNotFoundException
	 * @return \Behat\Mink\Element\NodeElement
	 */
	public function findFileLink() {
		$linkElement = $this->rowElement->find("xpath", $this->fileLinkXpath);
		if (is_null($linkElement)) {
			throw new ElementNotFoundException(
				"could not find link to " . $this->name
			);
		}
		return $linkElement;
	}

	/**
	 * opens the current file or folder by clicking on the link
	 *
	 * @return void
	 */
	public function openFileFolder() {
		$this->findFileLink()->click();
	}
}