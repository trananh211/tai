@extends('layouts.master')

<?php
$page_title = 'New Template WooCommerce';
$page_link = url('new-template/' . env('STORE_WOO_ID'));
$breadcrumb = [
    'page_title' => $page_title,
    'page_link' => $page_link
];
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
                            <h3 class="card-title">CREATE NEW TEMPLATE WOOCOMMERCE STORE : STEP 1</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <form action="{{url('check-woo-template')}}" method="post">
                                {{ csrf_field() }}
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn Stores</label>
                                                <select class="form-control" id="woo-store-select">
                                                    <option url="" consumer_key="" consumer_secret="">Chọn ở đây</option>
                                                    @foreach ($data['stores'] as $key => $store)
                                                        <option
                                                            url = {{ $store->url }}
                                                            consumer_key="{{ $store->consumer_key }}"
                                                            consumer_secret="{{ $store->consumer_secret }}"
                                                            value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4">
                                            <label for="url">Url</label>
                                            <input type="text" id="url" class="form-control" name="url">
                                        </div>
                                        <div class="col-4">
                                            <label for="consumer_key">Consumer Key</label>
                                            <input type="text" id="consumer_key" class="form-control"
                                                   name="consumer_key">
                                        </div>
                                        <div class="col-4">
                                            <label for="consumer_secret">Consumer_secret</label>
                                            <input type="text" id="consumer_secret" class="form-control"
                                                   name="consumer_secret">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for="id_product">Id Product</label>
                                            <input type="text" id="id_product" class="form-control" name="id_product"
                                                   placeholder="Nhập mã ID của product mẫu ở đây">
                                        </div>
                                        <div class="col-6">
                                            <label for="auto_sku">Auto Sku</label>
                                            <input type="text" id="auto_sku" class="form-control" name="auto_sku"
                                                   placeholder="Điền mã sku của sản phẩm">
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
    @include('script.admin.script_template')
@endsection

