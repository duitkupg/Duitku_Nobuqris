<?php
/**
 * Copyright (c) 2017. All rights reserved Duitku Nobuqris.
 *
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software.
 * It is also not legal to do any changes to the software and distribute it in your own name / brand.
 *
 * All use of the payment modules happens at your own risk. We offer a free test account that you can use to test the module.
 *
 * @author    Duitku Nobuqris
 * @copyright Duitku Nobuqris (http://duitku.com)
 * @license   Duitku Nobuqris
 *
 */
namespace Duitku\Nobuqris\Controller\Epaynobuqris;

class Cancel extends \Duitku\Nobuqris\Controller\AbstractActionController
{
    /**
     * Decline Action
     */
    public function execute()
    {
    	
        $this->cancelOrder();
    }
}
