@extends('layouts.app2')
@section('title') Profile @endsection

@section('content')


<div class="profile-center-area">
    <div class="page-header profile-header-row">
         
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                <li class="breadcrumb-item active">@if($user->user_type==1) Admin Profile @else User Profile @endif</li>
            </ol>
        </div>
    </div>
    <div class="fixed-profile-cards-row">
        <!-- Change Password -->
        <div class="fixed-profile-card">
            <div class="profile-title">Change Password</div>
            <form id="edit-profile-form" method="POST" action="{{ route('update_password') }}" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id">
                <div class="text-center chat-image mb-4 position-relative">
                    @if(@$user->image)
                        <img src="{{ asset('storage/images/user_image/'.@$user->image) }}" class="profile-avatar">
                        <a title="Remove profile image" class="img-remove-btn" onclick="romove()">
                            <i class="fa fa-times-circle-o"></i>
                        </a>
                    @else
                        <img src="{{asset('assets/images/users/7.png')}}" alt="Default User" class="profile-avatar">
                    @endif
                    <h5 class="mb-1 text-dark fw-semibold">{{ Auth::user() ? Auth::user()->name : '' }}</h5>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Old Password</label>
                    <div class="input-group">
                        <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                        </a>
                        <input type="password" class="form-control" name="old_password" id="old_password" placeholder="Old password">
                    </div>
                    @error('old_password')
                        <span class="invalide-feedback" role='alert'>
                            <strong style="color:red; font-size: 15px;">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                        </a>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Type password">
                    </div>
                    @error('password')
                        <span class="invalide-feedback" role='alert'>
                            <strong style="color: red;font-size: 15px;">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-1">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                            <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                        </a>
                        <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirm password">
                    </div>
                    @error('password_confirmation')
                        <span class="invalide-feedback" role='alert'>
                            <strong style="color: red;font-size: 15px;">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="{{route('edit_profile')}}" class="btn btn-danger ms-2">Cancel</a>
                </div>
            </form>
        </div>
        <!-- Edit Profile -->
        <div class="fixed-profile-card edit-profile-big">
            <div class="profile-title">Edit Profile</div>
            <form method="POST" action="{{ route('update_profile') }}" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id">
                <div class="card-body" style="font-size:15px;">
                    <div class="form-group mb-3">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Full name" name="name" value="{{ old('name', $user->name) }}">
                        @error('name')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="emailLabel">Email address</label>
                        <input type="email" class="form-control" name="email" id="emailLabel" placeholder="Email address" value="{{@$user->email}}" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="countryLabel">Country</label>
                        <input type="text" class="form-control" id="countryLabel" placeholder="Country Name" name="country" value="{{ old('country', $user->country ?? $ipdetails->country_name) }}">
                        @error('country')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="stateLabel">State</label>
                        <input type="text" class="form-control" id="stateLabel" placeholder="State Name" name="state" value="{{ old('state', $user->state ?? $ipdetails->region) }}">
                         @error('state')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="cityLabel">City</label>
                        <input type="text" class="form-control" id="cityLabel" placeholder="City Name" name="city" value="{{ old('city', $user->city ?? $ipdetails->city) }}">
                        @error('city')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="zipcodeLabel">Zip Code</label>
                        <input type="number" class="form-control" id="zipcodeLabel" placeholder="Zip Code" name="zipcode" value="{{ old('zipcode', $user->zipcode ?? $ipdetails->postal) }}">
                        @error('zipcode')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="profileImageLabel">Profile Image</label>
                        <input type="file" name="image" onchange="previewImage(event)" class="form-control mb-2">
                        <div class="d-flex align-items-center">
                            @if(@$user->image)
                                <img src="{{ asset('storage/images/user_image/'.@$user->image) }}" style="width: 64px;height: 76px;" id="imagefields" class="brround me-2"> 
                                <a href="javascript::void(0)" title='Click to remove profile image' onclick="romove()" style="font-size:1.7rem;color:#dc3545;"><i class="fa fa-times-circle-o"></i></a>
                            @else 
                                <img src="{{asset('assets/images/users/7.png')}}" alt="Default User" style="width: 64px;height: 76px;" id="imagefields" class="brround"> 
                            @endif
                        </div>
                        @error('image')
                          <span class="invalide-feedback" role='alert'>
                              <strong style="color: red;">{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
