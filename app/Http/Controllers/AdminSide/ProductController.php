<?php

namespace App\Http\Controllers\AdminSide;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $product=Product::leftjoin('categories','categories.id','products.category')
      ->leftjoin('sub_categories','sub_categories.id','products.sub_category')
      ->select('products.*','categories.name as category_name','sub_categories.name as sub_category_name')
      ->orderBy('id','desc')
      ->get();
      $url=config('global.productImageGet');
    return response(['message' => 'Product Get successfully!', 'product' => $product,'url'=>$url], 200);


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {  
        $validator = Validator::make($request->all(), [
        'category' => 'required|integer',
        'sub_category' => 'required|integer',
        'uploadedImages' => 'required|array|min:1',
        'uploadedImages.*' => 'required|string|starts_with:data:image/',
        'size' => 'required|array|min:1',
        'size.*' => 'required|string',
        'product_id' => 'required|string|unique:products,product_id',
        // 'tableData' => 'required|array|min:1',
        // 'tableData.*.size' => 'required|string',
        // 'tableData.*.listing_price' => 'required|numeric',
        // 'tableData.*.wrongDefectiveReturnsPrice' => 'required|numeric',
        // 'tableData.*.mrp' => 'required|numeric',
        'netWeight' => 'required|numeric',
        'name' => 'required|string',
        'netQuantity' => 'required|integer',
        'manufacturerDetails' => 'required|string',
        'packerDetails' => 'required|string',
        'description' => 'required|string',
        'importerDetails' => 'required|string',
        'warrantyPeriod' => 'required|string',
        'warrantyType' => 'required|string',
        'brand' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
    }

    $product = new Product();
    $product->category = $request->category;
    $product->sub_category = $request->sub_category;
    $product->uploadedImages = json_encode($request->uploadedImages); // Store as JSON
    $product->size = json_encode($request->size); // Store as JSON
    $product->product_id = $request->product_id;
    $product->price_collection = json_encode($request->tableData); // Store as JSON
    $product->netWeight = $request->netWeight;
    $product->name = $request->name;
    $product->netQuantity = $request->netQuantity;
    $product->manufacturerDetails = $request->manufacturerDetails;
    $product->packerDetails = $request->packerDetails;
    $product->description = $request->description;
    $product->importerDetails = $request->importerDetails;
    $product->warrantyPeriod = $request->warrantyPeriod;
    $product->warrantyType = $request->warrantyType;
    $product->brand = $request->brand;

    // Step 3: Handle Image Uploads
    $uploadedImages = [];
    foreach ($request->uploadedImages as $image) {
        $filename = $this->imageUpload($product->product_id, $image); // Call image upload function
        $uploadedImages[] = $filename;
    }

    // Update the uploadedImages with stored file paths
    $product->uploadedImages = json_encode($uploadedImages);
    $product->save();

    return response(['message' => 'Product created successfully!', 'status' => 'success'], 200);
}

public function imageUpload($id, $avatar)
{
    $originalImgStorage = config('global.productUpload') . $id . '/productimages/original';
    $thumbnailImgStorage = config('global.productUpload') . $id . '/productimages/thumbnailimg';

    if (!File::exists($originalImgStorage)) {
        File::makeDirectory($originalImgStorage, 0755, true);
    }

    if (!File::exists($thumbnailImgStorage)) {
        File::makeDirectory($thumbnailImgStorage, 0755, true);
    }

    $filename = Str::random(20) . '_' . $id . '.jpg';
    $originalimgpath = $originalImgStorage . '/' . $filename;
    $thumbnailimgpath = $thumbnailImgStorage . '/' . $filename;

    Image::make(file_get_contents($avatar))->save($originalimgpath);
    Image::make(file_get_contents($avatar))->fit(75, 75)->save($thumbnailimgpath);

    return $filename;
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
