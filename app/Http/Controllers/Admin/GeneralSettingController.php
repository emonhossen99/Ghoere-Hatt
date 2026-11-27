<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Toastr;

class GeneralSettingController extends Controller
{
    use ImageUploadTrait;
    public function __construct()
    {
        $this->middleware('permission:setting-list|setting-create|setting-edit|setting-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:setting-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setting-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:setting-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = GeneralSetting::orderBy('id', 'DESC')->get();
        return view('backEnd.settings.index', compact('show_data'));
    }

    public function create()
    {
        return view('backEnd.settings.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'white_logo' => 'required',
            'dark_logo' => 'nullable',
            'favicon' => 'required',
            'status' => 'required',
        ]);

        $input = $request->all();

        $input['white_logo'] = $this->imageUpload($request->file('white_logo'), 'settings', null, null);
        $input['dark_logo']  = $this->imageUpload($request->file('dark_logo'), 'settings', null, null);
        $input['favicon']    = $this->imageUpload($request->file('favicon'), 'settings', null, null);

        GeneralSetting::create($input);
        Toastr::success('Success', 'Data insert successfully');

        return redirect()->route('settings.index');
    }

    public function edit($id)
    {
        $edit_data = GeneralSetting::findOrFail($id);
        return view('backEnd.settings.edit', compact('edit_data'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $update_data = GeneralSetting::findOrFail($request->id);
        $input = $request->all();

        // New white logo
        if ($request->hasFile('white_logo')) {
            $this->imageDelete($update_data->white_logo);
            $input['white_logo'] = $this->imageUpload($request->file('white_logo'), 'settings', null, null);
        } else {
            $input['white_logo'] = $update_data->white_logo;
        }

        // New dark logo
        if ($request->hasFile('dark_logo')) {
            $this->imageDelete($update_data->dark_logo);
            $input['dark_logo'] = $this->imageUpload($request->file('dark_logo'), 'settings', null, null);
        } else {
            $input['dark_logo'] = $update_data->dark_logo;
        }

        // New favicon image
        if ($request->hasFile('favicon')) {
            $this->imageDelete($update_data->favicon);
            $input['favicon'] = $this->imageUpload($request->file('favicon'), 'settings', null, null);
        } else {
            $input['favicon'] = $update_data->favicon;
        }

        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Data update successfully');

        return redirect()->route('settings.index');
    }

    public function inactive(Request $request)
    {
        $inactive = GeneralSetting::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Data inactive successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = GeneralSetting::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Data active successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $delete_data = GeneralSetting::findOrFail($request->hidden_id);

        $this->imageDelete($delete_data->white_logo);
        $this->imageDelete($delete_data->dark_logo);
        $this->imageDelete($delete_data->favicon);

        $delete_data->delete();

        Toastr::success('Success', 'Data delete successfully');
        return redirect()->back();
    }
}