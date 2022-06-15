@extends('layouts.app2')
@section('title') Settings @endsection
 
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Settings</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row">
                <div class="col-md-12 col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">EKM Account</h4>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="">
                                    <!-- <div class="form-group">
                                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                                    </div> -->
                                     
                                </div>

                                <div class="mb-0 mt-4 row justify-content-end">
                                    <div class="col-lg-10 col-md-9"> 
                                        <!-- <button class="btn btn-primary" onClick="window.open('{{$ekmurl}}');">Connect</button> -->
                                        <button class="btn btn-primary ekmauthorize">Connect</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Ebay Account</h4>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal">
                                <!-- <div class=" row mb-4">
                                    <label for="inputName" class="col-md-3 form-label">User Name</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" id="inputName" placeholder="Name">
                                    </div>
                                </div>
                                <div class=" row mb-4">
                                    <label for="inputEmail3" class="col-md-3 form-label">Email</label>
                                    <div class="col-md-9">
                                        <input type="email" class="form-control" id="inputEmail3" placeholder="Email">
                                    </div>
                                </div> -->
                                 
                                 
                                <div class="mb-0 mt-4 row justify-content-end">
                                    <div class="col-lg-10 col-md-9"> 
                                        <button class="btn btn-danger">Disconnect</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close-->



@endsection

@section('script')
<script>
    $(document).on("click", ".ekmauthorize", function (e) {

        $.ajaxSetup({
          headers: {           
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')           
            }       
        });

        $(".fullpageloader").show(); 
        e.preventDefault();
        var service_name = $("#service_name").val();
        /*var ekm_store_url = $("#ekm_store_url").val();*/
        var ekm_currency = $("#ekm_currency").val();
        if (service_name == "") {
            swal("Please enter service name. !!");
            $(".fullpageloader").hide();
            return false;
        }
        /*else if (ekm_store_url == "") {
            swal("Please enter store url. !!");
            return false;
        }else if(ekm_store_url){
           var regex = new RegExp("(http|https)://(youraccount)\.([0-9][0-9]{0,2})\.(be|ekm.net)\/(be|admin)\/(be|dashboard|dashboard/)$");
           if(regex.test(ekm_store_url)==false){
                swal("Please enter valid store url. !!");
                return false;
           }

        }*/
        $.ajax({
            type: 'POST',
            url: "{{ route('authekmservice') }}",
            headers: {           
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')           
            },
            success: function (data) {
                //window.open(data);
                console.log(data);
                var title = "Register", w = 900, h = 800;
                var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
                var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

                var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                var systemZoom = width / window.screen.availWidth;
                var left = (width - w) / 2 / systemZoom + dualScreenLeft
                var top = (height - h) / 2 / systemZoom + dualScreenTop
                var authWindow = window.open(data, title, 'scrollbars=yes, location=no, status=no, menubar=yes, width=' + w / systemZoom + ', height=' + h / systemZoom + ', top=' + top + ', left=' + left);
                $(".fullpageloader").hide();
            },
            statusCode:{ 
                500: function () {
                    swal("Invalid API Key OR secret.", "", "error");
                    $(".fullpageloader").hide();
                }
            },
        });




    });

</script>
@endsection





 