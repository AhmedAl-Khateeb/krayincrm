<?php 
namespace Webkul\Admin\Http\Controllers\Lead;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;

class ProductLookupController extends Controller
{
    public function byBusinessType(Request $request)
    {
        $type = $request->query('business_type', 'existing');

        // مثال: حط IDs حقيقية من عندك
        $existingIds = [1, 2, 3, 4, 5];
        $newIds      = [6, 7, 8, 9, 10];

        $ids = $type === 'new' ? $newIds : $existingIds;

        // الواجهة بتستنى items: [{id:..}, ...]
        return response()->json([
            'items' => array_map(fn($id) => ['id' => $id], $ids)
        ]);
    }
}
