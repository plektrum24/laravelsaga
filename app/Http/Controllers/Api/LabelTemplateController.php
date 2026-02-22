<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabelTemplate;
use App\Models\PrintJob;
use App\Models\Product;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelTemplateController extends Controller
{
    private PrintService $printService;

    public function __construct(PrintService $printService)
    {
        $this->printService = $printService;
    }

    /**
     * Get all templates
     * GET /api/label-templates
     */
    public function index(Request $request)
    {
        $query = LabelTemplate::where('tenant_id', auth()->user()->tenant_id);

        if ($request->has('type')) {
            $query->where('template_type', $request->type);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->active === 'true');
        }

        $templates = $query->with('creator')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Get template by ID
     * GET /api/label-templates/{id}
     */
    public function show($id)
    {
        $template = LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template
        ]);
    }

    /**
     * Create template
     * POST /api/label-templates
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template_type' => 'required|in:price_tag,shelf_label,barcode_label,custom',
            'width_mm' => 'required|numeric|min:10|max:200',
            'height_mm' => 'required|numeric|min:10|max:200',
            'layout_json' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults for this type
        if ($validated['is_default'] ?? false) {
            LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
                ->where('template_type', $validated['template_type'])
                ->update(['is_default' => false]);
        }

        $template = LabelTemplate::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            ...$validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Label template created successfully',
            'data' => $template
        ], 201);
    }

    /**
     * Update template
     * PUT /api/label-templates/{id}
     */
    public function update(Request $request, $id)
    {
        $template = LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template_type' => 'required|in:price_tag,shelf_label,barcode_label,custom',
            'width_mm' => 'required|numeric|min:10|max:200',
            'height_mm' => 'required|numeric|min:10|max:200',
            'layout_json' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults for this type
        if (($validated['is_default'] ?? false) && !$template->is_default) {
            LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
                ->where('template_type', $validated['template_type'])
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Label template updated successfully',
            'data' => $template
        ]);
    }

    /**
     * Delete template
     * DELETE /api/label-templates/{id}
     */
    public function destroy($id)
    {
        $template = LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Can't delete default template
        if ($template->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete default template. Set another template as default first.'
            ], 400);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Label template deleted successfully'
        ]);
    }

    /**
     * Create default templates
     * POST /api/label-templates/create-defaults
     */
    public function createDefaults()
    {
        LabelTemplate::createDefaults(
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Default templates created successfully'
        ]);
    }

    /**
     * Preview label
     * POST /api/label-templates/preview
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:label_templates,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $template = LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($validated['template_id']);

        $product = Product::find($validated['product_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'template' => $template,
                'product' => $product,
                'preview_url' => '/api/label-templates/' . $template->id . '/render?product_id=' . $product->id
            ]
        ]);
    }

    /**
     * Print labels
     * POST /api/label-templates/print
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:label_templates,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
            'printer_name' => 'nullable|string',
        ]);

        $result = $this->printService->batchPrint(
            $validated['product_ids'],
            $validated['template_id'],
            $validated['quantity'] ?? 1,
            $validated['printer_name'] ?? null
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Print job created successfully',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Print failed'
            ], 500);
        }
    }

    /**
     * Quick print barcode
     * POST /api/label-templates/quick-print-barcode
     */
    public function quickPrintBarcode(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $result = $this->printService->quickPrintBarcode(
            $validated['product_id'],
            $validated['quantity'] ?? 1
        );

        return response()->json($result);
    }

    /**
     * Quick print price tag
     * POST /api/label-templates/quick-print-price-tag
     */
    public function quickPrintPriceTag(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $result = $this->printService->quickPrintPriceTag(
            $validated['product_id'],
            $validated['quantity'] ?? 1
        );

        return response()->json($result);
    }

    /**
     * Get available printers
     * GET /api/label-templates/printers
     */
    public function getPrinters()
    {
        $printers = $this->printService->getAvailablePrinters();

        return response()->json([
            'success' => true,
            'data' => $printers
        ]);
    }

    /**
     * Test printer
     * POST /api/label-templates/test-printer
     */
    public function testPrinter(Request $request)
    {
        $validated = $request->validate([
            'printer_name' => 'required|string',
        ]);

        $result = $this->printService->testPrinter($validated['printer_name']);

        return response()->json($result);
    }

    /**
     * Render label as HTML
     * GET /api/label-templates/{id}/render
     */
    public function render($id, Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $template = LabelTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $product = Product::find($validated['product_id']);

        $label = $this->printService->generateLabel($template, $product);
        $html = $this->printService->generatePrintHTML([$label], $template);

        return response($html);
    }

    /**
     * Get print history
     * GET /api/label-templates/print-history
     */
    public function printHistory(Request $request)
    {
        $query = PrintJob::where('tenant_id', auth()->user()->tenant_id)
            ->with(['template', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $jobs = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }
}
