<div class="message alert" style="display: none; text-align: center;">
    <a href="javascript:void(0);" class="close">&times;</a>
    <span></span>
</div>
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible" style="text-align: center;">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <ul>
            @foreach ($errors->all() as $error)
                <li style="list-style: none;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- OLD.... -->
<!-- @if(session()->has('success'))
    <p class="alert alert-success col-lg-12" id="success-alert" style="font-size: 16px; background-color: #99ffcc"><span class="badge badge-pill badge-success" style="background-color: #00cc66"> Success !</span>
        <span id="msg" >
        {{ session()->get('success') }}</span>
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    </p>
@endif -->
<!--END OLD.... -->

@if(session()->has('success'))
  <div class="alert alert-success" id="success-alert" role="alert">
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true">×</button>
      <span class="badge badge-pill badge-success" style="background-color: #00cc66">Success !</span>
      <span id="msg" >{{ session()->get('success') }}</span>
  </div>
@endif



@if(session()->has('error')) 
     
    <div class="alert alert-danger" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true">×</button>
        <span class="badge badge-pill badge-danger" style="background-color:#ff8080; font-size: 15px;"> Error !</span> <span id="msg" >{{ session()->get('error') }}</span>
    </div>
                                         

    <!-- <p class="alert alert-danger col-lg-12" id="danger-alert" style="font-size: 16px;"><span class="badge badge-pill badge-danger"> Error !</span>
        <span id="msg">
        {{ session()->get('error') }}</span>
        <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    </p>  -->
    <!-- <div class="col-sm-10">
        <div class="alert alert-danger alert-dismissible">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong> Error!</strong> {{ session()->get('error') }}
        </div>
    </div>  -->   
@endif