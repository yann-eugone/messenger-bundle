<?php

namespace MessengerBundle\Tests\Fixtures\Recipient;

use MessengerBundle\Recipient\SwiftmailerRecipientInterface;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class SwiftmailerRecipient implements SwiftmailerRecipientInterface
{
    private $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}
