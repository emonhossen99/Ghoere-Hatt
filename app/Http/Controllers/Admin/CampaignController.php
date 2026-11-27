<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CampaignReview;
use App\Models\Campaign;
use App\Traits\ImageUploadTrait;
use Toastr;
use Str;

class CampaignController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
        $show_data = Campaign::orderBy('id','DESC')->get();
        return view('backEnd.campaign.index',compact('show_data'));
    }
    public function create()
    {
        $products = Product::where(['status'=>1])->select('id','name','status')->get();
        return view('backEnd.campaign.create',compact('products'));
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'short_description' => 'required',
            'description' => 'required',
            'name' => 'required',
            'product_id' => 'required|integer',
            'review' => 'required',
        ]);
        
        $input = $request->except(['files', 'image', 'banner', 'banner_title', 'video']);
        $input['status'] = $request->status ? 1 : 0;
        foreach (['image_one', 'image_two', 'image_three'] as $field) {
            if ($request->hasFile($field)) {
                $input[$field] = $this->imageUpload($request->file($field), 'campaign', null, null);
            }
        }

        $input['slug'] = strtolower(Str::slug($request->name));
        $campaign = Campaign::create($input);

        $this->saveCampaignReviewImages($request->file('image'), $campaign->id);

        Toastr::success('Success','Data insert successfully');
        return redirect()->route('campaign.index');
    }
    
    public function edit($id)
    {
        $edit_data = Campaign::with('images')->find($id);
        $select_products = Product::where('campaign_id',$id)->get();
        $show_data = Campaign::orderBy('id','DESC')->get();
        $products = Product::where(['status'=>1])->select('id','name','status')->get();
        return view('backEnd.campaign.edit',compact('edit_data','products','select_products'));
    }
    
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'product_id' => 'required|integer',
            'review' => 'required',
        ]);
        $update_data = Campaign::findOrFail($request->hidden_id);
        $input = $request->except('hidden_id','product_ids','files','image','banner','banner_title','video');
        $input['status'] = $request->status ? 1 : 0;

        foreach (['image_one', 'image_two', 'image_three'] as $field) {
            if ($request->hasFile($field)) {
                $this->imageDelete($update_data->$field);
                $input[$field] = $this->imageUpload($request->file($field), 'campaign', null, null);
            } else {
                $input[$field] = $update_data->$field;
            }
        }

        $input['slug'] = strtolower(Str::slug($request->name));
        $update_data->update($input);

        $this->saveCampaignReviewImages($request->file('image'), $update_data->id);

        Toastr::success('Success','Data update successfully');
        return redirect()->route('campaign.index');
    }

    /**
     * Store campaign review images using ImageUploadTrait.
     */
    private function saveCampaignReviewImages($images, $campaignId)
    {
        if (!$images) {
            return;
        }

        foreach ($images as $image) {
            $imageUrl = $this->imageUpload($image, 'campaign', null, null);
            if ($imageUrl) {
                $pimage              = new CampaignReview();
                $pimage->campaign_id = $campaignId;
                $pimage->image       = $imageUrl;
                $pimage->save();
            }
        }
    }
 
    public function inactive(Request $request)
    {
        $inactive = Campaign::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Campaign::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Campaign::find($request->hidden_id);
        $this->imageDelete($delete_data->image_one);
        $this->imageDelete($delete_data->image_two);
        $this->imageDelete($delete_data->image_three);
        foreach ($delete_data->images as $reviewImage) {
            $this->imageDelete($reviewImage->image);
            $reviewImage->delete();
        }
        $delete_data->delete();
        
        $campaign = Product::whereNotNull('campaign_id')->get();
        foreach($campaign as $key=>$value){
            $product = Product::find($value->id);
            $product->campaign_id = null;
            $product->save();
        }
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    }
    public function imgdestroy(Request $request)
    { 
        $delete_data = CampaignReview::find($request->id);
        $this->imageDelete($delete_data->image);
        $delete_data->delete();
        Toastr::success('Success','Data delete successfully');
        return redirect()->back();
    } 
}
