<?php defined('BILLINGMASTER') or die;?>

<h1><?php if(isset($params['params']['title']) && !empty($params['params']['title'])) echo $params['params']['title']; else echo System::Lang('PARTNERSHIPLONG');?></h1>
<?php if(isset($_GET['success_reg'])):?>
    <div class="success_message"><?=System::Lang('REG_IN_AFF_PROGRAM');?></div>
<?php endif;

if(isset($_GET['success'])):

    ?>
    <div class="success_message"><?=System::Lang('SAVED');?>!</div>
<?php endif;?>

<div class="tabs">
    <ul>
        <li id="tab1"><?=System::Lang('BASIC');?></li>
        <li id="tab2"><?=System::Lang('PERTER_LINKS');?></li>
        <!-- <li id="tab3"><?=System::Lang('SHORT_LINKS');?></li> -->
        <li id="tab4"><?=System::Lang('PARTER_ORDERS');?></li>
        <li id="tab6"><?=System::Lang('ATTRACTED_PEOPLE');?></li>
        <li id="tab5"><?=System::Lang('REQUISITES');?></li>

        <!-- <li id="tab7"><?=System::Lang('INTEGRATION');?></li> -->
    </ul>

    <div class="userbox usertabs">
        <?php require_once (__DIR__ . '/aff_cabinet/aff_main_tab.php');?>
        <?php require_once (__DIR__ . '/aff_cabinet/aff_referral_tab.php');?>
        <?php //require_once (__DIR__ . '/aff_cabinet/aff_shortlink_tab.php');?>
        <?php require_once (__DIR__ . '/aff_cabinet/aff_orders_tab.php');?>
        <?php require_once (__DIR__ . '/aff_cabinet/aff_people_tab.php');?>
        <?php require_once (__DIR__ . '/aff_cabinet/aff_requisites_tab.php');?>

        <?php //require_once (__DIR__ . '/aff_cabinet/aff_postbacks_tab.php');?>
    </div>
</div>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        /* padding: 20px; */
    }
    .container {
        display: flex;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        gap: 20px;
    }
    .form-section {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        width: 48%;
        display: flex;
        flex-direction: column;
    }
    h2 {
        margin-bottom: 20px;
        font-size: 22px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-size: 16px;
        font-weight: bold;
    }
    #birthday,
    #passport-date {
        width: 100%;
        padding: 10px;
        border: 1px solid #a29595;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #a29595; /* #736868; */
        border-radius: 10px;
        font-size: 14px;
    }
    small {
        display: block;
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }
    .important-info {
        margin-top: 5px;
        color: red;
        margin-bottom: 12px;
        font-size: 11px;
        line-height: 19px;
    }
    .file-upload {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    button {
        background-color: transparent;
        border: 1px solid #111111;
        padding: 10px 15px;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
    }
    button svg {
        margin-right: 5px;
    }
    button:hover {
        background-color: #ddd;
    }
    #passport-file-name,
    #org-file-name {
        margin-left: 10px;
        font-size: 14px;
        color: #666;
    }
    .submit-btn {
        background: #3250ea;
        width: 100%;
        font-size: 16px;
        padding: 10px 0;
        border-radius: 10px;
        cursor: pointer;
        border: none;
        color: white;
        justify-content: center;
        margin-bottom: 7px;
        margin-top: 7px;
    }
    .submit-btn:hover {
        background-color: #2955b9;
    }
    @media (max-width: 1200px) {
        .important-info {
            font-size: 8px;
        }
    }
    @media (max-width: 904px) {
        .form-section_three h2{
            padding-right: 28px;
            line-height: 23px;
        }
        .form-section_second h2 {
            padding-right: 28px;
            line-height: 24px;
        }
    }
    @media (max-width: 899px) {
        .important-info {
            margin-bottom: 15px;
        }
    }
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        .form-section {
            width: 100%;
        }
        .form-section_two input[type="text"] {
            margin-bottom: 15px;
        }
        .important-info {
            margin-top: 7px;
            margin-bottom: 12px;
            line-height: 18px;
            font-size: 11px;
        }
    }
    @media (max-width: 350px) {
        .form-section {
            padding: 10px;
        }
        button {
            padding: 10px 10px;
            font-size: 12px;
        }
    }
</style>
