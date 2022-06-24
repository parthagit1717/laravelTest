<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Onepatch Connect :: Ebay EKM"> 
    <meta name="keywords" content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}" />

    <!-- TITLE -->
    <title>ONEPATCH CONNECT : EKM EBAY | Forgot Password</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/dark-style.css" rel="stylesheet" />
    <link href="assets/css/transparent-style.css" rel="stylesheet">
    <link href="assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="assets/colors/color1.css" />
    <style type="text/css">
        .wrap-login100{
            width: 400px;
          }
          body {
          background-image:url({{url('assets/images/brand/backgound.png')}})
        }
    </style>

</head>

<body class="app sidebar-mini ltr">

    <!-- BACKGROUND-IMAGE -->
    <div class="login-img">

        <!-- GLOABAL LOADER -->
        <div id="global-loader">
            <img src="../assets/images/loader.svg" class="loader-img" alt="Loader">
        </div>
        <!-- /GLOABAL LOADER -->

        <!-- PAGE -->
        <div class="page">
            <div class="">

                <!-- CONTAINER OPEN -->
                <div class="col col-login mx-auto mt-7">
                    <div class="text-center">
                        <img src="{{asset('assets/images/brand/logo-2.png')}}" class="header-brand-img" alt="" style="width: 300px;height: 100px;">
                    </div> 
                </div>
                @if(session()->has('success'))
                    <div class="col-lg-6 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                        <p class="alert alert-success col-lg-10" id="success-alert" style="font-size: 16px; background-color: #70db70; color: white;"><span class="badge badge-pill badge-success" style="background-color:  #1f7a1f;"> Success !</span>
                        <span id="msg">{{ session()->get('success') }} </span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                        </p>
                    </div>
                 @endif
                
               

                @if(session()->has('error')) 
                <div class="col-lg-6 col-md-6 center" style="display: flex; justify-content: center; margin-top: 10px;">
                    <p class="alert alert-danger col-lg-5" id="danger-alert" style="font-size: 16px; background-color:#ff9999"><span class="badge badge-pill badge-danger" style="background-color: #cc0000"> Error !</span>
                        <span id="msg" >
                        {{ session()->get('error') }}</span>
                        <a href="#" class="close" data-dismiss="alert" aria-label="close" style="float: right;">×</a>
                    </p> 
                </div>
                @endif
                <div class="container-login100">

                    <div class="wrap-login100 p-6">
                        
                        <form class="login100-form validate-form" method="POST" action="{{ route('user.password.email') }}">
                        @csrf

                        <span class="login100-form-title pb-5">
                            Forgot Password
                        </span>
                        <p class="text-muted">Enter the email address registered on your account</p>
                        <div class="panel panel-primary">
                            <div class="panel-body tabs-menu-body p-0 pt-5">
                                <div class="tab-content">
                                    <div class="tab-pane active col-lg-12" id="tab5">
                                        <div class="wrap-input100 validate-input input-group" data-bs-validate="Valid email is required: ex@abc.xyz">
                                            <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                                <i class="zmdi zmdi-email text-muted" aria-hidden="true"></i>
                                            </a>
                                            <input class="input100 border-start-0 form-control ms-0 @error('email') is-invalid @enderror" type="email" placeholder="Email" name="email" value="{{ old('email') }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>  
                                         
                                        <div class="container-login100-form-btn">
                                            <button type="submit" class="login100-form-btn btn-primary">Submit</button>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
                <!-- CONTAINER CLOSED -->
            </div>
        </div>
        <!-- End PAGE -->

    </div>
    <!-- BACKGROUND-IMAGE CLOSED -->

    <!-- JQUERY JS -->
    <script src="assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <!-- <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script> -->

    <!-- SHOW PASSWORD JS -->
    <script src="assets/js/show-password.min.js"></script>

    <!-- GENERATE OTP JS -->
    <script src="assets/js/generate-otp.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>

    <!-- Color Theme js -->
    <script src="assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="assets/js/custom.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>

</html>