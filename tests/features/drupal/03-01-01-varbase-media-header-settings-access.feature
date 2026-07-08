Feature: Varbase Media Header settings page access
  As a site builder
  I want the Varbase Media Header settings page protected by its permission
  So that only users granted "administer varbase media header" can configure it

  # The route varbase_media_header.settings requires the
  # "administer varbase media header" permission. Assert the real access
  # behaviour by role, not just that a URL responds.

  Scenario: An anonymous visitor is denied the settings page
    Given I am an anonymous visitor
    When I am on "/admin/config/varbase/varbase-media-header"
    Then I should see "Access denied"
    And I should not see "Varbase Media Header Settings"

  Scenario: A non-admin authenticated user without the permission is denied the settings page
    Given I am a logged in user with the "Authenticated" user
    When I am on "/admin/config/varbase/varbase-media-header"
    Then I should see "Access denied"
    And I should not see "Save configuration"

  Scenario: The Webmaster can reach the Varbase Media Header settings page
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    Then I should see "Varbase Media Header Settings"

  Scenario: The Varbase Media Header permission is listed on the module permissions page
    Given I am a logged in user with the "Webmaster" user
    When I open the administration page "/admin/people/permissions/module/varbase_media_header"
    Then I should see "Administer varbase media header"
