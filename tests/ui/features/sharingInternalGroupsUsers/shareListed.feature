@insulated
Feature: Sharing files and folders with internal users
As a user
I want to share files and folders with other users
So that those users can access the files and folders

	Background:
		Given these users exist:
			|username|password|displayname|email          |
			|admin   |admin   |admin     |admin@oc.com.np|
			|user1   |1234    |User One   |u1@oc.com.np   |
			 
		And I am on the login page
		And I login with username "admin" and password "admin"
		When the folder "simple-folder" is shared with the user "User One"
		And the file "testimage.jpg" is shared with the user "User One"
		And I relogin with username "user1" and password "1234"
	