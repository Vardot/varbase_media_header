Feature: Configure the Varbase Media Header settings
  As a Webmaster
  I want to enable the Varbase Media Header per entity type and bundle
  So that content types can show a universal media header

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The settings form exposes the entity-type enable options and the breadcrumbs option
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    Then I should see "Varbase Media Header Settings"
    And I should see "Select entity types which are going to use the varbase media header"
    And I should see "Enable Varbase Media Header for these entity types and bundles."
    And I should see "Hide breadcrumbs"
    And "#edit-submit" should be visible

  Scenario: Saving the settings confirms the configuration was stored
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    And I press "Save configuration"
    And wait for AJAX to finish
    Then I should see "The configuration options have been saved."

  Scenario: Enabling "Hide breadcrumbs" is remembered after saving
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    And I check the checkbox "#edit-hide-breadcrumbs"
    And I press "Save configuration"
    And wait for AJAX to finish
    Then I should see "The configuration options have been saved."
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    Then the "#edit-hide-breadcrumbs" checkbox should be checked
