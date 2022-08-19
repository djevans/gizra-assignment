<?php

namespace Drupal\Tests\og_invite\ExistingSite;

use Drupal\Core\Url;
use Drupal\og\OgMembershipInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\og\Og;

class OgInviteTest extends ExistingSiteBase {

  use ContentTypeCreationTrait;

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testOgInvitation() {
    // Create a content type.
    $node_type = $this->createContentType();
    $this->markEntityForCleanup($node_type);

    $bundle = $node_type->id();

    // Set the new content type as a group type.
    Og::addGroup('node', $bundle);

    // Create a new group node.
    $group_node = $this->createNode(['type' => $bundle]);

    // Create a new user.
    $account = $this->createUser();

    // Log in as the new user.
    $this->drupalLogin($account);

    // View the group node.
    $this->drupalGet($group_node->toUrl());

    // Check that the message contains the group's and user's names.
    $message = t('Hi @user_name, click here if you would like to subscribe to this group called @group_name', [
      '@user_name' => $account->getDisplayName(),
      '@group_name' => $group_node->label(),
    ]);
    $this->assertSession()->pageTextContains($message);

    // Check that the subscribe link has the correct target.
    $route_params = [
      'entity_type_id' => 'node',
      'group' => $group_node->id(),
      'og_membership_type' => OgMembershipInterface::TYPE_DEFAULT,
    ];
    $subscribe_path = Url::fromRoute('og.subscribe', $route_params);
    $this->assertSession()->linkByHrefExists($subscribe_path->toString());
  }
}
