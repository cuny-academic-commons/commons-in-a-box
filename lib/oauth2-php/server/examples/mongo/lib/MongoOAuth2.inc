<?php

// Set these values to your Mongo database
define("MONGO_CONNECTION", "mongodb://user:pass@mongoserver/mydb");
define("MONGO_DB", "mydb");

include "../../../lib/oauth.php";

/**
 * Sample OAuth2 Library Mongo DB Implementation.
 */
class MongoOAuth2 extends OAuth2 {

  private $db;

  /**
   * Overrides OAuth2::__construct().
   */
  public function __construct() {
    parent::__construct();

    $mongo = new Mongo(MONGO_CONNECTION);
    $this->db = $mongo->selectDB(MONGO_DB);
  }

  /**
   * Little helper function to add a new client to the database.
   *
   * Do NOT use this in production! This sample code stores the secret
   * in plaintext!
   *
   * @param $client_id
   *   Client identifier to be stored.
   * @param $client_secret
   *   Client secret to be stored.
   * @param $redirect_uri
   *   Redirect URI to be stored.
   */
  public function addClient($client_id, $client_secret, $redirect_uri) {
    $this->db->clients->insert(array(
      "_id" => $client_id,
      "pw" => $client_secret,
      "redirect_uri" => $redirect_uri
    ));
  }

  /**
   * Implements OAuth2::checkClientCredentials().
   *
   * Do NOT use this in production! This sample code stores the secret
   * in plaintext!
   */
  protected function checkClientCredentials($client_id, $client_secret = NULL) {
    $client = $this->db->clients->findOne(array("_id" => $client_id, "pw" => $client_secret));
    return $client !== NULL;
  }

  /**
   * Implements OAuth2::getRedirectUri().
   */
  protected function getRedirectUri($client_id) {
    $uri = $this->db->clients->findOne(array("_id" => $client_id), array("redirect_uri"));
    return $uri !== NULL ? $uri["redirect_uri"] : FALSE;
  }

  /**
   * Implements OAuth2::getAccessToken().
   */
  protected function getAccessToken($oauth_token) {
    return $this->db->tokens->findOne(array("_id" => $oauth_token));
  }

  /**
   * Implements OAuth2::setAccessToken().
   */
  protected function setAccessToken($oauth_token, $client_id, $expires, $scope = NULL) {
    $this->db->tokens->insert(array(
      "_id" => $oauth_token,
      "client_id" => $client_id,
      "expires" => $expires,
      "scope" => $scope
    ));
  }

  /**
   * Overrides OAuth2::getSupportedGrantTypes().
   */
  protected function getSupportedGrantTypes() {
    return array(
      OAUTH2_GRANT_TYPE_AUTH_CODE,
    );
  }

  /**
   * Overrides OAuth2::getAuthCode().
   */
  protected function getAuthCode($code) {
    $stored_code = $this->db->auth_codes->findOne(array("_id" => $code));
    return $stored_code !== NULL ? $stored_code : FALSE;
  }

  /**
   * Overrides OAuth2::setAuthCode().
   */
  protected function setAuthCode($code, $client_id, $redirect_uri, $expires, $scope = NULL) {
    $this->db->auth_codes->insert(array(
      "_id" => $code,
      "client_id" => $client_id,
      "redirect_uri" => $redirect_uri,
      "expires" => $expires,
      "scope" => $scope
    ));
  }
}
