<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category',
            'productDetail',
            'productGoogle',
            'productWeb',
            'productMercadolibre',
            'productMeta',
        ])->get();

        return response()->json($products);
    }
}
