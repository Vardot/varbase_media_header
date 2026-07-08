@wip
Feature: The node form shows a Media Header group when enabled
  As a content editor
  I want a "Media Header" group on the node form
  So that I can pick the header style and background media for the page

  # The module's node form hook adds a details element titled "Media Header"
  # (vmh_group) grouping field_page_header_style + field_media. The group is
  # #optional, so it only renders once the content type is enabled in the
  # settings form (which provisions field_page_header_style + field_media on
  # the bundle and adds them to the default form display). On a bare Standard
  # site the "page" bundle has neither field yet, so this whole feature is
  # tagged @wip until the recipe/CI enables a bundle for Varbase Media Header.
  # See NOTES.md for what the recipe must provision to make this pass.

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: Enabling the Basic page content type shows the Media Header group on its node form
    When I open the administration page "/admin/config/varbase/varbase-media-header"
    And I check the checkbox "#edit-varbase-media-header-settings-node-page"
    And I press "Save configuration"
    And wait for AJAX to finish
    Then I should see "The configuration options have been saved."
    When I open the administration page "/node/add/page"
    Then I should see "Media Header"
