@block @block_thumblinks_action
Feature: Adding and configuring Thumbnails links action block and following their links
  In order to follow the links of the block and use its action
  As a admin
  I need to add the Thumbnails links block to the front page

  @javascript
  Scenario: Setting a thumbnail link and get into it
    Given I log in as "admin"
    And I am on site homepage
    When I turn editing mode on
    And I add the "Thumbnail links and action" block
    And I configure the "Thumbnail links and action" block
    Then I should see "Title"
    And I should see "CALL to Action"
    And I should see "CALL to Action Title"
    And I should see "Thumbnail 1 Title"
    And I should see "Thumbnail 1 URL"
    Given I set the field "Title" to "Test thumbnail link"
    And I set the field "Thumbnail 1 Title" to "First Training"
    And I set the field "Thumbnail 1 URL" to "https://moodle.org/"
    When I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//a[1]" "xpath_element" should exist
    Given I click on "//*[contains(@class, 'block-thumblinks-action')]//a[1]" "xpath_element"
    Then the url should match "https://moodle.org/"

  @javascript
  Scenario: Setting an action link and get into it
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Thumbnail links and action" block
    When I configure the "Thumbnail links and action" block
    Then I should see "Title"
    And I should see "CALL to Action"
    And I should see "CALL to Action Title"
    Given I set the field "Title" to "Test action link"
    And I set the field "CALL to Action Title" to "Test Action"
    And I set the field "CALL to Action" to "https://moodle.org/"
    When I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//*[contains(@class, 'btn btn-primary')]" "xpath_element" should exist
    Given I click on "//*[contains(@class, 'block-thumblinks-action')]//*[contains(@class, 'btn btn-primary')]" "xpath_element"
    Then the url should match "https://moodle.org/"
