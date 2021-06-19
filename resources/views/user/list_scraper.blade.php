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
    <?php
    if (Session::has('get_scrap')) {
        $ss_scrap = Session('get_scrap');
    } else {
        $ss_scrap = false;
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
                            <h3 class="card-title">Danh sách</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Template</th>
                                    <th>Url</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(sizeof($lists) > 0)
                                    @foreach ($lists as $key => $item)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $item->template_id }}</td>
                                            <td>{{ $item->url }}</td>
                                            <td>{!! getStatus($item->status) !!}</td>
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

