<?php

namespace App\Http\Controllers;

use App\DataTables\SmsLogDataTable;

class SmsLogController extends Controller
{
    public function index(SmsLogDataTable $dataTable)
    {
        return $dataTable->render('backend.smslog.index');
    }
}
