<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    public function index(CategoryService $categoryService)
    {
        return response()->json($categoryService->getCategoryTree());
    }
}
