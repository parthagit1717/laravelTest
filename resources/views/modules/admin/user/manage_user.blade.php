@extends('layouts.app2')
@section('title') User Details @endsection
@section('links')
@include('includes.links') 
<style>
@media only screen and (min-width:576px) {
    a#add-new-post {float: right;}
   }
 </style>
 <style type="text/css">
    .paginate_button {
    background-color: blue;
    }
    #laravel_datatable_length{
    font-size: 17px !important;
    }
 </style>
@endsection
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">User Details</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Details</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                          <div class="row" style="width: 100%;">
                            <div class="col-sm-6">
                              <h3 class="card-title">User List</h3>
                            </div>
                            
                            <div class="col-sm-6" style="float: right;">
                              <a href="javascript:void(0)" class="btn btn-info" id="add-new-post">Add New User <i class="bi bi-person-plus"></i></a>  
                            </div>
                          </div>
                        </div>
                        <div class="card-body">
                            
                            <p class="alert alert-success" id="msgdiv" style="display: none; font-size: 16px;"><span class="badge badge-pill badge-secondary mr-2" style="background-color: #00e673"> Success ! </span> <span id="msg" style="color:black;"></span><a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right">×</a></p>

                             <p class="alert alert-primary" id="msgdiv2" style="display: none; background:#ffb3d9; font-size: 16px;"><span class="badge badge-pill badge-secondary mr-2" style="background-color: #cc0066;"> Status ! </span> <span id="msg2" style="color: black;"></span><a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right">×</a></p>

                            <!-- For User only.... -->  
                            <table class="table table-striped table-bordered dt-responsive nowrap" style="width:100%; font-size: 15px;" id="laravel_datatable">
                                <thead>
                                    <tr>
                                       <th>User ID</th>
                                       <th>Name</th>
                                       <th>Email</th>
                                       <th>Action</th> 
                                    </tr>
                                </thead>
                          </table>
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
               <input type="hidden" name="user_id" id="user_id">
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Name</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="name" id="name" placeholder="Full name" value="">
                      <span class="text-danger" id="nameError"></span>   
                    </div>
                                 
                </div>
     
                <div class="form-group">
                    <label class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="email" id="email" placeholder="clarice@example.com" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="emailError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label">Password</label>
                    <div class="col-sm-12">
                      <input type="password" class="form-control" name="password" id="password" placeholder="Type your password" value="">
                      <span class="text-danger" id="passwordError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label">Confirm Password</label>
                    <div class="col-sm-12">
                      <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirm password" value="">
                      <span class="text-danger" id="conpasswordError"></span>
                    </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">Profile Image</label>
                  <div class="col-sm-12">
                    <input type="file" name="image" onchange="previewImage(event)" class="mb-3"> 

                    <div class="polaroid "> 
                        <img src="{{asset('assets/images/users/7.png')}}" alt="5 Terre" style="width: 100px;height: 120px;" id="imagefields"> 
                    </div>
                  </div><span class="text-danger" id="imageError"></span>
                </div>   

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-primary add_tb_row" id="btn-save" value="create">Save
                 </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
  </div>
</div>




@endsection

@section('script')
 <script>
$(document).ready( function () {
   $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $('#laravel_datatable').DataTable({
      'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': [-1] /* 1st one, start by the right */
    }],
      "processing": true,
      "serverSide": true,

        "ajax":{
                 "url": "{{ url('alluser') }}",
                 "dataType": "json",
                 "type": "get",
                 "data":{ _token: "{{csrf_token()}}"}
               },
               
        "columns": [
            { "data": "id" },
            { "data": "name","orderable": "false" }, 
            { "data": "email","orderable": "false" },
            { "data": "options" }
            
        ]
  });  


  $('#add-new-post').click(function () {
    $('#btn-save').val("create-post");
    $('#post_id').val('');
    $('#postForm').trigger("reset");
    $('#postCrudModal').html("Add New User");
    $('#ajax-crud-modal').modal('show');
    var url= 'assets/images/users/7.png';
      $('#imagefields').attr('src',url);
  });

  $('#modelclose').click(function (a) {
    $('#postForm').trigger("reset");
    $('#ajax-crud-modal').modal('hide'); 
  });

  $('#ajax-crud-modal').on('hide.bs.modal', function() { 
        $('#nameError').text('');
        $('#emailError').text('');
        $('#passwordError').text('');
        $('#conpasswordError').text('');
        $('#imageError').text('');
        $('.add_tb_row').html('Save');
    }); 
   

  $('body').on('click', '.edit-post', function () {
  var post_id = $(this).data('id');
  $.get('editemp/'+post_id, function (data) {
      $('#name-error').hide();
      $('#email-error').hide();
      $('#postCrudModal').html("Edit User");
      $('#btn-save').val("edit-post");
      $('#ajax-crud-modal').modal('show');
      $('#user_id').val(data.id);
      $('#name').val(data.name); 
      $('#email').val(data.email); 
      if(data.image==null)
      {
        var url= 'assets/images/users/7.png';
      }
      else{
        var url= 'storage/images/user_image/'+data.image;
      }
      
      $('#imagefields').attr('src',url);
  })
});

  // Add site Url 
  $('body').on('click', '.add-site', function () {
      var userid = $(this).data('id');  
      $('#add-site-modal').modal('show');
      $('#userid').val(userid);  
  });

  // Reset Modal From...
  $('#add-site-modal').on('hide.bs.modal', function() { 
        $('#urlError').text(''); 
        $('.add_tb_row').html('Save');
    });
 
});
  
