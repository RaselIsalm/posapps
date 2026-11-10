<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportService
{
    public const MAX_ROWS = 1000;

    public const MAX_FILE_BYTES = 5 * 1024 * 1024;

    /** Exact header order required in sample / upload files. */
    public const HEADERS = [
        'product_title',
        'product_code',
        'product_type',
        'product_cost',
        'selling_price',
        'category_name',
        'brand_name',
        'unit_name',
        'wholesale_price',
        'quantity',
        'tax',
        'tax_method',
        'description',
        'is_active',
    ];

    public const REQUIRED_HEADERS = [
        'product_title',
        'product_code',
        'product_type',
        'product_cost',
        'selling_price',
        'category_name',
        'brand_name',
        'unit_name',
    ];

    /** @var array<string, int> */
    protected array $productTypeMap = [
        '1' => 1,
        'physical' => 1,
        'standard' => 1,
        '2' => 2,
        'digital' => 2,
        '4' => 4,
        'service' => 4,
    ];

    public function downloadSample(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        foreach (self::HEADERS as $index => $header) {
            $col = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col.'1', $header);
        }

        // Example row (user should replace)
        $example = [
            'Sample Product',
            'SKU-001',
            'physical',
            '100',
            '150',
            'Electronics',
            'Generic',
            'Piece',
            '140',
            '10',
            '0',
            'Exclusive',
            'Sample description',
            '1',
        ];
        foreach ($example as $index => $value) {
            $col = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($col.'2', $value);
        }

        $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex(count(self::HEADERS)).'1')->getFont()->setBold(true);
        foreach (range(1, count(self::HEADERS)) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'product_import_sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{imported:int, failed:int, errors:array<int, array{row:int, product_code:string, message:string}>}
     */
    public function import(UploadedFile $file, int $branchId): array
    {
        if ($file->getSize() > self::MAX_FILE_BYTES) {
            throw new RuntimeException('File too large. Maximum allowed size is 5MB.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            throw new RuntimeException('Invalid file type. Please upload an .xlsx Excel file.');
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            throw new RuntimeException('The Excel file is empty. Add a header row and at least one data row.');
        }

        $headerRow = array_map(function ($value) {
            return strtolower(trim((string) $value));
        }, $rows[0] ?? []);

        // Trim trailing empty header cells
        while (count($headerRow) > 0 && end($headerRow) === '') {
            array_pop($headerRow);
        }

        if ($headerRow !== self::HEADERS) {
            throw new RuntimeException(
                'Excel headers do not match the sample file. Download the sample and use the exact column names/order. Expected: '
                .implode(', ', self::HEADERS)
            );
        }

        $dataRows = array_slice($rows, 1);
        if (count($dataRows) > self::MAX_ROWS) {
            throw new RuntimeException('Too many rows. Maximum '.self::MAX_ROWS.' data rows allowed per import.');
        }

        $imported = 0;
        $errors = [];
        $seenCodes = [];

        $categories = Category::query()->get()->keyBy(fn ($c) => strtolower(trim($c->name)));
        $brands = Brand::query()->get()->keyBy(fn ($b) => strtolower(trim($b->name)));
        $units = Unit::query()->get()->keyBy(fn ($u) => strtolower(trim($u->name)));

        foreach ($dataRows as $offset => $rawRow) {
            $excelRow = $offset + 2; // 1-based with header

            // Skip completely empty rows
            $allEmpty = true;
            foreach ($rawRow as $cell) {
                if (trim((string) $cell) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            $row = [];
            foreach (self::HEADERS as $i => $key) {
                $row[$key] = isset($rawRow[$i]) ? trim((string) $rawRow[$i]) : '';
            }

            $code = $row['product_code'];
            try {
                $this->validateRow($row, $seenCodes, $categories, $brands, $units);

                DB::transaction(function () use ($row, $branchId, $categories, $brands, $units) {
                    $this->createProductFromRow($row, $branchId, $categories, $brands, $units);
                });

                $seenCodes[strtolower($code)] = true;
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'row' => $excelRow,
                    'product_code' => $code !== '' ? $code : '-',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed' => count($errors),
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<string, bool>  $seenCodes
     * @param  \Illuminate\Support\Collection  $categories
     * @param  \Illuminate\Support\Collection  $brands
     * @param  \Illuminate\Support\Collection  $units
     */
    protected function validateRow(array $row, array $seenCodes, $categories, $brands, $units): void
    {
        foreach (self::REQUIRED_HEADERS as $field) {
            if ($row[$field] === '') {
                throw new RuntimeException("Missing required field: {$field}");
            }
        }

        $typeKey = strtolower($row['product_type']);
        if (! isset($this->productTypeMap[$typeKey])) {
            throw new RuntimeException('Invalid product_type. Use physical, digital, service (or 1, 2, 4). Combo is not supported in bulk import.');
        }

        if (! is_numeric($row['product_cost']) || (float) $row['product_cost'] < 0) {
            throw new RuntimeException('product_cost must be a valid non-negative number.');
        }
        if (! is_numeric($row['selling_price']) || (float) $row['selling_price'] < 0) {
            throw new RuntimeException('selling_price must be a valid non-negative number.');
        }

        if ($row['wholesale_price'] !== '' && (! is_numeric($row['wholesale_price']) || (float) $row['wholesale_price'] < 0)) {
            throw new RuntimeException('wholesale_price must be a valid non-negative number.');
        }
        if ($row['quantity'] !== '' && (! is_numeric($row['quantity']) || (float) $row['quantity'] < 0)) {
            throw new RuntimeException('quantity must be a valid non-negative number.');
        }
        if ($row['tax'] !== '' && (! is_numeric($row['tax']) || (float) $row['tax'] < 0)) {
            throw new RuntimeException('tax must be a valid non-negative number.');
        }

        if ($row['tax_method'] !== '') {
            $tm = strtolower($row['tax_method']);
            if (! in_array($tm, ['exclusive', 'inclusive', 'no tax', 'no_tax'], true)) {
                throw new RuntimeException('tax_method must be Exclusive, Inclusive, or No Tax.');
            }
        }

        $codeKey = strtolower($row['product_code']);
        if (isset($seenCodes[$codeKey])) {
            throw new RuntimeException('Duplicate product_code within the Excel file.');
        }
        if (Product::where('product_code', $row['product_code'])->exists()) {
            throw new RuntimeException('product_code already exists in the system.');
        }

        $catKey = strtolower($row['category_name']);
        if (! $categories->has($catKey)) {
            throw new RuntimeException("category_name \"{$row['category_name']}\" not found. Create the category first.");
        }
        $brandKey = strtolower($row['brand_name']);
        if (! $brands->has($brandKey)) {
            throw new RuntimeException("brand_name \"{$row['brand_name']}\" not found. Create the brand first.");
        }
        $unitKey = strtolower($row['unit_name']);
        if (! $units->has($unitKey)) {
            throw new RuntimeException("unit_name \"{$row['unit_name']}\" not found. Create the unit first.");
        }

        if ($row['is_active'] !== '' && ! in_array(strtolower($row['is_active']), ['0', '1', 'yes', 'no', 'true', 'false', 'active', 'inactive'], true)) {
            throw new RuntimeException('is_active must be 1/0, yes/no, or active/inactive.');
        }
    }

    /**
     * @param  array<string, string>  $row
     * @param  \Illuminate\Support\Collection  $categories
     * @param  \Illuminate\Support\Collection  $brands
     * @param  \Illuminate\Support\Collection  $units
     */
    protected function createProductFromRow(array $row, int $branchId, $categories, $brands, $units): Product
    {
        $type = $this->productTypeMap[strtolower($row['product_type'])];
        $category = $categories->get(strtolower($row['category_name']));
        $brand = $brands->get(strtolower($row['brand_name']));
        $unit = $units->get(strtolower($row['unit_name']));

        // Persist the database enum values, not the display labels accepted in Excel.
        $taxMethod = 'exclusive';
        if ($row['tax_method'] !== '') {
            $tm = strtolower($row['tax_method']);
            $taxMethod = match ($tm) {
                'inclusive' => 'inclusive',
                'no tax', 'no_tax' => 'no_tax',
                default => 'exclusive',
            };
        }

        $isActive = true;
        if ($row['is_active'] !== '') {
            $isActive = in_array(strtolower($row['is_active']), ['1', 'yes', 'true', 'active'], true);
        }

        $qty = $row['quantity'] !== '' ? (float) $row['quantity'] : 0;

        $product = Product::create([
            'product_title' => $row['product_title'],
            'product_code' => $row['product_code'],
            'slug' => Str::slug($row['product_title'].'-'.uniqid()),
            'has_imei' => 0,
            'product_type' => $type,
            'product_cost' => (float) $row['product_cost'],
            'selling_price' => (float) $row['selling_price'],
            'wholesale_price' => $row['wholesale_price'] !== '' ? (float) $row['wholesale_price'] : null,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'tax_method' => $taxMethod,
            'tax' => $row['tax'] !== '' ? (float) $row['tax'] : null,
            'product_unit' => $unit->id,
            'purchase_unit' => $unit->id,
            'selling_unit' => $unit->id,
            'quantity' => 0,
            'description' => $row['description'] !== '' ? $row['description'] : null,
            'is_variation' => 0,
            'is_active' => $isActive ? 1 : 0,
            'created_by' => Auth::id(),
        ]);

        if ($qty > 0) {
            app(InventoryService::class)->incrementStock(
                $branchId,
                $product->id,
                null,
                $qty,
                'import',
                'BULK-IMPORT-'.now()->format('YmdHis').'-'.$product->id
            );
        }

        return $product;
    }
}
