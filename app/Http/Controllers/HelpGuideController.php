<?php

namespace App\Http\Controllers;

class HelpGuideController extends Controller
{
    public function index()
    {
        $guide = config('help_guide');

        return view('backend.help.index', [
            'gettingStarted' => $guide['getting_started'],
            'modules' => $guide['modules'],
            'faqs' => $guide['faqs'],
            'tourCopy' => $guide['tour'],
        ]);
    }
}
