 <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/dark-style.css" rel="stylesheet" />
    <link href="../assets/css/transparent-style.css" rel="stylesheet">
    <link href="../assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="../assets/colors/color1.css" />

    <!-- for subription list -->
     <style>
        @media only screen and (min-width:576px) {
            a#add-new-post {float: right;}
           }
            .card-body {
            font-size: 20px;
          }
          .bubsbtn {
            color: #000;
            background-color: #fff;
            box-shadow: none;
            font-size: 20px!important;
            border: 2px solid #fff;
             

        }.bubsbtn:hover {
            color: #fff;
            background-color:#ff9966;
            box-shadow: none;
            font-size: 20px!important;
            border: 2px solid #fff;
        }
          .card-footer small{
            color:#ffcc99;
            font-size: 20px;
            font-weight: bold;
          }

          .cardsty{
            font-size: 20px;
            background:#ff99ff; 
            color: white;
            text-align: center;
          }

          

 </style>
 <!-- End for subription list -->

 <style>
/* Attractive See More Button */
.see-more-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.9rem 2.2rem;
  border-radius: 999px;
  font-weight: bold;
  font-size: 1.17rem;
  color: #fff;
  background: linear-gradient(90deg,#2757ec 40%,#39c3ff 90%);
  box-shadow: 0 5px 20px -1px #2757ec77;
  border: none;
  letter-spacing: 0.02em;
  cursor: pointer;
  transition: all 0.27s;
  outline: none;
}
.see-more-btn:hover, .see-more-btn:focus {
  background: linear-gradient(90deg,#39c3ff 40%,#2747ec 90%);
  box-shadow: 0 8px 28px 0px #39c3ff99;
}
.see-more-btn:active {
  transform: scale(0.98);
}
.see-more-btn .see-more-icon {
  font-size: 1.3em;
  margin-right: 0.7em;
  animation: bounce 1.6s infinite;
}
@keyframes bounce {
  0%,100%{transform:translateY(0);} 50%{transform:translateY(-8px);}
}

/* Demo Post Card styles (optional) */
.fb-feed-bg     { background: #f0f2f5; }
.fb-post-card   { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; border: 1px solid #e4e6eb; margin-bottom: 28px; padding: 18px 18px 10px 18px; }
.fb-header-row  { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;}
.fb-user-wrap   { display: flex; align-items: center; gap: 10px; }
.fb-avatar      { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #d1d5db; object-fit: cover; }
.fb-user-info   { display: flex; flex-direction: column; }
.fb-username    { font-weight: 600; color: #050505; font-size: 1.01rem; }
.fb-user-time   { font-size: 0.84rem; color: #65676b; margin-top: 2px; }
.fb-title       { font-weight: bold; font-size: 1.08rem; color: #1d1e20; margin-bottom: 2px; }
.fb-posttext    { color: #22223b; font-size: 1.02rem; margin-bottom: 10px; line-height: 1.38; }
.fb-img         { width: 100%; border-radius: 9px; margin-bottom: 10px; max-height: 280px; object-fit: cover; border: 1px solid #edeefa;}
.fb-actions     { display: flex; justify-content: space-between; align-items: center; font-size: 0.93rem; color: #65676b; border-top: 1px solid #f1f3f5; margin-top: 10px; padding-top: 5px;}
.fb-action-btn  { flex: 1 1 0; display: flex; align-items: center; justify-content: center; padding: 8px 0; border-radius: 7px; font-weight: 500; background: none; border: none; transition: background 0.17s; cursor: pointer;}
.fb-action-btn:hover { background: #f0f2f5;}
.fb-count-row   { display: flex; justify-content: space-between; font-size: 0.97rem; color: #65676b; border-bottom: 1px solid #ececec; margin-bottom: 5px; padding-bottom:4px;}
.fb-dropdown    { position: relative;}
.fb-dot-btn     { background: none; border: none; color: #6b7280; cursor: pointer; padding: 5px; border-radius: 100px;}
.fb-dot-btn:hover { background: #ebedf0;}
.fb-dropdown-list{ display: none; position: absolute; right:0; top:29px; z-index: 15; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 1px 10px #0002; min-width: 120px; }
.fb-dropdown:hover .fb-dropdown-list { display: block;}
.fb-dropdown-list a, .fb-dropdown-list button { display:block; width:100%; padding: 8px 15px; font-size:0.96rem; text-align: left; background:none; border:none; outline:none; color:#333; transition: background 0.13s;}
.fb-dropdown-list a:hover, .fb-dropdown-list button:hover { background: #f0f2f5;}

.create-post-btn {
  font-size: 0.97rem;
  padding: 0.30rem 1.05rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.4em;
  box-shadow: 0 4px 14px -4px #0d6efd35;
  border: none;
}
.create-post-btn .bi-plus-circle-fill {
  font-size: 1.08em;
  animation: pulse-btn 1.2s infinite;
}
@keyframes pulse-btn {
  0%,100%{transform:scale(1);}
  60%{transform:scale(1.16);}
}
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
@media (max-width: 768px) {
  .header-row {
    flex-direction: column;
    gap:10px;
    align-items: stretch;
  }
  .create-post-btn {
    align-self: flex-end;
  }
  
}

.hadr{
    margin-bottom: 20px !important;
  }


</style>

<style>
.btn-like.liked, .btn-like[aria-pressed="true"] {
  background: #dff3fb;
  color: #0057a4;
  font-weight: bold;
  border: 1px solid #aee1fa;
}
.btn-like.liked i, .btn-like[aria-pressed="true"] i {
  color: #0d6efd;
}
</style>

<style>
/* REMOVE the width, padding, margin override! */
 
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

<style>
 
.page-title {
    font-size: 2.1rem;
    font-weight: 600;
    color: #222338;
    margin-bottom: 2px;
    padding-left: 18px;
    padding-top: 0;
    text-align: left;
}
.breadcrumb {
    padding-left: 18px;
    margin-bottom: 0;
    background: transparent;
    text-align: left;
}
.profile-summary-area {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 0;
    width: 100%;
}
.summary-card-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: 32px;
    margin-bottom: 32px;
}
.summary-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 3px 24px 0 #e6eeff;
    width: 100%;
    max-width: none;
    padding: 2.4rem 2.5rem 2rem 2.5rem;
    margin-top: -36px;
}
@media (max-width:991px) {
    .profile-header-row, .summary-card {
        max-width: 99vw;
    }
    .summary-card {
        padding: 1.2rem .8rem 1.2rem .8rem;
    }
}
.summary-image {
    width: 90px;
    height: 90px;
    border-radius: 14px;
    object-fit: cover;
    box-shadow: 0 2px 12px 0 #e6eeff;
}
.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 22px 0 22px 0;
    flex-wrap: wrap;
}
@media (max-width:650px) {
    .summary-row {
        flex-direction: column;
        gap: 18px;
        align-items: flex-start;
    }
}
.summary-label {
    font-weight: 500;
    color: #5a5a6e;
    margin-right: 6px;
}
.summary-val {
    font-weight: 500;
    color: #27294e;
}
.summary-title {
    font-size: 1.07rem;
    margin-bottom: 7px;
    color: #4d60c6;
    font-weight: 700;
}
.edit-profile-btn {
    float: right;
    margin-top: -6px;
    margin-bottom: 13px;
}

.profile-header-row {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding-top: 0px;
    padding-bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
</style>
<style>
.app-sidebar {
    background: #fff;
    min-width: 275px;
    max-width: 315px;
    min-height: 100vh;
    border-right: 1.5px solid #eee;
    box-shadow: 1px 0 16px 0 #e7eaff2e;
    display: flex;
    flex-direction: column;
    align-items: stretch;
}
.profile-section-sash {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 32px 22px 18px 22px;
    border-bottom: 1px solid #f2f4fa;
    margin-bottom: 8px;
}
.profile-section-sash .profile-pic {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
    border: 2.5px solid #e0e4f9;
}
.profile-section-sash .profile-name {
    font-size: 1.08rem;
    font-weight: 600;
    color: #232447;
    margin-bottom: 1px;
}
.profile-section-sash .profile-location {
    font-size: 0.98rem;
    color: #9596b2;
    margin-bottom: 0;
}

/* Profile stats row */
.profile-section-sash .profile-stats-row {
    width: 100%;
    display: flex;
    justify-content: space-evenly;
    border-radius: 12px;
    background: #f6f8fa;
    margin: 18px 0 0 0;
    padding: 13px 0 7px 0;
    border: 1px solid #f1f2fa;
    gap: 12px;
}
.profile-stats-block {
    text-align:center;
    flex: 1 1 40%;
}
.profile-stats-block .count {
    font-size: 1.15rem;
    font-weight: 700;
    color: #393fb0;
    line-height: 1.2;
}
.profile-stats-block .label {
    font-size: 0.96rem;
    color: #7c7f9c;
}
.side-menu {
    list-style: none;
    padding-left: 0;
    margin: 0;
    width: 100%;
}
.side-menu__item {
    width: 92%;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 13px 22px 13px 20px;
    margin: 0 auto 7px auto;
    border-radius: 13px;
    font-size: 1.13rem;
    font-weight: 500;
    color: #384060;
    transition: background 0.16s, color 0.11s, font-weight 0.11s;
    text-decoration: none;
    background: transparent;
    position: relative;
}
.side-menu__icon {
    font-size: 1.3rem;
    min-width: 25px;
}
.side-menu__item.active,
.side-menu__item:hover {
    background: #f7f8ff;
    color: #4567e2;
    font-weight: 700;
    box-shadow: 0 2px 16px 0 #e6eefa2a;
}
.side-menu__label {
    font-weight: 600;
    letter-spacing: 0.01em;
}
.slide-left, .slide-right {
    display: none !important;
}
@media (max-width: 991px) {
    .app-sidebar {
        min-width: 100vw;
        max-width: 100vw;
        box-shadow: none;
    }
}
</style>
