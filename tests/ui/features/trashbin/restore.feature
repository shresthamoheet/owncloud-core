@insulated
Feature: Restore deleted files/folders
As a user
I would like to restore files/folders
So that I can recover accidently deleted files/folders in ownCloud

	Background:
		Given a regular user exists
		And I am logged in as a regular user
		And I am on the files page

	Scenario: Restore files
		When I delete the file "data.zip" 
		Then the file "data-zip" should be listed in the trashbin
		When I restore the file "data.zip"
		Then the file "data.zip" should not be listed 
		When I am on the files page
		Then the file "data.zip" should be listed 

	Scenario: Restore folder
		When I delete the folder "folder with space" 
		Then the folder "folder with space" should be listed in the trashbin 
		When I restore the folder "folder with space"
		Then the file "folder with space" should not be listed 
		When I am on the files page
		Then the folder "folder with space" should be listed 