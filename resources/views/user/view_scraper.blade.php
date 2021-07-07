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
                        <form action="{{ url('post-data-scrap-setup') }}" method="post" >
                            {{ csrf_field() }}
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Select Template (Bắt Buộc)</label>
                                            <select name="template_id" class="form-control" required>
                                                <option value="0">Chọn Template</option>
                                                @foreach( $templates as $key => $item)
                                                    <?php
                                                        $template_id = $item->id;
                                                        $shop = (array_key_exists($item->type_platform, $platforms)) ? $platforms[$item->type_platform].' : '.$item->store_name : '';
                                                        $sku = ($item->sku_auto == 1) ? $item->sku.'++' : $item->sku;
                                                        $template_name = $shop.' - '.$item->name.' - '.$item->product_name.' '.$sku;
                                                    ?>
                                                    <option {{ ($ss_scrap && $template_id == $ss_scrap['template_id']) ? 'selected' : ''}}
                                                            value="{{ $template_id }}"> {{ $template_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
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
                                        <div class="form-group">
                                            <label>Bỏ text trong title</label>
                                            <input value="{{ ($ss_scrap) ? $ss_scrap['exclude_text'] : ''}}"
                                                   type="text" class="form-control" name="exclude_text"
                                                   placeholder="VD: HYA102">
                                        </div>
                                    </div>
                                    <div class="col-sm-3" title="Chọn ảnh theo thứ tự hiển thị ở trang product gốc">
                                        <div class="form-group">
                                            <label>Chọn Ảnh</label>
                                            <input value="{{ ($ss_scrap) ? $ss_scrap['image_array'] : ''}}"
                                                   type="text" class="form-control" name="image_array"
                                                   placeholder="VD: 1,2,3,5">
                                        </div>
                                    </div>
                                    <div class="col-sm-3" title="Bỏ ảnh nếu thứ tự ảnh không giống nhau">
                                        <div class="form-group">
                                            <label>Loại ảnh</label>
                                            <input value="{{ ($ss_scrap) ? $ss_scrap['exclude_image'] : ''}}"
                                                   type="text" class="form-control" name="exclude_image"
                                                   placeholder="VD: link1, link2">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <!-- select -->
                                        <div class="form-group">
                                            <label>Kiểu Tag Sản Phẩm</label>
                                            <select name="type_tag" class="form-control" required>
                                                <option value="0">Chọn kiểu</option>
                                                @foreach( $typeTag as $typeTag_id => $typeTag_name)
                                                    <option {{ ($ss_scrap && $typeTag_id == $ss_scrap['type_tag']) ? 'selected' : ''}}
                                                            value="{{ $typeTag_id }}">{{ $typeTag_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label>Tag cố định</label>
                                            <input value="{{ ($ss_scrap) ? $ss_scrap['tag_text'] : ''}}"
                                                   type="text" class="form-control" name="tag_text"
                                                   placeholder="VD: Manchester United">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label>Vị trí tag</label><smale>Vị trí thứ X trong title</smale>
                                            <input value="{{ ($ss_scrap) ? $ss_scrap['tag_position'] : ''}}"
                                                   type="number" class="form-control" name="tag_position"
                                                   placeholder="VD: 123">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <label>Dữ liệu ngoài Category</label>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="card card-outline card-info">
                                                    <div class="card-header">
                                                        <h3 class="card-title">
                                                            Dữ liệu khai báo
                                                        </h3>
                                                    </div>
                                                    <!-- /.card-header -->
                                                    <div class="card-body">
                                                    <textarea name="catalog_source" rows="16" cols="200" style="width:100%; font-size: 14px;">
                                                        {{ ($ss_scrap) ? $ss_scrap['catalog_source'] : ''}}
                                                    </textarea>
                                                    </div>
                                                    <div class="card-footer">
                                                        Khai báo đầy đủ thông tin như bên cạnh ====>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="card card-outline card-info">
                                                    <div class="card-header bg-info color-palette">
                                                        <h3 class="card-title">
                                                            Dữ liệu mẫu
                                                        </h3>
                                                    </div>
                                                    <!-- /.card-header -->
                                                    <div class="card-body">
                                                    <textarea rows="16" cols="200" disabled style="width:100%; font-size: 14px;">
                                                        {{ $data_template }}
                                                    </textarea>
                                                    </div>
                                                    <div class="card-footer bg-info color-palette">
                                                        Dữ liệu mẫu để khai báo. Chỉ copy không thể xoá được
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.col-->
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Dữ liệu Trang Sản Phẩm</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-outline card-info">
                                                <div class="card-header bg-navy color-palette">
                                                    <h3 class="card-title">
                                                        Dữ liệu khai báo
                                                    </h3>
                                                </div>
                                                <!-- /.card-header -->
                                                <div class="card-body">
                                                    <textarea name="product_source" rows="10" cols="200" style="width:100%;">
                                                        {{ ($ss_scrap) ? $ss_scrap['product_source'] : ''}}
                                                    </textarea>
                                                </div>
                                                <div class="card-footer bg-navy color-palette">
                                                    Khai báo đầy đủ thông tin như bên cạnh ====>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card card-outline card-info">
                                                <div class="card-header bg-olive color-palette">
                                                    <h3 class="card-title">
                                                        Dữ liệu mẫu
                                                    </h3>
                                                </div>
                                                <!-- /.card-header -->
                                                <div class="card-body">
                                                    <textarea rows="10" cols="200" disabled style="width:100%;">
                                                        {{ $product_template }}
                                                    </textarea>
                                                </div>
                                                <div class="card-footer bg-olive color-palette">
                                                    Dữ liệu mẫu để khai báo. Chỉ copy không thể xoá được
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.col-->
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
@endsection

@section('script')
    @include('script.user.main')
@endsection

