@extends('layouts.master')

<?php
$page_title = 'WooCommerce Connect';
$page_link = url('connect-woo');
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
                            <h3 class="card-title">FORM CONNECT WOOCOMMERCE STORE</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <form action="{{url('get-woo-info')}}" method="post">
                                {{ csrf_field() }}
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" placeholder="Name Store" name="name">
                                        </div>
                                        <div class="col-4">
                                            <label for="url">Url</label>
                                            <input type="text" class="form-control" name="url" placeholder="store url">
                                        </div>
                                        <div class="col-2">
                                            <label for="sku">Sku</label>
                                            <input type="text" class="form-control" name="sku" placeholder="SKU store">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for="consumer_key">Consumer Key</label>
                                            <input type="text" class="form-control" name="consumer_key">
                                        </div>
                                        <div class="col-6">
                                            <label for="Consumer_secret">Consumer_secret</label>
                                            <input type="text" class="form-control" name="consumer_secret">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for="Email">Email</label>
                                            <input name="email" type="email" class="form-control" placeholder="Email">
                                        </div>
                                        <div class="col-6">
                                            <label for="password">password</label>
                                            <input name="password" type="text" class="form-control" placeholder="password">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4">
                                            <label for="host">Host</label>
                                            <input type="text" class="form-control" placeholder="smtp.yandex.com" name="host">
                                        </div>
                                        <div class="col-4">
                                            <label for="port">Port</label>
                                            <input name="port" type="text" class="form-control" placeholder="465">
                                        </div>
                                        <div class="col-4">
                                            <label for="security">Security</label>
                                            <input name="security" type="text" class="form-control" placeholder="ssl">
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

