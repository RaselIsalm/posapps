<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Helpers\InvoiceHelper;
use App\Helpers\ThemeHelper;
use App\Models\Account;
use App\Models\GeneralSettings;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'type', 'is_default']);

        return view('backend.settings.general_settings', compact('currencies', 'accounts'));
    }

    public function updateGeneralSettings(Request $request)
    {
        if ($request->input('default_payment_method') === '') {
            $request->merge(['default_payment_method' => null]);
        }

        $request->validate([
            'thermal_printer_size' => 'nullable|in:58mm,72mm,76mm,80mm,100mm,112mm',
            'default_payment_method' => 'nullable|integer|exists:accounts,id',
            'timezone' => 'required|timezone',
            'default_tax' => 'nullable|numeric|min:0',
        ]);

        $settings = $request->except('_token', '_method', 'remove_logo', 'remove_favicon', 'logo', 'favicon');

        if ($request->input('remove_logo') == '1' && ! $request->hasFile('logo')) {
            $oldSetting = GeneralSettings::where('key_name', 'logo')->first();
            if ($oldSetting && $oldSetting->value) {
                ImageHelper::deleteImage($oldSetting->value, 'settings');
            }
            GeneralSettings::setGeneralSetting('logo', null);
        } elseif ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            $oldSetting = GeneralSettings::where('key_name', 'logo')->first();
            $oldFile = $oldSetting ? $oldSetting->value : null;
            $name = 'logo_'.time().'.'.$file->getClientOriginalExtension();
            GeneralSettings::setGeneralSetting('logo', ImageHelper::updateImage($name, $file, 'settings', $oldFile));
        }

        if ($request->input('remove_favicon') == '1' && ! $request->hasFile('favicon')) {
            $oldSetting = GeneralSettings::where('key_name', 'favicon')->first();
            if ($oldSetting && $oldSetting->value) {
                ImageHelper::deleteImage($oldSetting->value, 'settings');
            }
            GeneralSettings::setGeneralSetting('favicon', null);
        } elseif ($request->hasFile('favicon') && $request->file('favicon')->isValid()) {
            $file = $request->file('favicon');
            $oldSetting = GeneralSettings::where('key_name', 'favicon')->first();
            $oldFile = $oldSetting ? $oldSetting->value : null;
            $name = 'favicon_'.time().'.'.$file->getClientOriginalExtension();
            GeneralSettings::setGeneralSetting('favicon', ImageHelper::updateImage($name, $file, 'settings', $oldFile));
        }

        foreach ($settings as $key => $value) {
            if ($request->hasFile($key) || is_object($value) || is_array($value)) {
                continue;
            }
            GeneralSettings::setGeneralSetting($key, $value);
        }

        $defaultAccountId = $request->input('default_payment_method');
        Account::setAsDefault($defaultAccountId ? (int) $defaultAccountId : null);

        return back()->with('success', 'Settings updated successfully!');
    }

    public function termsAndConditions()
    {
        $terms_record = GeneralSettings::where('category', 'general')->where('key_name', 'terms_and_conditions')->first();
        $terms = $terms_record ? $terms_record->value : [];
        return view('backend.settings.terms_conditions', compact('terms'));
    }

    public function updateTermsAndConditions(Request $request)
    {
        $terms = $request->input('terms', []);
        
        // Remove empty or null terms
        $terms = array_filter($terms, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        // Re-index array
        $terms = array_values($terms);
        
        GeneralSettings::setGeneralSetting('terms_and_conditions', $terms);
        
        return back()->with('success', 'Terms and Conditions updated successfully!');
    }

    public function colorSettings()
    {
        $colors = [
            'primary_color' => ThemeHelper::getPrimaryColor(),
            'primary_hover_color' => ThemeHelper::getHoverColor(),
            'link_color' => ThemeHelper::getLinkColor(),
            'toast_success_color' => ThemeHelper::getToastSuccessColor(),
            'primary_light_color' => ThemeHelper::getLightColor(),
        ];

        return view('backend.settings.color_settings', compact('colors'));
    }

    public function updateColorSettings(Request $request)
    {
        $hexRule = ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        $validated = $request->validate([
            'primary_color' => $hexRule,
            'primary_hover_color' => $hexRule,
            'link_color' => $hexRule,
            'toast_success_color' => $hexRule,
            'primary_light_color' => $hexRule,
        ], [
            'regex' => 'Each color must be a valid hex value (e.g. #308e87).',
        ]);

        foreach ($validated as $key => $value) {
            GeneralSettings::setGeneralSetting($key, strtolower($value));
        }

        return back()->with('success', 'Color settings updated successfully!');
    }

    public function resetColorSettings()
    {
        $defaults = [
            'primary_color' => ThemeHelper::DEFAULT_PRIMARY,
            'primary_hover_color' => ThemeHelper::DEFAULT_HOVER,
            'link_color' => ThemeHelper::DEFAULT_PRIMARY,
            'toast_success_color' => ThemeHelper::DEFAULT_PRIMARY,
            'primary_light_color' => ThemeHelper::DEFAULT_LIGHT,
        ];

        foreach ($defaults as $key => $value) {
            GeneralSettings::setGeneralSetting($key, $value);
        }

        return back()->with('success', 'Color settings reset to defaults!');
    }

    public function printSettings()
    {
        $sale = \App\Models\Sale::query()
            ->with(['productSales.product', 'payments', 'branch', 'customer', 'seller', 'user'])
            ->latest('id')
            ->first();

        $invoice = $sale
            ? InvoiceHelper::fromSale($sale)
            : InvoiceHelper::sampleInvoice();
        $invoice = InvoiceHelper::withDemoIdentity($invoice);

        $thermalBlocks = InvoiceHelper::printBlocks('thermal');
        $a4Blocks = InvoiceHelper::printBlocks('a4');
        $thermalLogoHeight = InvoiceHelper::getThermalLogoHeight();
        $thermalLogo = $invoice['company']['thermal_logo'] ?? (GeneralSettings::getGeneralSetting('thermal_logo'));
        $a4Logo = $invoice['company']['a4_logo'] ?? (GeneralSettings::getGeneralSetting('a4_logo'));
        $showBranchName = InvoiceHelper::getShowBranchName();
        $thermalIdentity = InvoiceHelper::getThermalIdentityFlags();

        return view('backend.settings.print_settings', compact(
            'invoice',
            'thermalBlocks',
            'a4Blocks',
            'thermalLogoHeight',
            'thermalLogo',
            'a4Logo',
            'showBranchName',
            'thermalIdentity'
        ));
    }

    public function updatePrintSettings(Request $request)
    {
        $request->validate([
            'print_layout_thermal' => 'required|array',
            'print_layout_a4' => 'required|array',
            'thermal_logo_height' => 'required|integer|min:24|max:80',
            'thermal_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'a4_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        GeneralSettings::setGeneralSetting(
            'print_layout_thermal',
            $this->sanitizePrintLayout('thermal', $request->input('print_layout_thermal', []))
        );
        GeneralSettings::setGeneralSetting(
            'print_layout_a4',
            $this->sanitizePrintLayout('a4', $request->input('print_layout_a4', []))
        );
        GeneralSettings::setGeneralSetting(
            'thermal_logo_height',
            (string) (int) $request->input('thermal_logo_height')
        );
        GeneralSettings::setGeneralSetting(
            'print_show_branch_name',
            $request->boolean('print_show_branch_name') ? '1' : '0'
        );
        foreach (array_keys(InvoiceHelper::thermalIdentityToggles()) as $field) {
            GeneralSettings::setGeneralSetting(
                'thermal_show_'.$field,
                $request->boolean('thermal_show_'.$field) ? '1' : '0'
            );
        }

        if ($request->input('remove_thermal_logo') === '1') {
            $oldLogo = GeneralSettings::getGeneralSetting('thermal_logo');
            if (is_string($oldLogo) && $oldLogo !== '') {
                ImageHelper::deleteImage($oldLogo, 'settings');
            }
            GeneralSettings::setGeneralSetting('thermal_logo', null);
        } elseif ($request->hasFile('thermal_logo') && $request->file('thermal_logo')->isValid()) {
            $file = $request->file('thermal_logo');
            $oldLogo = GeneralSettings::getGeneralSetting('thermal_logo');
            $oldLogo = is_string($oldLogo) ? $oldLogo : null;
            $name = 'thermal_logo_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::updateImage($name, $file, 'settings', $oldLogo);
            if (! Storage::disk('public')->exists('settings/'.$filename)) {
                return back()->withErrors(['thermal_logo' => 'Thermal logo upload failed. Check storage permissions and run php artisan storage:link.'])->withInput();
            }
            GeneralSettings::setGeneralSetting('thermal_logo', $filename);
        }

        if ($request->input('remove_a4_logo') === '1') {
            $oldLogo = GeneralSettings::getGeneralSetting('a4_logo');
            if (is_string($oldLogo) && $oldLogo !== '') {
                ImageHelper::deleteImage($oldLogo, 'settings');
            }
            GeneralSettings::setGeneralSetting('a4_logo', null);
        } elseif ($request->hasFile('a4_logo') && $request->file('a4_logo')->isValid()) {
            $file = $request->file('a4_logo');
            $oldLogo = GeneralSettings::getGeneralSetting('a4_logo');
            $oldLogo = is_string($oldLogo) ? $oldLogo : null;
            $name = 'a4_logo_'.time().'.'.$file->getClientOriginalExtension();
            $filename = ImageHelper::updateImage($name, $file, 'settings', $oldLogo);
            if (! Storage::disk('public')->exists('settings/'.$filename)) {
                return back()->withErrors(['a4_logo' => 'A4 logo upload failed. Check storage permissions and run php artisan storage:link.'])->withInput();
            }
            GeneralSettings::setGeneralSetting('a4_logo', $filename);
        }

        return back()->with('success', 'Print layout updated. Invoices and POS receipts will use this order.');
    }

    protected function sanitizePrintLayout(string $format, array $items): array
    {
        $allowed = array_keys(config('print_layout.'.$format, []));
        $clean = [];
        $seen = [];

        foreach ($items as $item) {
            $id = $item['id'] ?? null;
            if (! $id || ! in_array($id, $allowed, true) || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $clean[] = [
                'id' => $id,
                'enabled' => filter_var($item['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        foreach ($allowed as $id) {
            if (! isset($seen[$id])) {
                $clean[] = ['id' => $id, 'enabled' => true];
            }
        }

        return $clean;
    }
}
