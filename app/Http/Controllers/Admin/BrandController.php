<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Traits\ImageUploadTrait;
use Toastr;
class BrandController extends Controller
{
    use ImageUploadTrait;
    
    public function index(Request $request)
    {
        $data = Brand::orderBy('id','DESC')->get();
        return view('backEnd.brand.index',compact('data'));
    }
    public function create()
    {
        return view('backEnd.brand.create');
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        // image with intervention
        $imageUrl = $this->imageUpload($request->file('image'), 'brand', 210, 210);
       

        $input = $request->all();
        $input['slug'] = strtolower(preg_replace('/\s+/u', '-', trim($request->name)));
        $input['image'] = $imageUrl;
        $input['status'] = $request->status ? 1 : 0;
        Brand::create($input);
        Toastr::success('Success','Data insert successfully');
        return redirect()->route('brands.index');
    }
    
    public function edit($id)
    {
        $edit_data = Brand::find($id);
        return view('backEnd.brand.edit',compact('edit_data'));
    }
    
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $update_data = Brand::find($request->id);
        $input = $request->all();
        $image = $request->file('image');
        if($image){
            $this->imageDelete($update_data->image);
            $input['image'] = $this->imageUpload($image, 'brand', 210, 210);
        }else{
            $input['image'] = $update_data->image;
        }
        $input['status'] = $request->status?1:0;
        $update_data->update($input);

        Toastr::success('Success','Data update successfully');
        return redirect()->route('brands.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Brand::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Brand::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Brand::find($request->hidden_id);
        $this->imageDelete($delete_data->image);
        $delete_data->delete();
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    }
}
