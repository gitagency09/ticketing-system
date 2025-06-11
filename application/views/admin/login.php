<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Signin | Ticketing</title>
        
        <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
        <link href="<?php echo site_url(); ?>assets/dist-assets/css/themes/lite-purple.min.css" rel="stylesheet">
        <link href="<?php echo site_url(); ?>assets/css/style09.css" rel="stylesheet">

         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <link href="<?php echo site_url(); ?>/assets/libs/ladda/ladda-themeless.min.css" rel="stylesheet" type="text/css" />

        <style type="text/css">
            .sig_col_left{
                float: left;
                display: flex;
                align-content: center;
                align-items: center;
                width: 70%;
                background: #fff;
                height: 100%;}
                .toggle-password{cursor: pointer;}
        </style>
    </head>

<body>

<div class="sig_min signin_bg1">

<div class="sig_col_left">

    <?php
    $images = json_decode($page['image'],true);
    if(is_array($images) && !empty($images)){

        echo '<div class="carousel_wrap">
            <div class="carousel slide" id="carouselExamplePause" data-ride="carousel">';

            echo '<ol class="carousel-indicators">';
            foreach ($images as $key => $value) {
                $class =''; if($key == 0){  $class = 'active';  }
                echo '<li class="'.$class.'" data-target="#carouselExamplePause" data-slide-to="'.$key.'"></li>';
            }
            echo '</ol>';

            echo '<div class="carousel-inner">';
            foreach ($images as $key => $value) {
                $class =''; if($key == 0){  $class = 'active';  }
                echo '<div class="carousel-item '.$class.'"><img class="d-block w-100" src="'.base_url($value['path']).'" />
                        
                    </div>';
            }
            echo '</div>';

            echo '</div></div>';
    }else{
        echo '<img src="'.base_url('assets/images/login.jpg').'" alt="">';
    }

    ?>
</div>

<div class="sig_col_right">
<div class="box_alg_center">    

<div class="p-4">
<div class="text-center mt-4 mb-4"><img src="<?php echo base_url('assets/images/black-logo.png'); ?>" alt=""></div>


<div class="text-center reg_company_name">
<p><strong>AGENCY09 Private Limited</p>
</div>

<div class="text-center">
<h1 class="mb-0 text-20 cust_sup_port"><strong>Customer Support Portal - admin / engineer login</strong></h1>
<h1 class="mb-0 text-20 cust_sup_port_login"><strong>Sign In</strong></h1>
<span style="font-weight: initial;font-size: 13px;">Enter your Registered Email address and password to access your account.</span>
</div>

 <?php echo form_open('admin/login',array('id' => 'login','autocomplete' => 'off') ); ?>


<div class="errors"> </div>

<div class="form-group">
    <label for="email">Email address</label>
    <input class="form-control form-control-rounded" id="email" type="email">
    <span class="error user_email"></span>
</div>


<div class="form-group">
    <label for="password">Password</label>

     <div class="form-group">
        <!-- <label for="password">Password*</label> -->
        <div class="input-group input-group-merge">
            <input class="form-control form-control-rounded" id="password" type="password">
            <div class="input-group-append" data-password="false">
                <div class="input-group-text toggle-password">
                    <i class="fa fa-eye " aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

    
    <span class="error user_pass"></span>
</div>

    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
    <div class="form-group mt-3">
        <!-- <div class="g-recaptcha" data-sitekey="<?php echo $RECAPTCHA_SITEKEY;?>"></div> -->
        <span class="error gresp"></span>
    </div>

<div class="form-group">

    <!-- <div class="lb">
        <label class="w-100">
        <div class="float-left">Password</div>
        <div class="clearfix"></div>
        </label>

        <input class="form-control form-control-rounded" id="password" type="password">
        <span class="user_pass"></span>
    </div> -->

 <!--    <div class="form-group mt-3">
        <div class="g-recaptcha" data-sitekey="<?php echo $RECAPTCHA_SITEKEY;?>"></div>
        <span class="gresp"></span>
    </div> -->

    <button class="ladda-button btn btn-rounded btn-primary btn-block mt-5" dir="ltr" data-style="slide-up" type="submit" id="submit_login">Sign In </button>

</div>


</form>


</div>
</div>
<div class="clearfix"></div>
</div>
</div>    

<script src="https://www.google.com/recaptcha/api.js?render=6LddUPkqAAAAAPsgTYwQC2gAp1vGBtW0xAoPp-1_"></script>

<!-- <script src="<?php //echo site_url(); ?>assets/dist-assets/js/plugins/jquery-3.3.1.min.js"></script> -->
<script src="<?php echo site_url(); ?>assets/dist-assets/js/plugins/jquery-3.6.0.min.js"></script>
<script src="<?php echo site_url(); ?>assets/dist-assets/js/plugins/bootstrap-4.5.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo site_url(); ?>assets/libs/ladda/spin.min.js"></script>
<script src="<?php echo site_url(); ?>assets/libs/ladda/ladda.min.js"></script>
<script src="<?php echo site_url(); ?>assets/custom.js"></script>

<script type="text/javascript">
    $(document).on('click', '.toggle-password', function() {
        $(this).find('i').toggleClass("fa-eye fa-eye-slash");
        var input = $(this).parents('.form-group').find('input');
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
    });


    $('#submit_login').click(function(e){
        e.preventDefault();

        $email = $.trim($('#email').val());
        $password  = $('#password').val();
        $token  = $('#login [name=token]').val();

        $error = 0;

        if($email == ""){
            $('.user_email').html('Enter email id');
            $error = 1;
        }else if(!nilesh.validateEmail($email)){
            $('.user_email').html('Invalid email id');
             $error = 1;
        }else{
            $('.user_email').html('');
        }
    
        if($password  == ""){
            $('.user_pass').html('Enter password');
            $error = 1;
        }else{
            $('.user_pass').html('');
        }

        var g_resp = '';
       /*var g_resp = grecaptcha.getResponse();
         if(g_resp  == ""){
            $('.gresp').html('Invalid Captcha');
            $error = 1;
        }else{
            $('.gresp').html('');
        }*/

        if($error == 1){
            return false;
        }
        grecaptcha.ready(function () {
            grecaptcha.execute('6LddUPkqAAAAAPsgTYwQC2gAp1vGBtW0xAoPp-1_', { action: 'login' }).then(function (token) {
                $('#g-recaptcha-response').val(token);
                var postdata = {"email":$email, "password":$password, "token":$token,"captcha" : token};

                $button = $(this);
                $button.addClass('process');
                var l = Ladda.create($('#submit_login')[0]);
                l.start();

                $('.errors').html('');
                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url()?>admin/login',
                    data: postdata,
                    success: function($res) {
                        if($res.status == 1){
                             window.location.href = "<?php echo site_url()?>";
                             return false;
                        }
                        else if($res.status == 2){
                            grecaptcha.execute();
                        }else if($res.status == 0){
                            $('.errors').html($res.message);
                        }

                        $('.errors').html($res.message);
                        
                        $button.removeClass('process');
                        Ladda.stopAll();
                    },
                    error: function(error, textStatus, errorMessage) {
                        $('.errors').html('Some error occured.');
                        $button.removeClass('process');
                        Ladda.stopAll();
                        grecaptcha.execute();
                    }             
                }); //end ajax
            });
        });
        
    });
</script>

</body>

</html>