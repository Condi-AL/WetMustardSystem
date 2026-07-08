<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DBMTS Administrators
    |--------------------------------------------------------------------------
    |
    | Email addresses granted the DBMTS "administrator" role on sign-in. Every
    | other authenticated tenant user receives the default "operator" role.
    | Comma-separated in DBMTS_ADMIN_EMAILS.
    |
    */

    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('DBMTS_ADMIN_EMAILS', 'adrian.lacki@condimentum.co.uk,stuart.riches@condimentum.co.uk'))
    ))),

];
