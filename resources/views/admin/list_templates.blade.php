@extends('layouts.master')

<?php
$page_title = 'List Templates';
$page_link = url('list-template');
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
                            <h3 class="card-title">Danh sách Templates</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Type</th>
                                    <th>Store</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(sizeof($templates) > 0)
                                    @foreach ($templates as $key => $item)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ ($item->sku_auto == 1) ? $item->sku.'++' : $item->sku }}</td>
                                            <td>{!! getTypeStore($item->type_platform) !!}</td>
                                            <td>{{ $item->store_name}}</td>
                                            <td>{!! getStatus($item->status) !!}</td>
                                            <td>
                                                Edit |
                                                <a href="{{ url('delete-template/'.$item->id) }}"
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa Template này?');"
                                                >
                                                    <button type="button" class="btn btn-xs btn-block btn-danger">Delete</button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr >
                                        <td colspan="8">Không có data . Cần được Tạo mới</td>
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

