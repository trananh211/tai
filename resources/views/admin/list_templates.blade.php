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
                                    <th>Template Name</th>
                                    <th>Product Name</th>
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
                                            <td>{!! getTypeStore($item->type_platform) !!}</td>
                                            <td>{{ $item->store_name}}</td>
                                            <td>{!! getTStatus($item->t_status) !!}</td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-block btn-warning"
                                                        data-toggle="modal" data-target="#modal-xl-{{ $item->id }}">Edit</button>

                                                <a href="{{ url('delete-template/'.$item->id) }}"
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa Template này?');"
                                                >
                                                    <button type="button" class="btn btn-xs btn-block btn-danger">Delete</button>
                                                </a>

                                                {{-- Model--}}
                                                <div class="modal fade" id="modal-xl-{{ $item->id }}">
                                                    <div class="modal-dialog modal-xl">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">{{ $item->name }} - {{ $item->store_name }}</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <form action="{{url('edit-templates-info')}}" method="post">
                                                                    {{ csrf_field() }}
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <input type="text" class="form-control hidden" name="id" value="{{ $item->id }}" >
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <label for="name">Template Name Change</label>
                                                                                <input value="{{ $item->name }}"
                                                                                       type="text" id="name" class="form-control" name="name"
                                                                                       placeholder="Nhập ký tự product mới ở đây" >
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label for="sale_price">Giá khuyến mại (USD)</label>
                                                                                    <input value="{{ ($item->sale_price) }}"
                                                                                           type="text" id="sale_price" class="form-control" name="sale_price"
                                                                                           placeholder="Điền giá khuyến mại của sản phẩm">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label for="sale_price_auto">Giá gốc (USD)</label>
                                                                                    <input value="{{ $item->origin_price }}"
                                                                                           type="text" id="origin_price" class="form-control" name="origin_price"
                                                                                           placeholder="Điền giá gốc của sản phẩm">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- /.card-body -->
                                                                    <button type="submit" class="btn btn-primary right">Save changes</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
                                                </div>
                                                <!-- /.modal -->
                                                {{-- End Model--}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr >
                                        <td colspan="7">Không có data . Cần được Tạo mới</td>
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
    <pre>
        <?php //print_r($templates); ?>
    </pre>
@endsection

@section('script')
    @include('script.user.main')
@endsection

