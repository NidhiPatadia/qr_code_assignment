<?php

namespace Drupal\jugaad_patch\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use \Drupal\node\Entity\Node;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use \Drupal\node\NodeInterface;
use \Drupal\Core\File\FileSystemInterface;
use Endroid\QrCode\Builder\Builder;
use \Drupal\Component\Render\FormattableMarkup; 

/**
 * QRCode Block.
 *
 * @Block(
 *   id = "qr_code_block",
 *   admin_label = @Translation("QR Code block"),
 * )
 */
class QRCodeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The pluginId.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   currentRouteMatch variable.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $currentRouteMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    global $base_url;

    $node = $this->currentRouteMatch->getParameter('node');
    if ($node instanceof NodeInterface && $node->bundle() == 'unicorn_products') {
      $node_data = Node::load($node->id());
      $uri = $node_data->field_product_link->uri;

      $result = Builder::create()
      ->writer(new PngWriter())
      ->writerOptions([])
      ->data($uri)
      ->encoding(new Encoding('UTF-8'))
      ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
      ->size(300)
      ->margin(10)
      ->build();
      $uri_qr = $result->getDataUri();
      
      return array(
        '#type' => 'markup',
        '#markup' => new FormattableMarkup('<p>Scan this QR to visit the product</p><img src="@uri" alt="Purchase QRCode" width="220" height="185">', ['@uri' => $uri_qr,]),
      );
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
      return 0;
  }
}