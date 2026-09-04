<?php

namespace App\Enums;

enum ProductSyncStatus: string
{
    case Running = 'running';
    case Ok = 'ok';
    case Failed = 'failed';
}
