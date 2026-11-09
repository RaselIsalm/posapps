<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;

return [
    AppServiceProvider::class,
    TelescopeServiceProvider::class,
    DataTablesServiceProvider::class,
];
