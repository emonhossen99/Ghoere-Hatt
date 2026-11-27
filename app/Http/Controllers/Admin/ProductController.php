<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\Productprice;
use App\Models\Productcolor;
use App\Models\Productsize;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use App\Traits\ImageUploadTrait;
use Toastr;
use Str;
use DB;

class ProductController extends Controller
{
    use ImageUploadTrait;
    public function getSubcategory(Request $request)
    {
        $subcategory = DB::table("subcategories")
        ->where("category_id", $request->category_id)
        ->pluck('subcategoryName', 'id');
        return response()->json($subcategory);
    }
    public function getChildcategory(Request $request)
    {
        $childcategory = DB::table("childcategories")
        ->where("subcategory_id", $request->subcategory_id)
        ->pluck('childcategoryName', 'id');
        return response()->json($childcategory);
    }
    
    
    function __construct()
    {
         $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','show']]);
         $this->middleware('permission:product-create', ['only' => ['create','store']]);
         $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }
    
    
    public function index(Request $request)
    {
        if($request->keyword){
            $data = Product::orderBy('id','DESC')->where('name', 'LIKE', '%' . $request->keyword . "%")->with('image','category')->paginate(50);
        }else{
            $data = Product::orderBy('id','DESC')->with('image','category')->paginate(50);
        }
        return view('backEnd.product.index',compact('data'));
    }
    public function create()
    {
        $categories = Category::where('parent_id','=','0')->where('status',1)->select('id','name','status')->with('childrenCategories')->get();
        $brands = Brand::where('status','1')->select('id','name','status')->get();
        $colors = Color::where('status','1')->get();
        $sizes = Size::where('status','1')->get();
        return view('backEnd.product.create',compact('categories','brands','colors','sizes'));
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'category_id' => 'required|integer',
            'new_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required',
        ]);
        $last_id = Product::orderBy('id', 'desc')->select('id')->first();
        $last_id = $last_id?$last_id->id+1:1;
        $input = $request->except(['image','files','proSize','proColor']);

        $input['slug'] = strtolower(preg_replace('/[\/\s]+/', '-', $request->name.'-'.$last_id));
        $input['purchase_price'] = (int) $request->purchase_price;
        $input['old_price'] = $request->filled('old_price') ? (int) $request->old_price : null;
        $input['new_price'] = (int) $request->new_price;
        $input['stock'] = (int) $request->stock;
        $input['status'] = $request->status?1:0;
        $input['topsale'] = $request->topsale?1:0;
        $input['feature_product'] = $request->feature_product?1:0;
        $input['product_code'] = 'P' . str_pad($last_id, 4, '0', STR_PAD_LEFT);
        $save_data = Product::create($input);

        $sizes = array_filter((array) $request->proSize);
        if (!empty($sizes)) {
            $save_data->sizes()->attach($sizes);
        }

        $colors = array_filter((array) $request->proColor);
        if (!empty($colors)) {
            $save_data->colors()->attach($colors);
        }

        // image with intervention
        $this->saveProductImages($request->file('image'), $save_data->id);
        Toastr::success('Success','Data insert successfully');
        return redirect()->route('products.index');
    }
    
    public function edit($id)
    {
        $edit_data = Product::with('images')->findOrFail($id);
        $categories = Category::where('parent_id','=','0')->where('status',1)->select('id','name','status')->with('childrenCategories')->get();
        $categoryId = $edit_data->category_id;
        $subcategoryId = $edit_data->subcategory_id;
        $subcategory = Subcategory::where('category_id', '=', $categoryId)->select('id','subcategoryName','status')->get();
        $childcategory = Childcategory::where('subcategory_id', '=', $subcategoryId)->select('id', 'childcategoryName', 'status')->get();
        $brands = Brand::where('status','1')->select('id','name','status')->get();
        $totalsizes = Size::where('status',1)->get();
        $totalcolors = Color::where('status',1)->get();
        $selectcolors = Productcolor::where('product_id',$id)->get();
        $selectsizes = Productsize::where('product_id',$id)->get();
        return view('backEnd.product.edit',compact('edit_data','categories', 'subcategory', 'childcategory', 'brands', 'selectcolors', 'selectsizes','totalsizes', 'totalcolors'));
    }
    public function price_edit()
    {
        $products = DB::table('products')->select('id','name','status','old_price','new_price','stock')->where('status',1)->get();;
        return view('backEnd.product.price_edit',compact('products'));
    }
    public function price_update(Request $request)
{
    $ids = $request->ids;
    $oldPrices = $request->old_price;
    $newPrices = $request->new_price;
    $stocks = $request->stock;
    foreach ($ids as $key => $id) {
        $product = Product::select('id','name','status','old_price','new_price','stock')->find($id);

        if ($product) {
            $product->update([
                'old_price' => $oldPrices[$key],
                'new_price' => $newPrices[$key],
                'stock' => $stocks[$key],
            ]);
        }
    }
    Toastr::success('Success','Price update successfully');
    return redirect()->back();
}
    
    public function update(Request $request)
    {
       $this->validate($request, [
            'name' => 'required',
            'category_id' => 'required|integer',
            'new_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required',
        ]);
          
        $update_data = Product::findOrFail($request->id);
        $input = $request->except(['image','files','proSize','proColor']);
        $input['slug'] = strtolower(preg_replace('/[\/\s]+/', '-', $request->name.'-'.$update_data->id));
        $input['purchase_price'] = (int) $request->purchase_price;
        $input['old_price'] = $request->filled('old_price') ? (int) $request->old_price : null;
        $input['new_price'] = (int) $request->new_price;
        $input['stock'] = (int) $request->stock;
        $input['status'] = $request->status?1:0;
        $input['topsale'] = $request->topsale?1:0;
        $input['feature_product'] = $request->feature_product?1:0;
        $update_data->update($input);
        $update_data->sizes()->sync(array_filter((array) $request->proSize));
        $update_data->colors()->sync(array_filter((array) $request->proColor));

        // image with intervention
        $this->saveProductImages($request->file('image'), $update_data->id);

        Toastr::success('Success','Data update successfully');
        return redirect()->route('products.index');
    }

    /**
     * Store uploaded product images using ImageUploadTrait and record them in DB.
     */
    private function saveProductImages($images, $productId)
    {
        if (!$images) {
            return;
        }

        foreach ($images as $image) {
            $imageUrl = $this->imageUpload($image, 'product', null, null);
            if ($imageUrl) {
                $pimage             = new Productimage();
                $pimage->product_id = $productId;
                $pimage->image      = $imageUrl;
                $pimage->save();
            }
        }
    }
 
    public function inactive(Request $request)
    {
        $inactive = Product::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Product::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Product::find($request->hidden_id);
        foreach ($delete_data->images as $pimage) {
            $this->imageDelete($pimage->image);
            $pimage->delete();
        }
        $delete_data->delete();
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    }
    public function imgdestroy(Request $request)
    { 
        $delete_data = Productimage::find($request->id);
        $this->imageDelete($delete_data->image);
        $delete_data->delete();
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    } 
    public function pricedestroy(Request $request)
    { 
        $delete_data = Productprice::find($request->id);
        $delete_data->delete();
        Toastr::success('Success','Product price delete successfully');
        return redirect()->back();
    }
    public function update_deals(Request $request){
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['topsale' => $request->status]);
        return response()->json(['status'=>'success','message'=>'Hot deals product status change']);
    }
    public function update_feature(Request $request){
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['feature_product' => $request->status]);
        return response()->json(['status'=>'success','message'=>'Feature product status change']);
    }
    public function update_status(Request $request){
        $products = Product::whereIn('id', $request->input('product_ids'))->update(['status' => $request->status]);
        return response()->json(['status'=>'success','message'=>'Product status change successfully']);
    }
}
