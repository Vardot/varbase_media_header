Feature: The Varbase Media Header block is provided
  As a site builder
  I want the "Varbase Media Header" block plugin available in the block library
  So that I can place the media header where I need it

  # The module provides a block plugin (id varbase_media_header_block) with the
  # admin label "Varbase Media Header". Assert it is discoverable to place in
  # the Varbase front-end theme (olivero).

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The Varbase Media Header block appears in the olivero block library
    When I open the administration page "/admin/structure/block/library/olivero"
    Then I should see "Varbase Media Header"

  Scenario: The Varbase Media Header block configuration form offers the media view mode
    When I open the administration page "/admin/structure/block/add/varbase_media_header_block/olivero"
    Then I should see "Media view mode"
    And "#edit-settings-vmh-media-view-mode" should be visible
