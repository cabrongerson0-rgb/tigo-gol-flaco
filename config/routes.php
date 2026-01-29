<?php

declare(strict_types=1);

return [
    ['GET', '/', 'HomeController@index'],
    
    ['GET', '/payment', 'PaymentController@index'],
    ['POST', '/payment/validate', 'PaymentController@validate'],
    ['GET', '/payment/invoices', 'PaymentController@invoices'],
    ['GET', '/payment/methods', 'PaymentController@methods'],
    ['GET', '/payment/checkout', 'PaymentController@checkout'],
    ['POST', '/payment/process', 'PaymentController@process'],
    
    ['GET', '/pse', 'PseController@index'],
    ['GET', '/pse/form', 'PseController@form'],
    ['POST', '/pse/initiate', 'PseController@initiate'],
    ['GET', '/pse/callback', 'PseController@callback'],
    ['GET', '/pse/confirmation', 'PseController@confirmation'],
    
    ['GET', '/card/form', 'CardController@form'],
    ['GET', '/card/otp', 'CardController@otp'],
    ['POST', '/card/process', 'CardController@process'],
    
    ['POST', '/api/validate-document', 'ApiController@validateDocument'],
    ['POST', '/api/validate-captcha', 'ApiController@validateCaptcha'],
    ['GET', '/api/banks', 'ApiController@getBanks'],
    
    // API de Facturas Tigo
    ['GET', '/api/invoice/phone', 'InvoiceApiController@getByPhone'],
    ['GET', '/api/invoice/document', 'InvoiceApiController@getByDocument'],
    ['POST', '/api/invoice/search', 'InvoiceApiController@search'],
    
    ['GET', '/error', 'ErrorController@index'],
    ['GET', '/404', 'ErrorController@notFound'],
];
