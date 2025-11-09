<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Partha Test">
    <meta name="author" content="Partha">
    <meta name="keywords" content="admin, admin dashboard, admin panel, responsive admin, ui kit, laravel sash template">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/brand/favicon.ico') }}" />

    <!-- TITLE -->
    <title>Partha Admin | @yield('title')</title>

    @include('includes.links')
    <!-- CUSTOM CSS -->
    <style>
        /* Buttons */
        .user-btn-action, .action-btn {
            padding: 5px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin: 2px 2px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-width: 90px;
        }
        .user-btn-action.verify, .action-btn.btn-info { background-color: #007bff; }
        .user-btn-action.block, .action-btn.btn-danger { background-color: #dc3545; }
        .user-btn-action.activate, .action-btn.activate-btn { background-color: #28a745; }

        .user-btn-action:hover, .action-btn:hover { filter: brightness(0.9); }

        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
        }
        .status-badge.active { background-color: #28a745; }
        .status-badge.inactive { background-color: #dc3545; }
        .status-badge.unverified { background-color: #ffc107; color: #000; }
        .status-badge.verified { background-color: #007bff; }

        /* Tables */
        .users-table-modern {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            border: 1.5px solid #e6ebf8;
            background: #fff;
        }
        .users-table-modern th, .users-table-modern td {
            padding: 12px 16px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }
        .users-table-modern th {
            background: #e9f0fd;
            color: #253872;
            font-weight: 700;
        }
        .users-table-modern tr:hover { background: #f5f8fd; }

        /* User filter bar */
        .user-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 25px;
        }
        .user-filter-bar input, .user-filter-bar select {
            border-radius: 20px;
            padding: 8px 18px;
            border: 1px solid #dde3ec;
            background: #f8fafc;
            font-size: 1rem;
            min-width: 145px;
        }
        .filter-btn, .reset-btn {
            border-radius: 22px;
            padding: 8px 28px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            border: none;
        }
        .filter-btn { background: #5548fe; color: #fff; }
        .filter-btn:hover { background: #3e35cc; }
        .reset-btn { background: #ecedf7; color: #3a3e5b; }
        .reset-btn:hover { background: #dadbf3; color: #222; }

        /* Post card */
        .fb-post-card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 8px #0001; margin-bottom: 28px; }
        .fb-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; }
        .fb-posttext { font-size: 1rem; line-height: 1.4; color: #222; margin-bottom: 10px; }

        /* Image preview */
        .post-image, .modal-post-image {
            width: 120px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            display: block;
        }
        .action-btn-group {
            display: flex;
            gap: 7px;
            align-items: center;
            justify-content: start;
        }
        .action-btn {
            border-radius: 18px !important;
            font-weight: 500 !important;
            padding: 6px 18px !important;
            font-size: 0.97rem !important;
            min-width: 84px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
        }
        .btn-info.action-btn { background: #55b6f9; color: #fff; }
        .btn-info.action-btn:hover { background: #2974a1; color: #fff; }
        .btn-danger.action-btn { background: #f14668; color: #fff; }
        .btn-danger.action-btn:hover { background: #a42135; color: #fff; }
        .btn-primary.action-btn { background: #5548fe; color: #fff;}
        .btn-primary.action-btn:hover{background:#2d2759;}

         /* Universal App Admin Action Buttons */
.action-btn-group, .d-flex.gap-2 {
    display: flex !important;
    gap: 12px !important;
    align-items: center;
}

.btn-action,
.btn-pill,
.action-btn,
.btn-info.btn-pill,
.btn-danger.btn-pill,
.btn-primary.btn-pill,
.btn-success.btn-pill {
    border-radius: 22px !important;
    font-weight: 600 !important;
    min-width: 94px !important;
    font-size: 1rem !important;
    padding: 7px 24px !important;
    letter-spacing: 0.01em;
    transition: background 0.13s, color 0.13s, box-shadow 0.15s;
    box-shadow: none;
    border: none;
    outline: none;
    cursor: pointer;
}

.btn-info,
.btn-info.btn-pill {
    background: #55b6f9 !important;
    color: #fff !important;
}
.btn-info:hover, .btn-info:focus { background: #329ad5 !important; }

.btn-danger,
.btn-danger.btn-pill {
    background: #f14668 !important;
    color: #fff !important;
}
.btn-danger:hover, .btn-danger:focus { background: #d5304c !important; }

.btn-primary,
.btn-primary.btn-pill {
    background: #7269ef !important;
    color: #fff !important;
}
.btn-primary:hover, .btn-primary:focus { background: #574bc9 !important; }

.btn-success,
.btn-success.btn-pill {
    background: #27c97f !important;
    color: #fff !important;
}
.btn-success:hover, .btn-success:focus { background: #18a265 !important; }

.btn-sm {
    font-size: 1rem !important;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
}

/* For icons in buttons */
.btn i, .btn svg, .action-btn i, .action-btn svg {
    margin-right: 6px;
    font-size: 1.11em;
    vertical-align: middle;
}

/* Remove margin bottom from buttons, align properly */
.btn, .action-btn { margin-bottom: 0 !important; }

    </style>

 
</head>

<body class="app sidebar-mini ltr light-mode">

    @include('includes.loader')

    <!-- PAGE WRAPPER -->
    <div class="page">
        <!-- MAIN PAGE -->
        <div class="page-main">

            {{-- HEADER --}}
            @include('admin.includes.header')

            {{-- SIDEBAR --}}
            @include('admin.includes.sidebar')

            <!-- APP-CONTENT START -->
            <div class="app-content main-content mt-0">
                <div class="side-app">

                    <!-- CONTAINER -->
                    <div class="main-container container-fluid px-4 py-6">
                        {{-- MAIN PAGE CONTENT --}}
                        @yield('content')
                    </div>
                    <!-- CONTAINER END -->

                </div>
            </div>
            <!-- APP-CONTENT END -->

        </div>
        <!-- MAIN PAGE END -->

        {{-- FOOTER --}}
        @include('includes.footer')

    </div>
    <!-- PAGE WRAPPER END -->

    <!-- BACK TO TOP -->
    <a href="#top" id="back-to-top">
        <i class="fa fa-angle-up"></i>
    </a>

    <!-- JS FILES -->
    @include('includes.scripts')

    @yield('script')

    <!-- EXTERNAL LIBRARIES -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- JQUERY JS -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <!-- SHOW PASSWORD JS -->
    <script src="{{ asset('assets/js/show-password.min.js') }}"></script>

    <!-- SPARKLINE JS -->
    <script src="{{ asset('assets/js/jquery.sparkline.min.js') }}"></script>

    <!-- CHART-CIRCLE JS -->
    <script src="{{ asset('assets/js/circle-progress.min.js') }}"></script>

    <!-- C3 CHART JS -->
    <script src="{{ asset('assets/plugins/charts-c3/d3.v5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/charts-c3/c3-chart.js') }}"></script>

    <!-- INPUT MASK JS -->
    <script src="{{ asset('assets/plugins/input-mask/jquery.mask.min.js') }}"></script>

    <!-- SIDE-MENU JS -->
    <script src="{{ asset('assets/plugins/sidemenu/sidemenu.js') }}"></script>

    <!-- INTERNAL SELECT2 JS -->
    <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>

    <!-- DATA TABLE JS -->
    <script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/table-data.js') }}"></script>

    <!-- SIDEBAR JS -->
    <script src="{{ asset('assets/plugins/sidebar/sidebar.js') }}"></script>

    <!-- PERFECT SCROLLBAR JS -->
    <script src="{{ asset('assets/plugins/p-scroll/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/plugins/p-scroll/pscroll.js') }}"></script>
    <script src="{{ asset('assets/plugins/p-scroll/pscroll-1.js') }}"></script>

    <!-- COLOR THEME JS -->
    <script src="{{ asset('assets/js/themeColors.js') }}"></script>

    <!-- CUSTOM JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <!-- IMAGE PREVIEW SCRIPT -->
    <script type="text/javascript">
        function previewImage(event) {
            var reader = new FileReader();
            var imageField = document.getElementById('imagefields');
            reader.onload = function() {
                if (reader.readyState == 2) {
                    imageField.src = reader.result;
                }
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    <!-- SweetAlert2 JS (make sure this is included in your page) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function confirmAdminLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out from the admin account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log me out!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }
    </script>

</body>

</html>
