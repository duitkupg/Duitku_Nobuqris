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
namespace Duitku\Nobuqris\Helper;

class DuitkuConstants
{
    
   
    //Config constants
    const ORDER_STATUS = 'order_status';
    const MASS_CAPTURE_INVOICE_MAIL = 'masscaptureinvoicemail';
    const TITLE = 'title';
    const MERCHANT_NUMBER = 'merchantnumber';
    const INSTANT_INVOICE = 'instantinvoice';
    const INSTANT_INVOICE_MAIL = 'instantinvoicemail';
    const SEND_MAIL_ORDER_CONFIRMATION = 'sendmailorderconfirmation';
    const WINDOW_STATE = 'windowstate';
    const PAYMENT_GROUP = 'paymentgroup';
	const ENABLE_INVOICE_DATA = 'enableinvoicedata';
    const ROUNDING_MODE = 'roundingmode';

    //Actions
    const CAPTURE = 'capture';
    const REFUND = 'refund';
    const VOID = 'void';
    const GET_TRANSACTION = 'gettransaction';
}
