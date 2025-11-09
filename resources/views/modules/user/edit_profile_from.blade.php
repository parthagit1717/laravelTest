@extends('layouts.app2')
@section('title') Profile @endsection

@section('content')
<style>
/* REMOVE the width, padding, margin override! */
.profile-header-row {
    width: 100%;
    max-width: 1060px;
    margin: 0;
    padding-top: 38px;
    padding-bottom: 0;
    text-align: left;
}
.page-header .page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #222338;
    margin-bottom: 0;
    padding-left: 18px;
    padding-top: 0;
}
.page-header .breadcrumb {
    font-size: 15px;
    padding-left: 0 !important;
}
.breadcrumb {
    padding-left: 18px;
    margin-bottom: 10;
    margin-bottom: 10;
    background: transparent;
    text-align: left;
    font-size: 17px;
}
.profile-center-area {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    padding-top: 0;
    width: 100%;
}
.fixed-profile-cards-row {
    display: flex;
    flex-direction: row;
    gap: 2.6rem;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
    margin: 0;
    max-width: 1060px;
}
.fixed-profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 8px 0 #e6eeff;
    padding: 2.3rem 2.2rem 2rem 2.2rem;
    width: 410px;
    min-width: 310px;
    min-height: 540px;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
}
.fixed-profile-card.edit-profile-big {
    width: 500px;
    min-width: 330px;
    max-width: 100%;
}
.profile-header-row {
    width: 100%;
    max-width: 1060px;
    margin: 0;
    padding-top: 0px;
    padding-bottom: 0;
    text-align: left;
}
@media (max-width:1200px) {
    .profile-header-row,
    .fixed-profile-cards-row {
        max-width: 99vw;
    }
}
@media (max-width:991px) {
    .fixed-profile-cards-row {
        flex-direction: column;
        align-items: center;
        gap: 2.2rem;
    }
    .fixed-profile-card,
    .fixed-profile-card.edit-profile-big {
        min-width: 94vw;
        max-width: 99vw;
        width: 97vw;
    }
}
.profile-title {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.35rem;
    font-weight: 600;
    color: #171a1f;
    letter-spacing: 0.03em;
}
.profile-avatar {
    width: 100px;
    height: 120px;
    border-radius: 16px;
    object-fit: cover;
    margin: 0 auto 0.7em auto;
    box-shadow: 0 4px 16px 0 #e6eeff77;
}
.img-remove-btn {
    position: absolute;
    right: 10px;
    top: 10px;
    color: #dc3545;
    font-size: 1.6rem;
    cursor: pointer;
    background: white;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: box-shadow .25s;
    box-shadow: 0 2px 12px 0 #e6eeff88;
}
.img-remove-btn:hover {
    background: #ffeaea;
    color: #c82333;
}
</style>

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
            <form id="edit-profile-form" method="POST" action="{{ route('update_pasword') }}" enctype="multipart/form-data" autocomplete="off">
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
