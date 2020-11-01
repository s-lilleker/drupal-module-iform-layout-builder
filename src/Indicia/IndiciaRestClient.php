<?php

namespace Drupal\iform_layout_builder\Indicia;

use Drupal\Core\Entity\EntityInterface;
use Drupal\simple_oauth\Controller\Oauth2Token;

class IndiciaRestClient {

  /**
   * Retrieve the auth header for Indicia REST requests.
   *
   * @return string
   *   Auth header to sign the request with a JWT token.
   */
  protected function getAuthHeader() {
    $keyFile = \Drupal::service('file_system')->realpath("private://") . '/rsa_private.pem';
    if (!file_exists($keyFile)) {
      \Drupal::logger('iform_layout_builder')->error('Incorrect configuration - Iform layout builder needs a private key file in the Drupal private file system.');
      \Drupal::messenger()->addError(t('Incorrect configuration - Iform layout builder needs a private key file in the Drupal private file system.'));
      return FALSE;
    }
    $privateKey = file_get_contents($keyFile);
    $payload = [
      'iss' => \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]),
      'http://indicia.org.uk/user:id' => \Drupal::currentUser()->id(),
      'exp' => time() + 300,
    ];
    $modulePath = \Drupal::service('module_handler')->getModule('iform')->getPath();
    require_once "$modulePath/lib/php-jwt/vendor/autoload.php";
    $token = \Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');
    return "Authorization: Bearer $token";
  }

  /**
   * Make an Indicia REST API call.
   *
   * @param string $endpoint
   *   Endpoint to call, e.g. 'surveys' or 'surveys/1'.
   * @param string $method
   *   Http method, e.g. 'POST'.
   * @param array|string $data
   *   Data to be sent as a payload. Array or JSON encoded string.
   *
   * @return array
   *   Response information.
   */
  protected function getRestResponse($endpoint, $method, $payload = NULL, $params = []) {
    $config = \Drupal::config('iform.settings');
    $url = $config->get('base_url') . "/index.php/services/rest/$endpoint";
    \Drupal::logger('iform_layout_builder')->notice("Calling $url");
    if (!empty($params)) {
      $url .= '?' . http_build_query($params);
    }
    $session = curl_init($url);
    curl_setopt($session, CURLOPT_HEADER, TRUE);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, TRUE);
    $headers = [
      $this->getAuthHeader(),
      'Content-Type: application/json',
    ];
    if (in_array($method, ['POST', 'PUT']) && $payload) {
      $postData = is_array($payload) ? json_encode($payload) : $payload;
      curl_setopt($session, CURLOPT_POSTFIELDS, $postData);
      $headers[] = 'Content-Length: ' . strlen($postData);
      if ($method === 'POST') {
        curl_setopt($session, CURLOPT_POST, TRUE);
      }
    }
    if (in_array($method, ['PUT', 'DELETE'])) {
      curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
    }
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($session);
    $headerSize = curl_getinfo($session, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    // Auto decode the JSON.
    if (!empty($body)) {
      $decoded = json_decode($body, TRUE);
      if ($decoded === NULL) {
        throw new \Exception('JSON response could not be decoded: ' . $response);
      }
      $body = $decoded;
    }
    $httpCode = curl_getinfo($session, CURLINFO_HTTP_CODE);
    $curlErrno = curl_errno($session);
    $message = curl_error($session);
    return [
      'errorMessage' => $message ? $message : 'curl ok',
      'curlErrno' => $curlErrno,
      'httpCode' => $httpCode,
      'response' => $body,
      'headers' => $header,
    ];
  }
}