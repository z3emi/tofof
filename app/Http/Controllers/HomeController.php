<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\PrimaryCategory;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get all categories
        $categories = Category::all();

        // Get latest 8 products as featured (without using is_featured)
        $featuredProducts = Product::latest()->take(8)->get();

        // You can add more product collections based on your needs
        $newProducts = Product::latest()->take(14)->get();
        $saleProducts = Product::whereNotNull('sale_price')->take(14)->get();
        $bestSellingProducts = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(14)->get();

        // Send data to the view
        return view('frontend.homepage', compact(
            'categories', 
            'featuredProducts', 
            'newProducts', 
            'saleProducts', 
            'bestSellingProducts'
        ));
        $navCategories = Category::query()
    ->with(['children.children'])       // لحد مستويين (زدها إذا تحتاج)
    ->withCount('products as total_products_count')
    ->get(); // أو الجذور فقط حسب شجرتك
return view('...', compact('navCategories'));

    }
}