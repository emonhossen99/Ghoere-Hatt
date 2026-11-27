@extends('backEnd.layouts.master')
@section('title','Announcement Setting')
@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Announcement Setting</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('announcement.update')}}" method="POST" class="row" data-parsley-validate="">
                        @csrf
                        <input type="hidden" name="id" value="{{$edit_data->id}}">
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="text" class="form-label">Announcement Text</label>
                                <textarea class="form-control @error('text') is-invalid @enderror" name="text" id="text" rows="3" placeholder="Enter header announcement text...">{{ old('text', $edit_data->text) }}</textarea>
                                @error('text')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="bg_color" class="form-label">Background Color</label>
                                <input type="color" class="form-control form-control-color @error('bg_color') is-invalid @enderror" name="bg_color" value="{{ old('bg_color', $edit_data->bg_color ?? '#fe5200') }}" id="bg_color">
                                @error('bg_color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="text_color" class="form-label">Text Color</label>
                                <input type="color" class="form-control form-control-color @error('text_color') is-invalid @enderror" name="text_color" value="{{ old('text_color', $edit_data->text_color ?? '#ffffff') }}" id="text_color">
                                @error('text_color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->
                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block">Status (ON / OFF)</label>
                                <label class="switch">
                                    <input type="checkbox" value="1" name="status" @if($edit_data->status == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->
                        <div>
                            <input type="submit" class="btn btn-success" value="Submit">
                        </div>
                    </form>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->
    </div>
</div>
@endsection
@section('script')
<script src="{{asset('public/backEnd/')}}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/js/pages/form-validation.init.js"></script>
@endsection
