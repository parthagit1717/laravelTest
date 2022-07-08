@extends('layouts.app2')
@section('title') Profile @endsection
 
 
@section('content')

<!--app-content open-->
<div class="main-content app-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Profile</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@if($user->user_type==1) Admin Profile @else User Profile @endif</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->
            
            <!-- ROW-1 OPEN -->
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Change Password</div>
                                    </div>
                                    <form id="edit-profile-form" method="POST" action="{{ route('update_pasword') }}" enctype="multipart/form-data" autocomplete="off">
                                    @csrf
                                        <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id">
                                        <div class="card-body">
                                            <div class="text-center chat-image mb-5">
                                                <div class="mb-3 brround">
                                                    @if(@$user->image)
                                                        <img src="{{ asset('storage/images/user_image/'.@$user->image) }}" style="width: 100px;height: 120px;  margin-top: 10px;"   class="brround">
                                                    @else
                                                        <img src="{{asset('assets/images/users/7.png')}}" alt="5 Terre" style="width: 100px;height: 120px; margin-top: 10px;" class="brround">
                                                    @endif   
                                                   <!--  <img alt="avatar" src="assets/images/users/7.png" class="brround"> --> 
                                                </div>
                                                <div class="main-chat-msg-name"> 
                                                    <h5 class="mb-1 text-dark fw-semibold">{{ Auth::user() ? Auth::user()->name : '' }}</h5> 
                                                    <!-- <p class="text-muted mt-0 mb-0 pt-0 fs-13">Web Designer</p> -->
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Old Password</label>
                                                <div class="wrap-input100 validate-input input-group" id="Password-toggle">
                                                    <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                        <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                                    </a>
                                                    <input type="password" class="form-control" name="old_password" id="old_password" placeholder="Old password" value=""> 
                                                    <!-- <input class="input100 form-control" type="password" placeholder="Current Password"> -->
                                                </div>
                                                @if($errors->has('old_password'))
                                                    <span class="invalide-feedback" role='alert' >
                                                      <strong style="color:red; font-size: 15px;">{{ $errors->first('old_password') }}</strong>
                                                    </span> 
                                                @endif
                                                <!-- <input type="password" class="form-control" value="password"> -->
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">New Password</label>
                                                <div class="wrap-input100 validate-input input-group" id="Password-toggle1">
                                                    <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                        <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                                    </a>
                                                    <input type="password" class="form-control" name="password" id="password" placeholder="Type password" value="">
                                                    
                                                    <!-- <input class="input100 form-control" type="password" placeholder="New Password"> -->
                                                </div>
                                                @if($errors->has('password'))
                                                    <span class="invalide-feedback" role='alert' >
                                                        <strong style="color: red;font-size: 15px;">{{ $errors->first('password') }}</strong>
                                                    </span> 
                                                @endif
                                                <!-- <input type="password" class="form-control" value="password"> -->
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Confirm Password</label>
                                                <div class="wrap-input100 validate-input input-group" id="Password-toggle2">
                                                    <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                        <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                                    </a>
                                                    <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirm password"   value="">
                                                    @if($errors->has('password_confirmation'))
                                                      <span class="invalide-feedback" role='alert' >
                                                          <strong style="color: red;font-size: 15px;">{{ $errors->first('password_confirmation') }}</strong>
                                                      </span> 
                                                    @endif
                                                    <!-- <input class="input100 form-control" type="password" placeholder="Confirm Password"> -->
                                                </div>
                                                <!-- <input type="password" class="form-control" value="password"> -->
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-primary" type="submit">Update</button>
                                            <a href="{{route('edit_profile')}}" class="btn btn-danger">Cancel</a>
                                        </div> 
                                    </form> 
                                </div> 
                            </div>
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Edit Profile</h3>
                                    </div>
                                    <form id="edit-profile-form" method="POST" action="{{ route('update_profile') }}" enctype="multipart/form-data" autocomplete="off" >
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id">
                                        <div class="card-body" style="font-size:15px;"> 
                                            <div class="form-group">
                                                <label for="name">Full Name</label>
                                                <input type="text" class="form-control" id="name" placeholder="Full name" name="name" value="{{@$user->name}}">
                                            </div>
                                            @if($errors->has('name'))
                                              <span class="invalide-feedback" role='alert' >
                                                  <strong style="color: red;">{{ $errors->first('name') }}</strong>
                                              </span> 
                                            @endif
                                                 
                                            <div class="form-group">
                                                <label for="emailLabel">Email address</label>
                                                <input type="email" class="form-control" name="email" id="emailLabel" placeholder="Email address" value="{{@$user->email}}" readonly>
                                            </div> 


                                            <div class="form-group">
                                                <label for="countryLabel">Country</label>
                                                <input type="text" class="form-control" id="countryLabel" placeholder="Country Name" name="country" value="{{ $user->country ? $user->country : $ipdetails->country_name}}">
                                            </div>
                                            @if($errors->has('country'))
                                              <span class="invalide-feedback" role='alert' >
                                                  <strong style="color: red;">{{ $errors->first('country') }}</strong>
                                              </span> 
                                            @endif

                                            <div class="form-group">
                                                <label for="stateLabel">State</label>
                                                <input type="text" class="form-control" id="stateLabel" placeholder="State Name" name="state" value="{{$user->state ? $user->state : @$ipdetails->region}}">
                                            </div>
                                            @if($errors->has('state'))
                                              <span class="invalide-feedback" role='alert' >
                                                  <strong style="color: red;">{{ $errors->first('state') }}</strong>
                                              </span> 
                                            @endif

                                            <div class="form-group">
                                                <label for="cityLabel">City</label>
                                                <input type="text" class="form-control" id="cityLabel" placeholder="City Name" name="city" value="{{$user->city ? $user->city : @$ipdetails->city}}">
                                            </div> 
                                            @if($errors->has('city'))
                                              <span class="invalide-feedback" role='alert' >
                                                  <strong style="color: red;">{{ $errors->first('city') }}</strong>
                                              </span> 
                                            @endif

                                            <div class="form-group">
                                                <label for="zipcodeLabel">Zip Code</label>
                                                <input type="number" class="form-control" id="zipcodeLabel" placeholder="City Name" name="zipcode" value="{{$user->zipcode ? $user->zipcode : @$ipdetails->postal}}">
                                            </div>
                                            @if($errors->has('zipcode'))
                                              <span class="invalide-feedback" role='alert' >
                                                  <strong style="color: red;">{{ $errors->first('zipcode') }}</strong>
                                              </span> 
                                            @endif
                                            
                                            <div class="form-group">
                                                <label for="zipcodeLabel">Profile Image</label>
                                                <div class="col-sm-" style="margin: auto;">
                                                    <input type="file" name="image" onchange="previewImage(event)" class="mb-3"> 
                                                    @if(@$user->image)
                                                        <img src="{{ asset('storage/images/user_image/'.@$user->image) }}" style="width: 100px;height: 120px;  margin-top: 10px;" id="imagefields" class="brround"> 
                                                        <span style="margin-left: -12px; font-size: 30px;"><a href="javascript::void(0)" title='Click to remove profile image' onclick="romove()"><i class="fa fa-times-circle-o" aria-hidden="true" style="color: red;"></i></a></span>
                                                    @else 
                                                        <img src="{{asset('assets/images/users/7.png')}}" alt="5 Terre" style="width: 100px;height: 120px; margin-top: 10px;" id="imagefields" class="brround"> 
                                                        <!-- <a href="" class="btn btn-info " style="margin-left: 10px;">Remove image</a> -->

                                                    @endif  

                                                </div> 
                                                @if($errors->has('image'))
                                                  <span class="invalide-feedback" role='alert' >
                                                      <strong style="color: red;">{{ $errors->first('image') }}</strong>
                                                  </span> 
                                                @endif
                                            </div>                                          
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-primary" type="submit">Save Changes</button>
                                            <!-- <a href="javascript:void(0)" class="btn btn-success my-1">Update</a> -->
                                            <!-- <a href="javascript:void(0)" class="btn btn-danger my-1">Cancel</a> -->
                                        </div>
                                    </form>
                                </div>
                                
                                
                            </div>
                        </div>
                        <!-- ROW-1 CLOSED -->
        </div>
        <!-- CONTAINER END -->
    </div>
</div>
<!--app-content close--> 

@endsection

<script type="text/javascript">
    function romove() {
        swal({
        title: "Are you sure you want to remove this profile image ?", 
        icon: "warning",
        buttons: true,
        dangerMode: true,
      })
          .then((willDelete) => {
            if (willDelete) {
             window.location.href = "{{route('remove_profile_image',['userid'=>$user->id])}}";
            } else {
             return false;
            }
          });
            
        }
    </script>



 





 