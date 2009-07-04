<?php

class IndexController extends Zend_Controller_Action
{
    protected $_username;
    protected $_password;
    
    public function init()
    {
        // A very simple example of authentication, hard coded login details
        $this->_username = 'admin';
        $this->_password = 'password';
    }
    
    public function indexAction()
    {
        // Rate limit the login form by IP address, with a maximum of 10 requests every 5 minutes
        $rateLimit = new Noginn_RateLimit(array('login', $_SERVER['REMOTE_ADDR']), 10, 5);
        
        $form = new Form_Login();
        if ($rateLimit->exceeded()) {
            // A CAPTCHA is added to the form is the rate limit is exceeded
            $form->addCaptcha();
        }
        
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            // Validate the login
            $values = $form->getValues();
            if ($values['username'] == $this->_username && $values['password'] == $this->_password) {
                // Correct login, continue
                $this->_helper->redirector('account');
            } else {
                // Increment request count for failed login attempts
                $rateLimit->increment();
            }
        }
        
        $form->setAction($this->_helper->url->url());
        $this->view->form = $form;
        $this->view->rateLimit = $rateLimit;
    }
    
    public function accountAction()
    {
        
    }
}
