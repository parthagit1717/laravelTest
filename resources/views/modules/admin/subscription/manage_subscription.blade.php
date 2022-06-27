@extends('layouts.app2')
@section('title') Manage Subscription @endsection
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
                <h1 class="page-title">Manage Subscription</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Subscription</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                          <div class="row"  style="width: 100%;">
                            <div class="col-sm-6">
                              <h3 class="card-title">Subscription List</h3>
                            </div>
                            
                            <div class="col-sm-6" style="float: right;">
                              <a href="javascript:void(0)" class="btn btn-info" id="add-new-post">Add Subscription <i class="fa fa-plus-circle"></i></a>  
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
                                       <th>ID</th>
        				                       <th>Title</th>
        				                       <th>Price</th>
        				                       <th>Validity(Days)</th>
        				                       <th>Description</th>
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
               <input type="hidden" name="subs_id" id="subs_id">
                <div class="form-group">
                    <label for="name" class="col-sm-4 control-label">Subscription Name</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="name" id="name" placeholder="Subscription Name" value="">
                      <span class="text-danger" id="nameError"></span>   
                    </div>
                                 
                </div>
     
                <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Description</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_desc" id="sub_desc" placeholder="Subscription Description" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subdescError"></span>
                    </div>
                </div>

                 <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Validity(Days)</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_vali" id="sub_vali" placeholder="30 or 90 etc..." aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subvaliError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-6 control-label">Subscription Price(INR)</label>
                    <div class="col-sm-12">
                      <input type="text" class="form-control" name="sub_price" id="sub_price" placeholder="Subscription Price(INR)" aria-label="clarice@example.com" value="">
                      <span class="text-danger" id="subpriceError"></span>
                    </div>
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
      "processing": true,
      "serverSide": true,
        "ajax":{
                 "url": "{{ url('allsubscription') }}",
                 "dataType": "json",
                 "type": "get",
                 "data":{ _token: "{{csrf_token()}}"}
               },
               
        "columns": [
            { "data": "id" },
            { "data": "sub_title" }, 
            { "data": "sub_price" },
            { "data": "sub_vali" },
            { "data": "sub_desc" },
            { "data": "options" }
            
        ] 
  });  


  $('#add-new-post').click(function () {
    $('#btn-save').val("create-post");
    $('#post_id').val('');
    $('#postForm').trigger("reset");
    $('#postCrudModal').html("Add New Subscription");
    $('#ajax-crud-modal').modal('show');
    var url= 'public/asset/dist/img/avatar5.png';
      $('#imagefields').attr('src',url);
  });

  $('#modelclose').click(function (a) {
    $('#postForm').trigger("reset");
    $('#ajax-crud-modal').modal('hide'); 
  });

  $('#ajax-crud-modal').on('hide.bs.modal', function() { 
        $('#nameError').text('');
        $('#subdescError').text('');
        $('#subvaliError').text('');
        $('#subpriceError').text(''); 
        $('.add_tb_row').html('Save');
    }); 
   

  $('body').on('click', '.edit-post', function () {
  var post_id = $(this).data('id');
  $.get('editsubscription/'+post_id, function (data) {
      $('#name-error').hide();
      $('#email-error').hide();
      $('#postCrudModal').html("Edit Subscription");
      $('#btn-save').val("edit-post");
      $('#ajax-crud-modal').modal('show');
      $('#subs_id').val(data.id);
      $('#name').val(data.sub_title); 
      $('#sub_desc').val(data.sub_desc); 
      $('#sub_vali').val(data.sub_vali); 
      $('#sub_price').val(data.sub_price); 
        
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
      var subs_id = id; 
      
      swal({
        title: "Are you sure?",
        text: "You want to inactive this subscription ?",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
         $.ajax({
              type: "get",
              url: "inactivesubscription/"+subs_id,
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
      var subs_id = id; 

        swal({
        title: "Are you sure?",
        text: "You want to active this subscription?",
        icon: "success", 
        buttons: ["No","Yes !"],
         
      })
      .then((willDelete) => {
        if (willDelete) 
        {
            $.ajax({
              type: "get",
              url: "activesubscription/"+subs_id,
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
   
     url: "{{ route('store_subscription') }}",
   
     data:formData,
   
     cache:false,
   
     contentType: false,
   
     processData: false,
   
          success: function (data) {  
               
               if(typeof data.errors !== 'undefined' && data.errors!=''){
                  // alert('error');
                  var err = data.errors;
                  console.log(err);
                  $('#nameError').text('');
                  $('#subdescError').text('');
                  $('#subvaliError').text('');
                  $('#subpriceError').text(''); 
                  $('.add_tb_row').html('Save');

                  if(typeof(err.name) != "undefined" && err.name !== '') {
                      $('#nameError').text(err.name[0]);
                  }

                  if(typeof(err.sub_desc) != "undefined" && err.sub_desc !== '') {
                      $('#subdescError').text(err.sub_desc[0]);
                  }

                  if(typeof(err.sub_vali) != "undefined" && err.sub_vali !== '') {
                      $('#subvaliError').text(err.sub_vali[0]);
                  }

                  if(typeof(err.sub_price) != "undefined" && err.sub_price !== '') {
                      $('#subpriceError').text(err.sub_price[0]);
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

 





 