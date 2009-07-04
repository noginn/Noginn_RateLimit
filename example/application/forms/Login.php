<?php

class Form_Login extends Zend_Form
{
    public function init()
    {
        // Add the login field elements
        $this->addElement('text', 'username', array(
            'label' => 'Username',
            'required' => true,
            'order' => 1,
            'filters' => array(
                'StringTrim'
            )
        ));
        
        $this->addElement('password', 'password', array(
            'label' => 'Password',
            'required' => true,
            'order' => 2,
            'filters' => array(
                'StringTrim'
            )
        ));
        
        // Add the submit button element
        $this->addElement('submit', 'save', array(
            'label' => 'Login',
            'ignore' => true,
            'order' => 4,
        ));
    }
    
    public function addCaptcha()
    {
        $recaptcha = new Zend_Captcha_ReCaptcha(array(
            'privKey' => '6LcSMQcAAAAAAEfF3edgQpdSb6Chg0AvBp50Xf7A',
            'pubKey' => '6LcSMQcAAAAAAFPmViFuud4Kx0g0TQAh8tSpR6FG',
        ));
        
        $this->addElement('captcha', 'captcha', array(
            'label' => 'Human test',
            'required' => true, 
            'order' => 3,
            'captcha' => $recaptcha,
        ));
    }
}
