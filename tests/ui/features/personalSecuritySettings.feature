Feature: personalSecuritySettings

	Scenario: create new app password
		Given I am logged in as a regular user
		And I am on personal security settings page
		When I create a new App password
		Then the new app should be listed in the App passwords list
		And my username and the app password should be displayed
