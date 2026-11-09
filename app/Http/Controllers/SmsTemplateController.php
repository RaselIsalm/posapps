<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::latest()->paginate(10);

        return view('backend.sms_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backend.sms_templates.ajax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
            'message_body' => 'required|string',
        ]);

        $formattedName = strtolower(str_replace(' ', '_', $request->template_name));
        $isActive = $request->has('is_active') ? 1 : 0;

        if ($isActive == 1) {
            SmsTemplate::where('is_active', '1')->update(['is_active' => 0]);
        }

        SmsTemplate::create([
            'template_name' => $formattedName,
            'message_body' => $request->message_body,
            'is_active' => $isActive,
        ]);

        return redirect()->route('sms_templates.index')->with([
            'success' => 'SMS Template created successfully',
        ]);
    }

    public function edit($id)
    {
        $template = SmsTemplate::findOrFail($id);

        return view('backend.sms_templates.ajax.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
            'message_body' => 'required|string',
        ]);

        $template = SmsTemplate::findOrFail($id);

        $formattedName = strtolower(str_replace(' ', '_', $request->template_name));
        $isActive = $request->has('is_active') ? 1 : 0;

        if ($isActive == 1) {
            SmsTemplate::where('id', '!=', $id)->where('is_active', '1')->update(['is_active' => 0]);
        }

        $template->update([
            'template_name' => $formattedName,
            'message_body' => $request->message_body,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('sms_templates.index')->with('success', 'SMS Template updated successfully!');
    }

    public function destroy($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('sms_templates.index')->with('success', 'SMS Template deleted successfully!');
    }
}
