<?php

namespace Drupal\stanford_events_pdb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Events.
 *
 * @package Drupal\stanford_events_pdb\Controller
 */
class EventsJson extends ControllerBase {

  protected $stanfordUrl = 'http://events.stanford.edu/xml/drupal/v2.php?organization=19';

  protected $localUrl = 'jsonapi/node/stanford_event';

  /**
   * @var \GuzzleHttp\Client
   */
  protected $guzzle;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('request_stack'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(GuzzleClient $client, RequestStack $request_stack, EntityTypeManagerInterface $entity_manager) {
    $this->guzzle = $client;
    $this->request = $request_stack;
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * @return string
   */
  public function getLocalUrl() {
    $base_url = $this->request->getMasterRequest()->getSchemeAndHttpHost();
    return $base_url . base_path() . $this->localUrl;
  }

  /**
   * Translates the XML from events.stanford.edu into json object.
   *
   * @return bool|\Psr\Http\Message\ResponseInterface|\Symfony\Component\HttpFoundation\Response
   *   The json object.
   */
  public function eventsJson() {

    $events = [];
    try {
      $response = $this->guzzle->get($this->stanfordUrl);
      $data = (string) $response->getBody();
      $xml = simplexml_load_string($data);
      $json = json_encode($xml);
      $events = json_decode($json, TRUE);
    }
    catch (RequestException $e) {
      // Dont do anything.
    }

    try {
      $response = $this->guzzle->get($this->getLocalUrl());
      $data = (string) $response->getBody();
      $local_events = json_decode($data, TRUE);
      foreach ($this->processLocalData($local_events) as $event) {
        $events['Event'][] = $event;
      }
    }
    catch (RequestException $e) {
      // Dont do anything.
    }

    $response = new Response();
    $response->setContent(json_encode($events));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  /**
   * @param $data
   *
   * @return array
   */
  protected function processLocalData($data) {
    $events = [];
    foreach ($data['data'] as $event) {
      $event = $event['attributes'];
      $event['description'] = $event['description']['value'];
      $events[] = $event;
    }
    return $events;
  }

}
