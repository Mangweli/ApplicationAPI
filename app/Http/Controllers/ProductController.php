<?php

namespace App\Http\Controllers;

use App\Jobs\ProductCreated;
use App\Jobs\ProductDeleted;
use App\Jobs\ProductUpdated;
use Illuminate\Http\Request;
use \App\Models\Product;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\If_;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function show($id)
    {
        return Product::find($id);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['title' => 'required', 'image' => 'required']);

        if($validator->fails()) {
            return response($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $product = Product::create($request->only('title','image'));
        ProductCreated::dispatch($product->toArray())->onQueue('main_queue');

        return response($product, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if(empty($product)) {
            return response("Invalid Product", Response::HTTP_NOT_FOUND);
        }

        $product->update($request->only('title','image'));
        ProductUpdated::dispatch($product->toArray())->onQueue('main_queue');

        return response($product, Response::HTTP_ACCEPTED);
    }

    public function destroy($id)
    {
        $product = Product::destroy($id);
        ProductDeleted::dispatch($id)->onQueue('main_queue');

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
