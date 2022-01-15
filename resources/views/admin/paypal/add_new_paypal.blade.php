@extends('layouts.master')

<?php
$page_title = 'Add new a paypal';
$page_link = url('add-new-paypal');
$breadcrumb = [
    'page_title' => $page_title,
    'page_link' => $page_link
];

if ($paypal != false) {
    $action = url('edit-paypal');
} else {
    $action = url('post-new-paypal');
}
?>
@section('title',$page_title)

@section('content')
    @include('layouts.breadcrumb', $breadcrumb)
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Main row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info color-palette">
                            <h3 class="card-title">Thêm mới Paypal</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <form action="{{ $action }}" method="post">
                                {{ csrf_field() }}
                                <div class="card-body">
                                    @if ($paypal != false)
                                        <div class="row hidden">
                                            <div class="col-5">
                                                <label for="id">Id</label>
                                                <input value="{{ $paypal->id }}"
                                                       type="text" id="id" class="form-control" name="id">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-5">
                                            <label for="api_email">Email</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_email : '' }}"
                                                   type="email" id="api_email" class="form-control" name="api_email">
                                        </div>
                                        <div class="col-4">
                                            <label for="api_pass">Pass</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_pass : '' }}"
                                                   type="text" id="api_pass" class="form-control" name="api_pass">
                                        </div>
                                        <div class="col-3">
                                            <label for="api_merchant_id">Merchant Id</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_merchant_id : '' }}"
                                                   type="text" id="api_merchant_id" class="form-control" name="api_merchant_id">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">
                                            <label for="api_signature">Signature</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_signature : '' }}"
                                                   type="text" id="api_signature" class="form-control" name="api_signature">
                                        </div>
                                        <div class="col-4">
                                            <label for="api_client_id">Client Id</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_client_id : '' }}"
                                                   type="text" id="api_client_id" class="form-control" name="api_client_id">
                                        </div>
                                        <div class="col-4">
                                            <label for="api_secret">Secret</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->api_secret : '' }}"
                                                   type="text" id="api_secret" class="form-control"
                                                   name="api_secret">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <label for="profit_limit">Limit Profit</label>
                                            <input required value="{{ ($paypal != false) ? $paypal->profit_limit : '' }}"
                                                   type="text" id="profit_limit" class="form-control" name="profit_limit">
                                        </div>
                                        <div class="col-2">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn Stores</label>
                                                <select class="form-control" id="woo-store-select" name="store_info_id" required>
                                                    <option> Chọn ở đây </option>
                                                    @foreach ($stores as $key => $store)
                                                        <option
                                                            {{ ($paypal != false && $paypal->store_info_id == $store->id) ? 'selected' : ''}}
                                                            value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    @include('script.user.main')
@endsection

