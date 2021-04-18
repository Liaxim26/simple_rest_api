<?php
require_once('php-login-registration-api-master/classes/JwtHandler.php');
require_once('UserDao.php');
class AuthChecker extends JwtHandler{
    protected $userDao;
    protected $headers;

    public function __construct($userDao, $headers) {
        parent::__construct();
        $this->userDao = $userDao;
        $this->headers = $headers;
    }

    public function authorize($requestedUserId) {
        $userId = $this->authentificateByTokenAndGet();
        if ($userId != $requestedUserId) {
            throw RequestProcessingException(402, "Access to the data is not allowed");
        }
    }

    public function authentificateByTokenAndGet() {
        if (!array_key_exists('Authorization', $this->headers)) {
            throw new RequestProcessingException(402, "Missing Authorization header");
        }

        $authHeader = trim($this->headers['Authorization']);

        if (empty($authHeader)) {
            throw new RequestProcessingException(406, "No authorization data provided");
        }

        $authHeaderParts = explode(" ", $authHeader);
        $authType = trim($authHeaderParts[0] ?? "");
        $encodedToken = trim($authHeaderParts[1] ?? "");

        if ($authType != 'Bearer') {
            throw new RequestProcessingException(408, "Unknown auth type");
        }
        
        $authData = (object) $this->_jwt_decode_data($encodedToken);

        if(!$authData->auth) {
            throw new RequestProcessingException(405, $authData->message);
        }

        $token = $authData->data;
        $userId = $token->user_id;

        return $userId;
    }

    public function authentificateByCredentials() {

    }
}