<?php
/**
* ownCloud
*
* @author Artur Neumann
* @copyright 2017 Artur Neumann info@individual-it.net
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;

use Page\LoginPage;

require_once 'bootstrap.php';

/**
 * Login context.
 */
class LoginContext extends RawMinkContext implements Context
{
	private $loginPage;
	private $filesPage;
	private $regularUserPassword;
	public function __construct(LoginPage $loginPage)
	{
		$this->loginPage = $loginPage;
	}
	
	/**
	 * @Given I am on login page
	 */
	public function iAmOnLoginPage()
	{
		$this->loginPage->open();
	}
	
	/**
	 * @When I login with username :username and password :password
	 */
	public function iLoginWithUsernameAndPassword($username, $password)
	{
		$this->filesPage = $this->loginPage->loginAs($username, $password);
		$this->filesPage->waitTillPageIsloaded(10);
	} 
	
	/**
	 * @When I login with an existing user and a correct password
	 */
	public function iLoginWithAnExistingUserAndACorrectPassword()
	{
		$this->filesPage = $this->loginPage->loginAs("user1", $this->regularUserPassword);
		$this->filesPage->waitTillPageIsloaded(10);
	}
	
	/**
	 * @Then I should be redirected to a page with the title :title
	 */
	public function iShouldBeRedirectedToAPageWithTheTitle($title)
	{
		
		$actualTitle = $this->filesPage->find(
			'xpath', './/title'
		)->getHtml();
		PHPUnit_Framework_Assert::assertEquals($title, $actualTitle);
	}
	
	/** @BeforeScenario*/
	public function setUpScenario(BeforeScenarioScope $scope)
	{
		$this->regularUserPassword = $scope->getSuite()->getSettings() ['context'] ['parameters'] ['regularUserPassword'];
	}
	
}
