<?defined('BILLINGMASTER') or die;

$texts = [
    'training' => [
        System::Lang('TRAINING_OPEN'), System::Lang('NO_ACCESS_TRAINING')
    ],
    'section' => [
        System::Lang('SECTION_OPEN'), System::Lang('NO_ACCESS_SECTION')
    ],
    'lesson' => [
        System::Lang('LESSON_OPEN'), System::Lang('NO_ACCESS_LESSON')
    ]
];

if (($access['status'] == Training::NO_ACCESS_TO_DATE) || ($access['status'] == TrainingLesson::STATUS_LESSON_NOT_YET) & $access['start_date']>0) {
    $h3 = $texts[$this->view['is_page']][0] . System::dateSpeller($access['start_date']);
} else {
    $h3 = $texts[$this->view['is_page']][1];
}?>

<div class="layout" id="no_access">
    <div class="content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <?if($this->page_settings['h1']):?>
                <h1><?=$this->page_settings['h1'];?></h1>
                <h3><?=$h3;?></h3>
            <?else:?>
                <h1><?=$h3;?></h1>
            <?endif;

            if(($access['status'] != Training::NO_ACCESS_TO_DATE) && ($access['status'] != TrainingLesson::STATUS_LESSON_NOT_YET)):?>
                <?if(!$user_id):?>
                    <p><?=System::Lang('LOGIN_FAULT');?> <a href="#modal-login" data-uk-modal="{center:true}"><?=System::Lang('SITE_LOGIN');?></a>.</p>
                <?else:
                    $section = $this->view['is_page'] == 'section' || $this->view['is_page'] == 'lesson' ? $section : null;
                    $lesson = $this->view['is_page'] == 'lesson' ? $lesson : null;
                    $buttons = Training::renderByButtons(false, $training, $section, $lesson);

                    if ($buttons['big_button'] || $buttons['small_button']):?>
                        <div class="by_buttons-wrap">
                            <?if($buttons['big_button']):?>
                                <div class="z-1 by_button">
                                    <a class="<?=Training::getCssClasses($this->settings, $buttons['big_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['big_button']['url'], $user_id);?>"><?=$buttons['big_button']['text'];?></a>
                                </div>
                            <?endif;

                            if($buttons['small_button']):?>
                                <a class="<?=Training::getCssClasses($this->settings, $buttons['small_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['small_button']['url'], $user_id);?>"><?=$buttons['small_button']['text'];?></a>
                            <?endif;?>
                        </div>
                    <?endif;
                endif;
            endif;?>
        </div>

        <?require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>
<?php 

function replaceNameEmail($string, $user_id)
{
    
    if($user_id){
        
        $user = User::getUserById($user_id);
        $replace = array(
            '[NAME]' => urlencode($user['user_name']),
            '[EMAIL]' => $user['email'],
        ); 
        
        return strtr($string, $replace);  
    } else return $string;
}

?>