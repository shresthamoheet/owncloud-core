@insulated
Feature: Mark file as favourite

As a user
I would like to mark any file/folder as favourite
So that I can find my favourite file/folder easily

	Background:
		Given I am logged in as admin
		And I am on the files page

	Scenario: mark a file as favourite and list it in favourites page
		When I mark the file "data.zip" as favourite 
		Then the file "data.zip" should be marked as favourite
		And the files page is reloaded
		Then the file "data.zip" should be marked as favourite
		And the file "data.zip" should be listed in the favourites page

	Scenario: mark a folder as favourite and list it in favourites page
		When I mark the folder "simple-folder" as favourite 
		Then the folder "simple-folder" should be marked as favourite
		And the files page is reloaded
		Then the folder "simple-folder" should be marked as favourite
		And the folder "simple-folder" should be listed in the favourites page
