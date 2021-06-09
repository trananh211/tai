@if (Session::has('error') || Session::has('info') || Session::has('warning') || Session::has('success'))
    {{-- Aler--}}
    <div class="row">
        <div class="col-md-12">
            <!-- /.card-header -->
            <div class="card-body">
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Alert!</h5>
                        {{ Session('error') }}
                    </div>
                @endif
                @if (Session::has('info'))
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-info"></i> Info!</h5>
                        {{ Session('info') }}
                    </div>
                @endif
                @if (Session::has('warning'))
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Cảnh báo!</h5>
                        {{ Session('warning') }}
                    </div>
                @endif
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        {{ Session('success') }}
                    </div>
                @endif
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
        <!-- /.col -->
    </div>
@endif
{{-- End Aler--}}
