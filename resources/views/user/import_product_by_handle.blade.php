@extends('layouts.master')

<?php
$page_title = 'Import Product By handle';
$page_link = url('import-product-web-scrap/'.$id);
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
                            <h3 class="card-title">Danh sách</h3>
                        </div>
                        <!-- /.card-header -->
                        <form action="{{url('import-data-product')}}" method="post">
                            {{ csrf_field() }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" placeholder="Name Store" value="{{ $info->template_name }}">
                                    </div>
                                    <div class="col-4">
                                        <label for="url">Store Name</label>
                                        <input type="text" class="form-control" value="{{ $info->store_name }}" placeholder="Store Name">
                                    </div>
                                    <div class="col-2">
                                        <label for="url">Sku</label>
                                        <input type="text" class="form-control" value="{{ $info->sku }}" placeholder="SKU">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <label for="consumer_key">Url</label>
                                        <input type="text" class="form-control" value="{{ $info->url }}">
                                    </div>
                                    <div class="col-3">
                                        <label for="template_id">Web Scrap ID</label>
                                        <input type="text" class="form-control" name="web_scrap_id" value="{{ $info->id }}">
                                    </div>
                                    <div class="col-3">
                                        <label for="type_platform">Type Platform</label>
                                        <input type="text" class="form-control" name="type_platform" value="{{ $info->type_platform }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <label for="Email">List Product</label>
                                        <div class="card-body">
                                            <textarea name="list_products" rows="16" cols="200" style="width:100%; font-size: 14px;">

                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>

                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
    <div>
        <pre>
            <?php
                print_r($info);
            ?>
        </pre>
    </div>
@endsection

@section('script')
    @include('script.user.main')
@endsection

