Feature: Admin management of Stripe plans
  In order to control the predefined amounts available to customers
  As a Site Administrator
  I need an interface to the Stripe service that allows me to read and write the existing plans


Scenario: Admin user loads existing plans from Stripe
    Given I am logged in to the wordpress admin interface
    And I am on the "Manage Stripe Plans" options page
    When I the page loads
    Then I see a list of existing plans as defined in Stripe


Scenario: Create a new plan
  Given I am logged in to the wordpress admin interface
  And I am on the "Manage Stripe Plans" options page
  When I click the "create new plan" button
  Then the "Edit/Create Plan" form appears


Scenario: Edit an existing plan
  Given I am logged in to the wordpress admin interface
  And I am on the "Manage Stripe Plans" options page
  When I select one of the existing plans
  Then the "Edit/Create Plan" form  appears 
  And the existing plan details are prefilled


Scenario: Save an open plan with a new valid value
  Given I am logged in as "admin"
  And I am on the "Edit Stripe Plan" page
  When I change the plan value to "100"
  And I click the save button
  Then the "Manage Stripe Plans" options page loads
  And the 