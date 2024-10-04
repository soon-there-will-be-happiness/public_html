<?defined('BILLINGMASTER') or die;
$count_public_homeworks = TrainingPublicHomework::getCountOtherHomeworks($lesson['lesson_id'], $user_id);
$offset = 0;
$limit = 10;
$public_homeworks = $count_public_homeworks ? TrainingPublicHomework::getOtherHomeworks($lesson['lesson_id'], $user_id, $limit) : false;

if($count_public_homeworks):?>
    <h4>Работы других пользователей</h4>

    <div class="public-homework-list-wrap">
        <form enctype="multipart/form-data" class="" action="" method="POST" id="add_user_comment_form">
            <div class="public-homework-list">
                <?require_once (__DIR__ .'/list.php');?>
            </div>

            <div class="add-user-comment hidden" id="add_user_comment">
                <textarea class="editor" name="public_homework[user_comment]" required="required" placeholder="Напишите свой ответ"></textarea>
                <div class="attach home-work-attach">
                    <input type="file" data-browse="<?=System::Lang('UPLOAD_FILE');?>" multiple name="public_homework_user_attach[]">
                </div>

                <div class="add-home-work-submit z-1 add-home-work--simple">
                    <button type="submit" name="public_homework[add_comment]" class="button btn-orange btn-green btn-green--big"><?=System::Lang('SEND');?></button>
                </div>
            </div>

            <input type="hidden" name="public_homework[homework_id]">
        </form>
    </div>
<?endif;?>

<link rel="stylesheet" href="/extensions/training/web/frontend/style/public_homeworks.css?v=<?=CURR_VER;?>" type="text/css" />
<script src="/extensions/training/web/frontend/js/public_homeworks.js?v=<?=CURR_VER;?>"></script>