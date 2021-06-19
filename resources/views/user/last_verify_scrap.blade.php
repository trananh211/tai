@extends('layouts.master')

<?php
    $page_title = 'Scraper Setup : Verify Product Page';
    $page_link = url('view-scraper');
    $breadcrumb = [
        'page_title' => $page_title,
        'page_link' => $page_link
    ];
    ?>
@section('title',$page_title)

@section('content')
    <?php
    if (Session::has('get_scrap')) {
        $ss_scrap = Session('get_scrap');
    } else {
        $ss_scrap = false;
    }
    ?>
    <!-- Content Header (Page header) -->
    @include('layouts.breadcrumb', $breadcrumb)
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Main row -->
            <div class="row">
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">SETUP Basic</h3>
                        </div>
                        <!-- /.card-header -->
                        <!-- form start -->
                        <form action="{{ url('save-data-scrap-setup') }}" method="post" >
                            {{ csrf_field() }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Select Template (Bắt Buộc)</label>
                                            <select name="template_id" class="form-control" required>
                                                <option value="0">Chọn Template</option>
                                                @foreach( $templates as $template_id => $template_name)
                                                    <option {{ ($ss_scrap && $template_id == $ss_scrap['template_id']) ? 'selected' : ''}}
                                                            value="{{ $template_id }}"> {{ $template_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Kiểu tải trang</label>
                                            <select name="type_page_load" class="form-control" required>
                                                <option value="0">Chọn kiểu</option>
                                                @foreach( $typePageLoad as $typePageLoad_id => $typePageLoad_name)
                                                    <option {{ ($ss_scrap && $typePageLoad_id == $ss_scrap['type_page_load']) ? 'selected' : ''}}
                                                            value="{{ $typePageLoad_id }}">{{ $typePageLoad_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3" title="Xoá đoạn text của title gốc">
                                        <label>Bỏ text trong title</label>
                                        <input value="{{ ($ss_scrap) ? $ss_scrap['exclude_text'] : ''}}"
                                               type="text" class="form-control" name="exclude_text" placeholder="VD: HYA102" >
                                    </div>
                                    <div class="col-sm-3"  title="Chọn ảnh theo thứ tự hiển thị ở trang product gốc">
                                        <label>Chọn Ảnh</label>
                                        <input value="{{ ($ss_scrap) ? $ss_scrap['image_array'] : ''}}"
                                               type="text" class="form-control" name="image_array" placeholder="VD: 1,2,3,5">
                                    </div>
                                    <div class="col-sm-3" title="Bỏ ảnh nếu thứ tự ảnh không giống nhau">
                                        <label>Loại ảnh</label>
                                        <input value="{{ ($ss_scrap) ? $ss_scrap['exclude_image'] : ''}}"
                                               type="text" class="form-control" name="exclude_image" placeholder="VD: link1, link2" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <label>Dữ liệu để crawl</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card card-outline card-info">
                                                    <div class="card-header bg-navy color-palette">
                                                        <h3 class="card-title">
                                                            Dữ liệu ngoài Category
                                                        </h3>
                                                    </div>
                                                    <!-- /.card-header -->
                                                    <div class="card-body">
                                                    <textarea name="catalog_source" rows="15" cols="200" style="width:100%;">
                                                        {{ ($ss_scrap) ? $ss_scrap['catalog_source'] : ''}}
                                                    </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card card-outline card-info">
                                                    <div class="card-header bg-navy color-palette">
                                                        <h3 class="card-title">
                                                            Dữ liệu Trang Sản Phẩm
                                                        </h3>
                                                    </div>
                                                    <!-- /.card-header -->
                                                    <div class="card-body">
                                                    <textarea name="product_source" rows="15" cols="200" style="width:100%;">
                                                        {{ ($ss_scrap) ? $ss_scrap['product_source'] : ''}}
                                                    </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.col-->
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
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

    <section class="content">
        <div class="container-fluid">
            <!-- Main row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-danger color-palette">
                            <h3 class="card-title">Kiểm tra lại lần cuối trước khi lưu data từ Server gửi về</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Product Title</th>
                                    <th>Link</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($lst_product as $key => $item)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>
                                            <img alt="" style="height: 100px; width: auto;"
                                                 src="{{ ($item['img'] != null) ? $item['img'] : asset('/admin-lte/dist/img/default-150x150.png') }}"
                                            >
                                        </td>
                                        <td>{{ $item['title'] }}</td>
                                        <td><a href="{{ $item['link'] }}" target="_blank">Link</a></td>
                                    </tr>
                                @endforeach
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

