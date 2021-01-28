@block @block_thumblinks_action
Feature: Adding and configuring Thumbnails links action block
  In order to have the Thumbnails links block used
  As a admin
  I need to add the Thumbnails links block to the front page

  @javascript @_file_upload @runonly
  Scenario: Adding Thumbnails links block and I change the image, this should result in the new image being displayed.
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Thumbnail links and action" block
    And I configure the "Thumbnail links and action" block
    Then I should see "Title"
    And I set the field "Title" to "Trainings"
    Then I should see "CALL to Action"
    Then I should see "CALL to Action Title"
    Then I should see "Thumbnail 1 Title"
    Then I should see "Thumbnail 1 URL"
    Given I set the field "Thumbnail 1 Title" to "First Training"
    Given I set the field "Thumbnail 1 URL" to "http://www.myurl1.fr"
    And I upload "blocks/thumblinks_action/tests/fixtures/bookmark-new.png" file to "Thumbnail 1 Image" filemanager
    And I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//a[1][contains(@style, 'bookmark-new.png')]" "xpath_element" should exist
    And I configure the "Trainings" block
    And I delete "bookmark-new.png" from "Thumbnail 1 Image" filemanager
    And I upload "blocks/thumblinks_action/tests/fixtures/document-edit.png" file to "Thumbnail 1 Image" filemanager
    And I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//a[1][contains(@style, 'document-edit.png')]" "xpath_element" should exist

  @javascript @_file_upload
  Scenario: Adding Thumbnails links block and several images
    Given I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Thumbnail links and action" block
    And I configure the "Thumbnail links and action" block
    Then I should see "Title"
    And I set the field "Title" to "Trainings"
    And I press "Add 1 more thumbnails"
    Given I set the field "Thumbnail 1 Title" to "First Training"
    Given I set the field "Thumbnail 1 URL" to "http://www.myurl1.fr"
    Given I set the field "Thumbnail 2 Title" to "Second Training"
    Given I set the field "Thumbnail 2 URL" to "http://www.myurl2.fr"
    # Issue here (xpath node is not visible and it should be visible)
    # Seems to be due to the fact that we have several filemanagers on the same page and the second one opened
    # does not appear to behat as visible. A workaround here is to save them in sequence.
    And I upload "blocks/thumblinks_action/tests/fixtures/bookmark-new.png" file to "Thumbnail 1 Image" filemanager
    And I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//a[1][contains(@style, 'bookmark-new.png')]" "xpath_element" should exist
    And I configure the "Trainings" block
    And I upload "blocks/thumblinks_action/tests/fixtures/document-edit.png" file to "Thumbnail 2 Image" filemanager
    And I press "Save changes"
    Then "//*[contains(@class, 'block-thumblinks-action')]//a[2][contains(@style, 'document-edit.png')]" "xpath_element" should exist
