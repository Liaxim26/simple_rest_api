<?php
require_once('php-login-registration-api-master/classes/JwtHandler.php');
require_once('UserDao.php');
class AuthChecker extends JwtHandler{

    protected $headers;
    protected $token;
    protected $userDao;
    public function __construct($headers) {
        parent::__construct();
        $this->headers = $headers;
        $this->userDao = new UserDao();
    }

    public function retrieveUserId(){
        if(array_key_exists('Authorization',$this->headers) && !empty(trim($this->headers['Authorization']))):
            $this->token = explode(" ", trim($this->headers['Authorization']));
            if(isset($this->token[1]) && !empty(trim($this->token[1]))):
                
                $data = $this->_jwt_decode_data($this->token[1]);

                if(isset($data['auth']) && isset($data['data']->user_id) && $data['auth']):
                    $user = $this->fetchUser($data['data']->user_id);
                    return $user->id;

                else:
                    return null;

                endif; // End of isset($this->token[1]) && !empty(trim($this->token[1]))
                
            else:
                return null;

            endif;// End of isset($this->token[1]) && !empty(trim($this->token[1]))

        else:
            return null;

        endif;
    }

    protected function fetchUser($userId) {

        try {
            $user = $this->userDao->findById($userId);
            return $user;
        }
        catch(PDOException $e){
            return null;
        }
    }
}