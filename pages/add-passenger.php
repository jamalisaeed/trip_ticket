<?php
/* add passenger
** Add a new passenger
*/

defined("ABSPATH") || exit;

global $wpdb;
$table = $wpdb->prefix .'passenger';


if( isset($_POST['add_passenger']) ){

              $melli = sanitize_text_field( $_POST['tt_melli'] );
              $name = sanitize_text_field( $_POST['tt_name'] );
              $family = sanitize_text_field( $_POST['tt_family'] );
              $phone = sanitize_text_field( $_POST['tt_phone'] );
              $email = sanitize_text_field( $_POST['tt_email'] );
              $birthday = sanitize_text_field( $_POST['tt_year'] ).sanitize_text_field( $_POST['tt_month'] ).sanitize_text_field( $_POST['tt_day'] );
              $sex = sanitize_text_field( $_POST['tt_sex'] );
              $group= sanitize_text_field( $_POST['tt_group'] );


  		      	$password = wp_rand(0,9999999);

              if( empty( $melli ) || empty( $email ) || empty( $name ) || empty( $family ) || empty( $phone ) || empty( $sex ) || empty( $group )){
                  wp_redirect(admin_url().'admin.php?page=add-passenger&empty=1&nonce='.wp_create_nonce('empty') );
                  $has_error = true;
              }

              if( username_exists( $melli ) ){
                  wp_redirect(admin_url().'admin.php?page=add-passenger&melli=1&nonce='.wp_create_nonce('melli') );
                  $has_error = true;
              }
              if( email_exists( $email ) || !filter_var( $email , FILTER_VALIDATE_EMAIL ) ){
                  wp_redirect(admin_url().'admin.php?page=add-passenger&nonce='.wp_create_nonce('email'));
                  $has_error = true;
              }

              if( !isset($has_error) ){

                  $user = array(
                          'user_login' =>  $melli,
                          'user_email' =>  $email,
                          'user_pass' => $password,
                          'first_name' => $name,
                          'last_name' => $family,
                      );

                  $userid = wp_insert_user($user);

                  if( !is_wp_error( $userid ) ){

                      add_user_meta($userid, 'phone' , $phone);
                      add_user_meta($userid, 'birthday' , $birthday);
                      add_user_meta($userid, 'sex' , $sex);
                      add_user_meta($userid, 'group' , $group);


                      $sj_home_url=home_url().'/tt_login/';

                      //mail password
                      $to = $email;
                      $subject = '?????? ???????? ???????? ???? ???????? ??????????';
                      $body = '?????? ???????? ??????: '.$password.'    '.$sj_home_url;

                      wp_mail( $to, $subject, $body );


                      $add_pass = $wpdb->insert($wpdb->prefix.'passenger', array(
                          'melli'    => $melli,
                          'phone'    => $phone,
                          'name'     => $name,
                          'family'   => $family,
                          'birthday' => $birthday,
                          'sex'      => $sex,
                          'group'      => $group,
                          'user_id'  => $userid,
                          'parent'   => 0,
                          'status'   => 1,
                          'dated'     => jdate('Y/m/j'),
                          'ip'       => $_SERVER['REMOTE_ADDR']
                          ));

                      if( $add_pass ){
                          wp_redirect(admin_url().'/admin.php?page=add-passenger&addpass=1&nonce='.wp_create_nonce('added_pass') );
                      }


                      require_once( ROOT . '/shortcodes/sms-class.php' );

                      try {

                  	date_default_timezone_set("Asia/Tehran");


                  	// your sms.ir panel configuration
                  	$APIKey = "e7faa04884fa68114a048";
                  	$SecretKey = "gfgd2g5dg1f5g2w5rrw2er6we2re";


                  	// message data
                  	$data = array(
                  		"ParameterArray" => array(
                  			array(

                  				"Parameter" => "name_family",
                  				"ParameterValue" => $name . ' ' . $family
                  			),
                        array(

                          "Parameter" => "site_url",
                          "ParameterValue" => $sj_home_url
                        ),
            							array(
            								"Parameter" => "pass",
            								"ParameterValue" => $password

            							)
                  		),
                  		"Mobile" => $phone,
                  		"TemplateId" => "6614"
                  	);

                  	$SmsIR_UltraFastSend = new SmsIR_UltraFastSend($APIKey,$SecretKey);
                  	$UltraFastSend = $SmsIR_UltraFastSend->UltraFastSend($data);
                  // 	var_dump($UltraFastSend);

                  } catch (Exeption $e) {
                  	echo 'Error UltraFastSend : '.$e->getMessage();
                  }

                      //redirect to login page

                  }
              }

}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">???????????? ??????????</h1>
    <a href="admin.php?page=all-passengers" class="page-title-action">???????? ??????????????</a>
    <br /><br />

    <?php


    if( isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'] , 'empty') ){
        echo '<span style="color:red">all filed should fill!</span>';
        echo '<br>';
    }
    else if( isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'] , 'melli') ){
        echo '<p style="box-shadow:0 0 5px #ddd;border-right:3px solid rgba(229,57,53 ,1);padding:8px 15px">?????????? ?????? ???????? ?????? ?????? ??????.</p>';
        echo '<br>';
    }
    if( isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'] , 'email') ){
        echo '<p style="box-shadow:0 0 5px #ddd;border-right:3px solid rgba(229,57,53 ,1);padding:8px 15px">?????????? ???????? ?????? ???????? ?????? ?????? ??????.</p>';
        echo '<br>';
    }

    if( isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'] , 'added_pass') ){
        echo '<p style="box-shadow:0 0 5px #ddd;border-right:3px solid green;padding:8px 15px">?????????? ???????? ???? ???????????? ?????? ?? ?????? ???????? ???????? ?????????? ?????????? ??????????!</p>';
        echo '<br>';
    }

     ?>
    <form id="add_pass_form" action="" method="post">

        <p>
            <label for="melli"><span style="color:red">*</span> ?????????? ??????:</label><br>
            <input type="text" name="tt_melli" id="melli" placeholder="" required></p>
        <p>
            <label for="phone"><span style="color:red">*</span> ???????? ??????????:</label><br>
            <input type="text" name="tt_phone" id="phone" placeholder="" required></p>
        <p>
            <label for="name"><span style="color:red">*</span> ??????:</label><br>
            <input type="text" name="tt_name" id="name" placeholder="" required></p>
        <p>
            <label for="family"><span style="color:red">*</span> ?????? ????????????????:</label><br>
            <input type="text" name="tt_family" id="family" placeholder="" required></p>
        <p> <label for="birthday" style="margin-bottom:12px;display:inline-block"><span style="color:red">*</span> ?????????? ????????:</label><br>
            <select name="tt_day" required style="width:70px;">
                    <option value="" hidden>??????</option>
                    <?php

                    for( $i=1 ; $i<32 ; $i++ ){
                        echo '<option value="'.$i.'">'.$i.'</option>';
                    }

                    ?>
                </select>
                &nbsp;&nbsp;&nbsp;
                    <select id="date" required name="tt_month" required >
                        <option value="" hidden>??????</option>
                        <option value="01">??????????????</option>
                        <option value="02">????????????????</option>
                        <option value="03">??????????</option>
                        <option value="04">??????</option>
                        <option value="05">??????????</option>
                        <option value="06">????????????</option>
                        <option value="07">??????</option>
                        <option value="08">????????</option>
                        <option value="09">??????</option>
                        <option value="10">????</option>
                        <option value="11">????????</option>
                        <option value="12">??????????</option>
                    </select>

                &nbsp;&nbsp;&nbsp;
                <select name="tt_year" required  style="width:70px;">
                    <option value="" hidden>??????</option>
                    <?php

                    $persian = array('??', '??', '??', '??', '??', '??', '??', '??', '??', '??');
                    $num = range(0, 9);
                    $x = str_replace($persian, $num, jdate("Y") );

                    while($x>1300){
                        echo '<option value="'.$x.'">'.$x.'</option>';
                        $x--;
                    }
                    ?>
                </select>
        </p>
        <p>
            <label for="email">??????????:</label><br><br>
            <input type="email" name="tt_email" id="email" placeholder="">
          </p>

        <p><label for="sex"><span style="color:red">*</span> ??????????:</label><br><br />
        <input type="radio" name="tt_sex" id="sex" required value="??????"/> ??????
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="tt_sex" id="sex" value="????"/> ????</p>

        <p>
            <label for="group"><span style="color:red">*</span> ???????? ????????????</label><br><br>
            <select id="group" name="tt_group" required >
              <option value="?????????? ????" selected="selected" >?????????? ????</option>
              <option value="??????????">??????????</option>
              <option value="??????">??????</option>
            </select>
      </p>
<br><br>
        <input type="submit" id="wp-submit" name="add_passenger" value="????????????"  class="button button-primary"/>

    </form>
</div>
