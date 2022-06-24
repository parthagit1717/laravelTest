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
                        <div class="card-header" style="border:none!important;"> 
                            <div class="column">
                                <img src="{{asset('assets/images/brand/ekm.png')}}" alt="ekm" style="width: 100px;height:40px; float: left;" class="">
                            </div>
                            <div class="column">
                                <h4 class="card-title" style="font-size:22px; color:#b30047;font-weight: bold;">Account</h4>
                            </div> 
                        </div>

                       <!--  <div class="" style="padding:0px 20px; background-color:#e6fff2; border:1px solid #33ff99; border-radius: 5px;width: 90%;margin: 0px 27px;" >
                            <h5 style="margin: 0px !important; padding: 6px 0px;"><i class="fa fa-check" aria-hidden="true" style="color:#00994d;"></i> Connected</h5> 
                        </div> --> 
                        <hr style="border-top: dotted 1px;">
                        <div class="card-body">
                            @if($accountserviceid->status==0)
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
                                        <!-- <button class="btn btn-primary ekmauthorize">Connect</button> -->
                                        <a href="javascript:void(0)" class="btn btn-primary" id="add-new-post"> Connect</a>
                                    </div>
                                </div>
                            </form>
                            @else
                            <div class="" style="padding:0px 20px; background-color:#e6fff2; border:1px solid #33ff99; border-radius: 5px;width: 90%;margin: 0px 27px;" >
                                <h5 style="margin: 0px !important; padding: 6px 0px;"><i class="fa fa-check" aria-hidden="true" style="color:#00994d;"></i> Connected</h5> 
                            </div> 
                            @endif
                            <div>
                                <a href="javascript:void(0)" class="btn btn-success mt-4">Import product </a>
                            </div>
                            
                            <!-- <a class="btn btn-primary ekmauthorize">Connect</a> -->
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-xl-6">
                    <div class="card">
                        <div class="card-header" style="border:none!important;"> 
                            <div class="column">
                                <img src="{{asset('assets/images/brand/ebay.png')}}" alt="ekm" style="width: 100px;height:40px; float: left;" class="">
                            </div>
                            <div class="column">
                                <h4 class="card-title" style="font-size:22px; color:#b30047;font-weight: bold;">Account</h4>
                            </div> 
                        </div>

                        <!-- <div class="" style="padding:0px 20px; background-color:#e6fff2; border:1px solid #33ff99; border-radius: 5px;width:90%;margin: 0px 27px;" >
                            <h5 style="margin: 0px !important; padding: 6px 0px;"><i class="fa fa-check" aria-hidden="true" style="color:#00994d;"></i> Connected</h5> 
                        </div> --> 
                        <hr style="border-top: dotted 1px;">
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
                                        <a class="btn btn-primary">Connect</a>
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

<div class="modal fade" id="ajax-crud-modal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="postCrudModal"></h4>
            <span  id="modelclose" style="cursor: pointer; color: red;"><i class="fa fa-times-circle" aria-hidden="true"></i></span> 
        </div>
        <div class="modal-body">
            <form id="postForm" name="postForm" class="form-horizontal" enctype="multipart/form-data">
               {{ csrf_field() }}
                
                <div class="form-group">
                    <label for="service_name" class="col-sm-4 control-label">Integuration Name</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="integuration_name" id="integuration_name" placeholder="Integuration Name" value="" required>
                      <span class="text-danger" id="nameError"></span>   
                    </div>
                                 
                </div>
     
                <div class="form-group">
                    <label class="col-sm-6 control-label">Currency</label>
                    <div class="col-sm-12">
                        <select name="ekm_currency" class="form-control form-select" data-bs-placeholder="Select Currency" required>
                            <option label="Select Currency"></option>  
                            @foreach($currencies as $showcurrencies)
                                <option value="{{$showcurrencies['code']}}"> {{$showcurrencies["name"]}}</option> 
                            @endforeach
                        </select>
                      <!-- <input type="text" class="form-control" name="sub_desc" id="sub_desc" placeholder="Subscription Description" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subdescError"></span> -->
                      <span class="text-danger" id="currenError"></span>
                    </div>
                </div> 

                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-success add_tb_row" id="btn-save" value="create">Install App</button>
                </div>
            </form>
        </div>
        <div class="modal-footer"> 
            <div style="border: 1px solid #f4f4f4;  width: 100%; background-color:#f5f5f0;border-radius: 5px;">
                <p style="padding:5px 5px 0px 5px;margin-bottom: 0px;">Connect your EKM store To OnePatch</p>
            </div>
            <div style="border: 1px solid #f4f4f4;  width: 100%; background-color:#f5f5f0;border-radius: 5px;">
                <p style="padding:5px 5px 0px 5px;margin-bottom: 0px;">1. Make sure you are logged into EKM on another browser</p>
            </div>
            <div style="border: 1px solid #f4f4f4;  width: 100%; background-color:#f5f5f0;border-radius: 5px;">
                <p style="padding:5px 5px 0px 5px;margin-bottom: 0px;">2. Enter a integuration name for your EKM account( This is help to indentify your service easier and can be anything you like.) </p>
            </div>
            <div style="border: 1px solid #f4f4f4;  width: 100%; background-color:#f5f5f0;border-radius: 5px;">
                <p style="padding:5px 5px 0px 5px;margin-bottom: 0px;">3. Click install app </p>
            </div>
        </div>
        
    </div>
  </div>
</div>



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
<script>

    $(document).ready( function () {
       $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#add-new-post').click(function () {
        $('#btn-save').val("create-post");
        $('#post_id').val('');
        $('#postForm').trigger("reset");
        $('#postCrudModal').html("EKM Configuration");
        $('#ajax-crud-modal').modal('show');
        var url= 'public/asset/dist/img/avatar5.png';
          $('#imagefields').attr('src',url);
        });

        $('#modelclose').click(function (a) {
            $('#postForm').trigger("reset");
            $('#ajax-crud-modal').modal('hide'); 
        });
    });
</script>

<script>
 
$(document).ready(function (e) {
 
  $('#postForm').on('submit',(function(e) {

  $('.savbtn').html('<i class="fa fa-spinner fa-spin"></i> Submitting...');
   
  $.ajaxSetup({
   
  headers: {
   
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
   
  }
   
  });
   
  e.preventDefault();
   
  var formData = new FormData(this);


  $.ajax({
   
     type:'POST',
   
     url: "{{ route('saveekmdata') }}",
   
     data:formData,
   
     cache:false,
   
     contentType: false,
   
     processData: false,
   
          success: function (data) {   
               console.log(data);
                $('#postForm').trigger("reset");
                $('#ajax-crud-modal').modal('hide');
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

      // Hide loder
   
  }));
   
});
 
</script> 

@endsection





 