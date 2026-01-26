<?php

namespace Webkul\Admin\Http\Controllers\Products;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;
use Webkul\Admin\DataGrids\Product\ProductDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\AttributeForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\ProductUpdateForm;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\Lead\Repositories\TypeRepository;
// ✅ NEW
use Webkul\Product\Repositories\ProductRepository;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected TypeRepository $typeRepository
    ) {
        request()->request->add(['entity_type' => 'products']);
    }

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(ProductDataGrid::class)->process();
        }

        return view('admin::products.index');
    }

    public function create(): View
    {
        $leadTypes = $this->typeRepository->all(['id', 'name']);

        return view('admin::products.create', compact('leadTypes'));
    }

    public function store(AttributeForm $request)
    {
        // ✅ validate lead_type_ids (optional)
        $request->validate([
            'lead_type_ids' => ['nullable', 'array'],
            'lead_type_ids.*' => ['integer'],
        ]);

        Event::dispatch('product.create.before');

        $product = $this->productRepository->create($request->all());

        // ✅ sync lead types
        $typeIds = collect($request->input('lead_type_ids', []))
            ->map(fn ($x) => (int) $x)
            ->filter(fn ($x) => $x > 0)
            ->unique()
            ->values()
            ->all();

        if (method_exists($product, 'leadTypes')) {
            $product->leadTypes()->sync($typeIds);
        }

        Event::dispatch('product.create.after', $product);

        session()->flash('success', trans('admin::app.products.index.create-success'));

        return redirect()->route('admin.products.index');
    }

    public function view(int $id): View
    {
        $product = $this->productRepository->findOrFail($id);

        return view('admin::products.view', compact('product'));
    }

    public function edit(int $id): View|JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        // ✅ هات attribute ids ديناميك من code
        $attrIds = DB::table('attributes')
            ->where('entity_type', 'products')
            ->whereIn('code', ['name', 'sku', 'description', 'price', 'quantity', 'Plan_P'])
            ->pluck('id', 'code')
            ->toArray();

        // ✅ لو موجودين: اعمل upsert في attribute_values من جدول products
        if (!empty($attrIds)) {
            $upsert = function ($attributeId, array $values) use ($product) {
                DB::table('attribute_values')->updateOrInsert(
                    [
                        'entity_type' => 'products',
                        'entity_id' => $product->id,
                        'attribute_id' => $attributeId,
                    ],
                    $values + ['unique_id' => (string) Str::uuid()]
                );
            };

            if (!empty($attrIds['name'])) {
                $upsert($attrIds['name'], ['text_value' => $product->name]);
            }

            if (!empty($attrIds['sku'])) {
                $upsert($attrIds['sku'], ['text_value' => $product->sku]);
            }

            if (!empty($attrIds['description'])) {
                $upsert($attrIds['description'], ['text_value' => $product->description]);
            }

            if (!empty($attrIds['Plan_P'])) {
                DB::table('attribute_values')->updateOrInsert(
                    [
                        'entity_type' => 'products',
                        'entity_id' => $product->id,
                        'attribute_id' => $attrIds['Plan_P'],
                    ],
                    ['unique_id' => (string) Str::uuid()]
                );
            }
        }

        $product->load('attribute_values');

        // leadTypes
        if (method_exists($product, 'leadTypes')) {
            $product->load('leadTypes');
        }

        $leadTypes = $this->typeRepository->all(['id', 'name']);

        $inventories = $product->inventories()
            ->with('location')
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'name' => $inventory->location->name,
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_location_id' => $inventory->warehouse_location_id,
                    'in_stock' => $inventory->in_stock,
                    'allocated' => $inventory->allocated,
                ];
            });

        return view('admin::products.edit', compact('product', 'inventories', 'leadTypes'));
    }

    public function update(AttributeForm $request, int $id)
    {
        $request->validate([
            'lead_type_ids' => ['nullable', 'array'],
            'lead_type_ids.*' => ['integer'],
        ]);

        Event::dispatch('product.update.before', $id);

        $data = $request->except(['_token', '_method']);

        $data = collect($data)->reject(function ($value, $key) {
            if ($key === 'entity_type') {
                return false;
            }

            if (is_array($value)) {
                return empty(array_filter($value, fn ($v) => !($v === null || $v === '')));
            }

            return $value === null || $value === '';
        })->toArray();

        $product = $this->productRepository->update($data, $id);

        if ($request->has('lead_type_ids') && method_exists($product, 'leadTypes')) {
            $typeIds = collect($request->input('lead_type_ids', []))
                ->map(fn ($x) => (int) $x)
                ->filter(fn ($x) => $x > 0)
                ->unique()
                ->values()
                ->all();

            $product->leadTypes()->sync($typeIds);
        }

        Event::dispatch('product.update.after', $product);

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('admin::app.products.index.update-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.products.index.update-success'));

        return redirect()->route('admin.products.index');
    }





    public function storeInventories(int $id, ?int $warehouseId = null): JsonResponse
    {
        $this->validate(request(), [
            'inventories' => 'array',
            'inventories.*.warehouse_location_id' => 'required',
            'inventories.*.warehouse_id' => 'required',
            'inventories.*.in_stock' => 'required|integer|min:0',
            'inventories.*.allocated' => 'required|integer|min:0',
        ]);

        $product = $this->productRepository->findOrFail($id);

        Event::dispatch('product.update.before', $id);

        $this->productRepository->saveInventories(request()->all(), $id, $warehouseId);

        Event::dispatch('product.update.after', $product);

        return new JsonResponse([
            'message' => trans('admin::app.products.index.update-success'),
        ], 200);
    }

    public function search(): JsonResource
    {
        $allowedIds = request('allowed_ids');

        $query = $this->productRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->orderBy('created_at', 'desc');

        $ids = collect();

        if (is_array($allowedIds)) {
            $ids = collect($allowedIds);
        } elseif (is_string($allowedIds) && trim($allowedIds) !== '') {
            $ids = collect(explode(',', $allowedIds));
        }

        $ids = $ids
            ->map(fn ($x) => (int) trim((string) $x))
            ->filter(fn ($x) => $x > 0)
            ->unique()
            ->values();

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids->all());
        }

        $products = $query->take(10)->get();

        return ProductResource::collection($products);
    }

    public function warehouses(int $id): JsonResponse
    {
        $warehouses = $this->productRepository->getInventoriesGroupedByWarehouse($id);

        return response()->json(array_values($warehouses));
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        try {
            Event::dispatch('settings.products.delete.before', $id);

            $product->delete($id);

            Event::dispatch('settings.products.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.products.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.products.index.delete-failed'),
            ], 400);
        }
    }

    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('product.delete.before', $index);

            $this->productRepository->delete($index);

            Event::dispatch('product.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('admin::app.products.index.delete-success'),
        ]);
    }
}
