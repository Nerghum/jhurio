<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\HomepageSection;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CliController extends Controller
{
    protected array $allowedModels = [
        'Product' => Product::class,
        'Category' => Category::class,
        'Tag' => Tag::class,
        'Order' => Order::class,
        'Review' => Review::class,
        'Banner' => Banner::class,
        'HomepageSection' => HomepageSection::class,
        'Setting' => Setting::class,
        'Coupon' => Coupon::class,
        'Media' => Media::class,
    ];

    public function execute(Request $request): JsonResponse
    {
        $action = $request->input('action', 'ping');

        return match ($action) {
            'ping' => $this->handlePing(),
            'stats' => $this->handleStats(),
            'model_query' => $this->handleModelQuery($request),
            'model_update' => $this->handleModelUpdate($request),
            'model_delete' => $this->handleModelDelete($request),
            'quick_hero' => $this->handleQuickHero($request),
            'quick_order' => $this->handleQuickOrder($request),
            'quick_stock' => $this->handleQuickStock($request),
            default => response()->json(['error' => "Unknown action: {$action}"], 400),
        };
    }

    protected function handlePing(): JsonResponse
    {
        return response()->json([
            'status' => 'pong',
            'server_time' => now()->toIso8601String(),
            'app_name' => config('app.name'),
            'env' => config('app.env'),
            'api_version' => '2.0',
        ]);
    }

    protected function handleStats(): JsonResponse
    {
        return response()->json([
            'products' => [
                'total' => Product::count(),
                'published' => Product::where('status', 'published')->count(),
                'low_stock' => Product::where('stock_quantity', '<=', 5)->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'revenue' => (float) Order::whereIn('status', ['delivered', 'completed'])->sum('total'),
            ],
            'categories' => Category::count(),
            'reviews' => [
                'total' => Review::count(),
                'approved' => Review::where('status', 'approved')->count(),
                'pending' => Review::where('status', 'pending')->count(),
            ],
            'banners' => Banner::count(),
            'homepage_sections' => HomepageSection::count(),
        ]);
    }

    protected function handleModelQuery(Request $request): JsonResponse
    {
        $modelName = $request->input('model');
        if (! isset($this->allowedModels[$modelName])) {
            return response()->json(['error' => "Model '{$modelName}' is not allowed or supported."], 422);
        }

        /** @var class-string<Model> $class */
        $class = $this->allowedModels[$modelName];
        $query = $class::query();

        if ($request->has('where') && is_array($request->input('where'))) {
            foreach ($request->input('where') as $col => $val) {
                $query->where($col, $val);
            }
        }

        if ($request->filled('order_by')) {
            $query->orderBy($request->input('order_by'), $request->input('order_dir', 'desc'));
        } else {
            $query->latest();
        }

        $take = min((int) $request->input('limit', 20), 100);
        $records = $query->take($take)->get();

        return response()->json([
            'model' => $modelName,
            'count' => $records->count(),
            'records' => $records,
        ]);
    }

    protected function handleModelUpdate(Request $request): JsonResponse
    {
        $modelName = $request->input('model');
        if (! isset($this->allowedModels[$modelName])) {
            return response()->json(['error' => "Model '{$modelName}' is not allowed."], 422);
        }

        $id = $request->input('id');
        if (! $id) {
            return response()->json(['error' => 'Record ID is required.'], 422);
        }

        /** @var class-string<Model> $class */
        $class = $this->allowedModels[$modelName];
        $record = $class::find($id);

        if (! $record) {
            return response()->json(['error' => "Record with ID {$id} not found in {$modelName}."], 404);
        }

        $data = $request->input('data', []);
        if (! is_array($data) || empty($data)) {
            return response()->json(['error' => 'Data must be a non-empty array of attributes.'], 422);
        }

        $record->update($data);

        return response()->json([
            'message' => "{$modelName} #{$id} updated successfully",
            'record' => $record->fresh(),
        ]);
    }

    protected function handleModelDelete(Request $request): JsonResponse
    {
        $modelName = $request->input('model');
        if (! isset($this->allowedModels[$modelName])) {
            return response()->json(['error' => "Model '{$modelName}' is not allowed."], 422);
        }

        $id = $request->input('id');
        if (! $id) {
            return response()->json(['error' => 'Record ID is required.'], 422);
        }

        /** @var class-string<Model> $class */
        $class = $this->allowedModels[$modelName];
        $record = $class::find($id);

        if (! $record) {
            return response()->json(['error' => "Record with ID {$id} not found."], 404);
        }

        $record->delete();

        return response()->json([
            'message' => "{$modelName} #{$id} deleted successfully",
        ]);
    }

    protected function handleQuickHero(Request $request): JsonResponse
    {
        $section = HomepageSection::firstOrCreate(
            ['type' => 'hero'],
            ['order' => 1, 'is_active' => true, 'data' => []]
        );

        $data = $section->data ?? [];
        $hero = $data['hero'] ?? [];

        if ($request->filled('slider_image')) {
            $hero['slider']['image'] = $request->input('slider_image');
        }
        if ($request->filled('slider_link')) {
            $hero['slider']['link'] = $request->input('slider_link');
        }
        if ($request->filled('right_top_image')) {
            $hero['right_top']['image'] = $request->input('right_top_image');
        }
        if ($request->filled('right_bottom_image')) {
            $hero['right_bottom']['image'] = $request->input('right_bottom_image');
        }

        $data['hero'] = $hero;
        $section->update(['data' => $data]);

        return response()->json([
            'message' => 'Hero banner updated successfully',
            'hero' => $hero,
        ]);
    }

    protected function handleQuickOrder(Request $request): JsonResponse
    {
        $orderId = $request->input('order_id');
        $status = $request->input('status');

        if (! $orderId || ! $status) {
            return response()->json(['error' => 'order_id and status are required'], 422);
        }

        $order = Order::find($orderId);
        if (! $order) {
            return response()->json(['error' => "Order #{$orderId} not found"], 404);
        }

        $order->update(['status' => $status]);

        return response()->json([
            'message' => "Order #{$orderId} status changed to {$status}",
            'order' => $order->fresh(),
        ]);
    }

    protected function handleQuickStock(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');
        $stock = $request->input('stock');

        if (! $productId || is_null($stock)) {
            return response()->json(['error' => 'product_id and stock are required'], 422);
        }

        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['error' => "Product #{$productId} not found"], 404);
        }

        $product->update(['stock_quantity' => (int) $stock]);

        return response()->json([
            'message' => "Product '{$product->name}' stock updated to {$stock}",
            'product_id' => $product->id,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }
}
