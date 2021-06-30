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
    <?php
    if (Session::has('template_new_woo')) {
        $session = Session('template_new_woo');
    } else {
        $session = false;
    }
    ?>
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
                                        <div class="col-4">
                                            <!-- select -->
                                            <div class="form-group">
                                                <label>Chọn Stores</label>
                                                <select class="form-control" id="woo-store-select" name="store_info_id">
                                                    <option url="" consumer_key="" consumer_secret="" value="" store_name="">
                                                        Chọn ở đây
                                                    </option>
                                                    @foreach ($data['stores'] as $key => $store)
                                                        <option
                                                            {{ ($session && $store->id == $session['store_info_id']) ? 'selected' : ''}}
                                                            url = "{{ $store->url }}" store_name="{{ $store->name }}"
                                                            consumer_key="{{ $store->consumer_key }}"
                                                            consumer_secret="{{ $store->consumer_secret }}"
                                                            value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label for="name">Template Name</label>
                                            <input value="{{ ($session) ? $session['name'] : ''}}"
                                                type="text" id="name" class="form-control" name="name" required>
                                        </div>
                                    </div>

                                    <div class="row hidden">
                                        <div class="col-3">
                                            <label for="url">Url</label>
                                            <input value="{{ ($session) ? $session['url'] : ''}}"
                                                type="text" id="url" class="form-control" name="url">
                                        </div>
                                        <div class="col-3">
                                            <label for="store_name">Store Name</label>
                                            <input value="{{ ($session) ? $session['store_name'] : ''}}"
                                                type="text" id="store_name" class="form-control" name="store_name">
                                        </div>
                                        <div class="col-3">
                                            <label for="consumer_key">Consumer Key</label>
                                            <input value="{{ ($session) ? $session['consumer_key'] : ''}}"
                                                type="text" id="consumer_key" class="form-control"
                                                   name="consumer_key">
                                        </div>
                                        <div class="col-3">
                                            <label for="consumer_secret">Consumer_secret</label>
                                            <input value="{{ ($session) ? $session['consumer_secret'] : ''}}"
                                                type="text" id="consumer_secret" class="form-control"
                                                   name="consumer_secret">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4">
                                            <label for="id_product">Id Product (ID woocommerce product)</label>
                                            <input value="{{ ($session) ? $session['store_template_id'] : ''}}"
                                                type="text" id="id_product" class="form-control" name="store_template_id"
                                                   placeholder="Nhập mã ID của product mẫu ở đây" required>
                                        </div>
                                        <div class="col-2">
                                            <label for="sku">SKU (cố định)</label>
                                            <input value="{{ ($session) ? $session['sku'] : ''}}"
                                                type="text" id="sku" class="form-control" name="sku"
                                                   placeholder="SKU fixed">
                                        </div>
                                        <div class="col-6">
                                            <label for="sku_auto">Auto SKU</label>
                                            <small class="text-info">
                                                Nếu sản phẩm không có SKU cố định.
                                                Hãy chọn trường này để hệ thống gen tự động mã SKU </small>
                                            <input value="{{ ($session) ? $session['sku_auto'] : ''}}"
                                                type="text" id="sku_auto" class="form-control" name="sku_auto"
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

