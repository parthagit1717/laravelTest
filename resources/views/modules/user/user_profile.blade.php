 

@extends('layouts.app2')
@section('title', 'Profile')

@section('content') 


<div class="profile-summary-area">
    <div class="page-header profile-header-row">
         
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
            <li class="breadcrumb-item active">@if($user->user_type==1) Admin Profile @else User Profile @endif</li>
        </ol>
    </div>
    <div class="summary-card-wrap">
        <div class="summary-card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                <div class="summary-title">Welcome <span class="summary-val">{{ $user->name }}</span></div>
                <a href="{{ route('edit_profile') }}" class="btn btn-primary edit-profile-btn">Edit Profile <i class="bi bi-pencil-square"></i></a>
            </div>
            <hr style="margin-top:8px;">
            <div class="summary-row">
                <div style="min-width:220px;">
                    <div class="mb-2">
                        <span class="summary-label">Your Name:</span>
                        <span class="summary-val">{{ $user->name }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="summary-label">Your Email:</span>
                        <span class="summary-val">{{ $user->email }}</span>
                    </div>
                </div>
                <div>
                    <span class="summary-label">Profile Image:</span>
                    <!-- Use your uploaded image.jpg as profile image -->
                    <img src="{{ @$user->image ? asset('storage/images/user_image/' . @$user->image) : asset('assets/images/users/7.png') }}" class="summary-image" alt="User" />
                </div>
            </div>
            <input type="hidden" name="user_id" value="{{$user->id}}" id="user_id">
        </div>
    </div>
</div>
@endsection
