@extends('layouts.master')

<?php
    $page_title = 'Danh sách các website đang crawl';
    $page_link = url('list-scraper');
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
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Template</th>
                                    <th>Sku</th>
                                    <th>Store</th>
                                    <th>Url</th>
                                    <th>Platform</th>
                                    <th>Status</th>
                                    <th>T Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(sizeof($lists) > 0)
                                    @foreach ($lists as $key => $item)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $item->template_name }}</td>
                                            <td>{{ ($item->sku_auto == 1) ? $item->sku.'++' : $item->sku }}</td>
                                            <td>{{ $item->store_name }}</td>
                                            <td>{!! subString($item->url) !!}</td>
                                            <td>{!! getTypeStore($item->type_platform) !!}</td>
                                            <td>
                                                {!! getStatus($item->status) !!}
                                            </td>
                                            <td>
                                                {!! getTStatus($item->t_status) !!}
                                            </td>
                                            <td>

                                                <a href="{{ url('import-product-web-scrap/'.$item->id) }}">
                                                    <button type="button" class="btn btn-xs btn-block btn-info">Import Handle</button>
                                                </a>

                                                <button type="button" class="btn btn-xs btn-block btn-default"
                                                        data-toggle="modal" data-target="#modal-xl-{{ $item->id }}">Edit</button>

                                                <a href="{{ url('delete-web-scrap/'.$item->id) }}"
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ website scrap này?');"
                                                >
                                                    <button type="button" class="btn btn-xs btn-block btn-danger">Delete</button>
                                                </a>

                                                {{-- Model--}}
                                                <div class="modal fade" id="modal-xl-{{ $item->id }}">
                                                    <div class="modal-dialog modal-xl">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">{{ $item->template_name }} - {{ $item->sku }}</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <form action="{{url('edit-product-info')}}" method="post">
                                                                    {{ csrf_field() }}
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <input type="text" class="form-control hidden" name="id" value="{{ $item->id }}" >
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <label for="product_name_change">Product Name Change</label>
                                                                                <input value="{{ $item->product_name_change }}"
                                                                                    type="text" id="product_name_change" class="form-control" name="product_name_change"
                                                                                       placeholder="Nhập ký tự product mới ở đây" >
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <label for="product_name_exclude">Product Name Exclude</label>
                                                                                <input value="{{ ($item->product_name_exclude) }}"
                                                                                    type="text" id="product_name_exclude" class="form-control" name="product_name_exclude"
                                                                                       placeholder="Nhập ký tự product cần bỏ ở đây" >
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-2">
                                                                                <div class="form-group">
                                                                                    <label for="sku">SKU (cố định)</label>
                                                                                    <input value="{{ ($item->sku) }}"
                                                                                        type="text" id="sku" class="form-control" name="sku"
                                                                                           placeholder="SKU fixed">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="form-group">
                                                                                    <label for="sku_auto">Auto SKU</label>
                                                                                    <small class="text-info">
                                                                                        Nếu sản phẩm không có SKU cố định.
                                                                                        Hãy chọn trường này để hệ thống gen tự động mã SKU
                                                                                    </small>
                                                                                    <input value="{{ ($item->sku_auto != 0) ? $item->sku_auto : '' }}"
                                                                                        type="text" id="sku_auto" class="form-control" name="sku_auto"
                                                                                           placeholder="Điền mã sku của sản phẩm">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-sm-3" title="Xoá đoạn text của title gốc">
                                                                                <div class="form-group">
                                                                                    <label>Bỏ text trong title</label>
                                                                                    <input value="{{ ($item->exclude_text) }}"
                                                                                        type="text" class="form-control" name="exclude_text"
                                                                                           placeholder="VD: HYA102">
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-sm-3" title="Bỏ ảnh nếu thứ tự ảnh không giống nhau">
                                                                                <div class="form-group">
                                                                                    <label>Loại ảnh</label>
                                                                                    <input value="{{ ($item->exclude_image) }}"
                                                                                        type="text" class="form-control" name="exclude_image"
                                                                                           placeholder="VD: link1, link2">
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
                                        <td colspan="8">Không có data . Tạo mới ở <a href="{{ url('view-scraper') }}">đây</a></td>
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

