<?php

namespace Drupal\shanfont\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for invalidating cache when Shan Font config changes.
 */
class ShanFontCacheInvalidator implements EventSubscriberInterface {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a ShanFontCacheInvalidator object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
    ];
  }

  /**
   * Invalidates cache tags when Shan Font configuration is saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The configuration event.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    
    if ($config->getName() === 'shanfont.settings') {
      // Invalidate rendered cache to ensure new font settings take effect
      $this->cacheTagsInvalidator->invalidateTags([
        'rendered',
        'library_info',
        'shanfont_settings',
      ]);
    }
  }

}