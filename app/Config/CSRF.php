<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CSRF extends BaseConfig
{
    public string $tokenName  = 'csrf_token_name';
    public string $headerName = 'X-CSRF-TOKEN';
    public bool   $regenerate = true;
    public bool   $redirect   = false;
    public int    $samesite   = 0;
    public array  $excludes   = [
        'api/*',
    ];
}
