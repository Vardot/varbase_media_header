@varbase_media_header @admin
Feature: Varbase Media Header - settings and permission
  As a site administrator
  I want the Varbase Media Header settings and permission to be available

  Scenario: The Media Header settings page is available to the administrator
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    Then I should see "Varbase Media Header Settings"
    And I should see "Save configuration"
    And I should not see "Page not found"

  Scenario: The Media Header administration permission is provided
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/admin/people/permissions/module/varbase_media_header"
    Then I should see "Administer varbase media header"
    And I should not see "Page not found"
