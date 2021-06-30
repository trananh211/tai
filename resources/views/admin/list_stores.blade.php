@extends('layouts.master')

<?php
$page_title = 'List Stores';
$page_link = url('list-stores');
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
                            <h3 class="card-title">Danh sách Stores đang được kết nối</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Url</th>
                                    <th>SKU</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(sizeof($stores) > 0)
                                    @foreach ($stores as $key => $item)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{!! getTypeStore($item->type) !!}</td>
                                            <td>{{ $item->url }}</td>
                                            <td>{{ $item->sku }}</td>
                                            <td>
                                                Edit | Delete
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr >
                                        <td colspan="5">Không có data . Tạo mới ở <a href="{{ url('view-scraper') }}">đây</a></td>
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

