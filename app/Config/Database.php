<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'admin',
        'password'     => 'Arroz123@',
        'database'     => 'ospos',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => 'ospos_',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'    => [],
        'port'         => 3306,
    ];

    public array $tests = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'admin',
        'password'     => 'Arroz123@',
        'database'     => 'ospos',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => 'ospos_',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'    => [],
        'port'         => 3306,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->default['hostname'] = env('database.default.hostname', $this->default['hostname']);
        $this->default['username'] = env('database.default.username', $this->default['username']);
        $this->default['password'] = env('database.default.password', $this->default['password']);
        $this->default['database'] = env('database.default.database', $this->default['database']);
        $this->default['DBDriver'] = env('database.default.DBDriver', $this->default['DBDriver']);
        $this->default['DBPrefix'] = env('database.default.DBPrefix', $this->default['DBPrefix']);
        $this->default['port'] = env('database.default.port', $this->default['port']);

        $this->tests['hostname'] = env('database.tests.hostname', $this->tests['hostname']);
        $this->tests['username'] = env('database.tests.username', $this->tests['username']);
        $this->tests['password'] = env('database.tests.password', $this->tests['password']);
        $this->tests['database'] = env('database.tests.database', $this->tests['database']);
        $this->tests['DBDriver'] = env('database.tests.DBDriver', $this->tests['DBDriver']);
        $this->tests['DBPrefix'] = env('database.tests.DBPrefix', $this->tests['DBPrefix']);
        $this->tests['port'] = env('database.tests.port', $this->tests['port']);
    }
}
