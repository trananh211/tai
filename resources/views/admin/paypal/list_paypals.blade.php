@extends('layouts.master')

<?php
$page_title = 'List Paypals';
$page_link = url('list-paypals');
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
                            <h3 class="card-title">Danh sách Paypal</h3>
                            <div class="float-lg-right">
                                <a href="{{ url('add-new-paypal') }}" class="btn btn-primary">Thêm mới Account</a>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Email</th>
                                    <th>Store</th>
                                    <th>Profit</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(sizeof($lists) > 0)
                                    @foreach ($lists as $key => $item)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $item->api_email }}</td>
                                            <td>{{ $item->store_name }}</td>
                                            <td>{{ $item->profit_limit }} $</td>
                                            <td>{{ $item->status }}</td>
                                            <td>
                                                <a href="{{ url('edit-paypal-info'.'/'.$item->id) }}">
                                                        <span class="btn btn-info">Edit</span></a> |
                                                <span class="btn btn-danger">Delete</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr >
                                        <td colspan="6">Không có data . Cần được tạo mới.
                                            <a href="{{ url('add-new-paypal') }}">Thêm mới ở đây</a>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
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

