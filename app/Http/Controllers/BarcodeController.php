<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('backend.barcode.index');
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('search');

        if ($query == '') {
            return response()->json([]);
        }

        $products = Product::with('productVariants')
            ->where('is_active', 1)
            ->where(function ($q) use ($query) {
                $q->where('product_title', 'LIKE', "%{$query}%")
                    ->orWhere('product_code', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $results = [];

        foreach ($products as $product) {
            if ($product->productVariants->count() > 0) {
                foreach ($product->productVariants as $variant) {
                    $results[] = [
                        'id' => $product->id,
                        'product_title' => $product->product_title.' - '.($variant->name ?? $variant->variant_name ?? ''),
                        'product_code' => $variant->variant_code ?? $product->product_code,
                        'price' => $variant->sellingPriceFromBase($product->selling_price),
                        'variant_id' => $variant->id,
                    ];
                }
            } else {
                $results[] = [
                    'id' => $product->id,
                    'product_title' => $product->product_title,
                    'product_code' => $product->product_code,
                    'price' => $product->selling_price,
                    'variant_id' => null,
                ];
            }
        }

        return response()->json($results);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'qty' => 'required|array',
            'product_code' => 'required|array',
            'product_name' => 'required|array',
            'price' => 'required|array',
        ]);

        $barcodeGenerator = new BarcodeGeneratorPNG;

        $barcodesToPrint = [];

        foreach ($request->product_id as $key => $productId) {
            $code = $request->product_code[$key];
            $qty = (int) $request->qty[$key];

            $barcodeBase64 = base64_encode($barcodeGenerator->getBarcode($code, $barcodeGenerator::TYPE_CODE_128));

            for ($i = 0; $i < $qty; $i++) {
                $barcodesToPrint[] = [
                    'product_name' => $request->product_name[$key],
                    'product_code' => $code,
                    'price' => $request->price[$key],
                    'barcode' => $barcodeBase64,
                ];
            }
        }

        $settings = [
            'show_company' => $request->has('show_company'),
            'show_product_name' => $request->has('show_product_name'),
            'show_price' => $request->has('show_price'),
            'show_product_code' => $request->has('show_product_code'),
            'paper_size' => $request->paper_size ?? '38x25',
            'company_name' => config('app.name', 'OMS'),
        ];

        return view('backend.barcode.print', compact('barcodesToPrint', 'settings'));
    }
}
