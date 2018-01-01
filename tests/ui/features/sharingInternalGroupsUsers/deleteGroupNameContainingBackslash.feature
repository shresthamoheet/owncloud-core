Feature: manage groups
As an admin
I want to manage groups
So that access to resources can be controlled more effectively

	Background:
		Given a regular user exists but is not initialized
		And I am logged in as admin
		And I am on the users page

	Scenario: delete group name containing "/"
		And these groups exist:
		|groupname     |
		|test/test   |
		|&@/?        |
		|123/        |
		And I am on the users page
		When I delete these groups:
		|groupname|
		|test/test   |
		|&@/?        |
		|123/        |
		And the users page is reloaded