</script> 

<!-- For Inactive User... -->
<script type="text/javascript"> 
  function inactive(id){
      var user_id = id; 

      swal({
        title: "Are you sure?",
        text: "You want to inactive this user ?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
         $.ajax({
              type: "get",
              url: "inactiveuser/"+user_id,
              success: function (data) {
                  var oTable = $('#laravel_datatable').dataTable(); 
                  oTable.fnDraw(false);
                  $('#msgdiv2').show();
                  $('#msg2').text(data.success);
                  window.setTimeout(function(){
                    $("#msgdiv2").slideUp();
                  }, 4000);
              },
              error: function (data) {
                  console.log('Error:', data);
              }
            });
        } else {
         return false;
        }
      });
   
  }
  
</script>
<!--End For Inactive User... -->

<!-- For Active User... -->
<script type="text/javascript"> 
  function active(id){
      var user_id = id; 

        swal({
        title: "Are you sure?",
        text: "You want to active this user ?",
        icon: "success", 
        buttons: ["No","Yes !"],
         
      })
      .then((willDelete) => {
        if (willDelete) 
        {
            $.ajax({
              type: "get",
              url: "activeuser/"+user_id,
              success: function (data) {
                  var oTable = $('#laravel_datatable').dataTable(); 
                  oTable.fnDraw(false);
                  $('#msgdiv').show();
                  $('#msg').text(data.success);
                  window.setTimeout(function(){
                    $("#msgdiv").slideUp();
                  }, 4000);
              },
              error: function (data) {
                  console.log('Error:', data);
              }
            });
            return true;
        } 
        else 
        {
         return false;
        }
      });
 
  }
  
</script>
<!--End For Active User... -->  

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
   
     url: "{{ route('store_user') }}",
   
     data:formData,
   
     cache:false,
   
     contentType: false,
   
     processData: false,
   
          success: function (data) {  
               
               if(typeof data.errors !== 'undefined' && data.errors!=''){
                  // alert('error');
                  var err = data.errors;
                  $('#nameError').text('');
                  $('#emailError').text('');
                  $('#passwordError').text('');
                  $('#conpasswordError').text('');
                  $('#imageError').text('');
                  $('.add_tb_row').html('Save');

                  if(typeof(err.name) != "undefined" && err.name !== '') {
                      $('#nameError').text(err.name[0]);
                  }

                  if(typeof(err.email) != "undefined" && err.email !== '') {
                      $('#emailError').text(err.email[0]);
                  }

                  if(typeof(err.password) != "undefined" && err.password !== '') {
                      $('#passwordError').text(err.password[0]);
                  }

                  if(typeof(err.password_confirmation) != "undefined" && err.password_confirmation !== '') {
                      $('#conpasswordError').text(err.password_confirmation[0]);
                  }

                  if(typeof(err.image) != "undefined" && err.image !== '') {
                      $('#imageError').text(err.image[0]);
                  }
                     
              }

             else if(typeof data.sucs !== 'undefined' && data.sucs!=''){
                
                $('#postForm').trigger("reset");
                $('#ajax-crud-modal').modal('hide');
                $('#btn-save').html('Save Changes');
                var oTable = $('#laravel_datatable').dataTable();
                oTable.fnDraw(false);
                $('#msgdiv').show();
                  $('#msg').text(data.sucs);
                  window.setTimeout(function(){
                    $("#msgdiv").slideUp();
                  }, 4000);
                console.log(data.error);
              } 


          },
          error: function (response) { 
              $('#btn-save').html('Save Changes');
          }
   
      });

      // Hide loder
   
  }));
   
});
 
</script>
 
@endsection

 





 