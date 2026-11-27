<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Toastr;

class AnnouncementController extends Controller
{
    public function index()
    {
        $edit_data = Announcement::first();
        if (!$edit_data) {
            $edit_data = Announcement::create([
                'text' => 'Welcome to our store! Enjoy fast delivery & special discounts.',
                'bg_color' => '#fe5200',
                'text_color' => '#ffffff',
                'status' => 1,
            ]);
        }

        return view('backEnd.announcement.edit', compact('edit_data'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'text' => 'nullable|string',
            'bg_color' => 'nullable|string|max:30',
            'text_color' => 'nullable|string|max:30',
        ]);

        $update_data = Announcement::findOrFail($request->id);
        $update_data->update([
            'text' => $request->text,
            'bg_color' => $request->bg_color ?: '#fe5200',
            'text_color' => $request->text_color ?: '#ffffff',
            'status' => $request->status ? 1 : 0,
        ]);

        Toastr::success('Success', 'Announcement updated successfully');
        return redirect()->back();
    }
}
