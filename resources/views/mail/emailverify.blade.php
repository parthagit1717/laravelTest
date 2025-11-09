<!DOCTYPE html>
<html>
   <head>
      <title>Email Verify</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
      <style>
         #customers {
           font-family: Arial, Helvetica, sans-serif;
           border-collapse: collapse;
           width: 150%;
         }

         #customers td, #customers th {
           border: 1px solid #ddd;
           padding: 8px;
         }

         #customers tr:nth-child(even){background-color: #f2f2f2;}

         #customers tr:hover {background-color: #ddd;}

         #customers th {
           padding-top: 12px;
           padding-bottom: 12px;
           text-align: left;
           background-color:#990099;
           color: white;
         }

         .card {
             margin-top: 16px;
             margin-left: 16px;
             width: 150%;
             /*height: 400px;*/
             /*margin-right: 16px;*/
             background: #FFFFFF 0% 0% no-repeat padding-box;
             box-shadow: 0px 3px 20px #BCBCCB47;
             /*border-radius: 8px;*/
             opacity: 1;
             /*border: 2px solid red;*/
         }

         .header {
             width: 150%;
             height: 40px;
             background: #ECF2F9 0% 0% no-repeat padding-box;
             border-radius: 8px 8px 0px 0px;
         }

         .header h1 {
             text-align: center;
             font-family: 'Noto Sans', sans-serif;
             font-size: 14px;
             letter-spacing: 0;
             color: #4D4F5C;
         }

         .card-table {
           word-break: break-all;
         }

         .column {
          float: left;
          width: 33.33%;
          }
          .footer_row{ 
          /*height: 60px;*/
          margin: 10px 0;
          display: block;
          padding-left: 20px;
           
          }
          .column
          {
          position: relative;
          top:50%;
          transform: translateY(-50%);
          }
         
      </style>
      
   </head>
   <body>
      <div style="max-width:720px; margin:0 auto;">
          <div style="/*width:620px;*/background-color:#ffffff; /*padding: 0px 10px;*/ border:1px solid #dcd7d7; height: 75px;">
            <div style="float: none; text-align: center; margin-top: 0px; background:url('#') repeat center center">  
              <p>Email Verify</p>
            </div>
         </div>
         <div style="max-width:720px; border:1px solid #dcd7d7; margin:0 0; padding:15px; border-top:none;">
            <h1 style="font-family:Arial; font-size:16px; font-weight:500; /*color:#8ccd56;*/ margin:5px 0 12px 0;">Dear {{@$data['name']}},</h1>
            <div style="display:block; overflow:hidden; width:100%;">
               <p style="font-family:Arial; font-size:14px; font-weight:500; color:#000;margin-left: 5px;">
                  We're excited to have you get started. First, you need to confirm your account. Just press the button below.
               </p>  
               <div style="display:block;overflow:hidden; width:100%; text-align:center; margin: 0px 0px 10px 0px;">

                  <a href="{{ route('user.verify', [@$data['email_vcode'],md5(@$data['id'])]) }}" style="font-family:Arial; border-radius:17px;font-size:15px; font-weight:500; color:#FFF; display:inline-block; padding: 7px 12px; background-color: #ffc107;background-image: linear-gradient(326deg, #ffc107 0%, #ffc107 74%); text-decoration:none;">Verify Email</a>
               </div>
               <p style="font-family:Arial; font-size:14px; font-weight:500; color:#000;margin-left: 5px;">
                  If that doesn't work, copy and paste the following link in your browser:
               </p>  
               <div style="display:block;overflow:hidden; width:100%; margin: 0px 0px 10px 0px;"> 
                <p style="font-family:Arial; font-size:14px; font-weight:500; color:#ff944d;margin-left: 5px;">
                  {{ route('user.verify', [@$data['email_vcode'],md5(@$data['id'])]) }}
                </p>
               </div>               
            </div> 
            
            <p style="font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px; margin-top: 20px;">Cheers,</p>
            <p style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px;">Team Partha.</p>
            
         </div>

         <div style="/*width:620px;*/background-color: #ffffff; /*padding: 0px 10px;*/ border:1px solid #dcd7d7;color: #000;">
            <div id="sub-footer"">
                <div class="row footer_row">
                    <div class="column1" style=" font-family:Arial; font-size:14px; font-weight:500; color:#363839;margin: 0px 0px 10px 0px; text-align: center">Partha © <?php echo date("Y"); ?>. All Rights Reserved.</div>
                     
                </div>
            </div>
         </div>
      </div>
   </body>
</html>
