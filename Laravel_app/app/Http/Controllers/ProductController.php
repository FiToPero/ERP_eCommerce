<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category',
            'images',
            'videos',
            'productDetail',
            'productGoogle',
            'productWebEcommerce',
            'productWebVenta',
            'productMercadolibre',
            'productMeta',
        ])->get();

        return response()->json($products);
    }
}
