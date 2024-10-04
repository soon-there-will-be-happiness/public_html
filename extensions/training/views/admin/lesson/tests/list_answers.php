<?defined('BILLINGMASTER') or die;?>

<div class="col-1-1" id="answers_for_question">
    <?if($options):?>
        <table>
            <?foreach($options as $key => $option):?>
                <tr>
                    <td class="text-center">
                        <label class="custom-chekbox-wrap mt-8">
                            <input type="hidden" name="answers[answer_<?=$key+1;?>][option_id]" value="<?=$option['option_id'];?>">
                            <input type="checkbox" value="1" class="answer-valid" name="answers[answer_<?=$key+1;?>][valid]"<?if($option['valid']) echo ' checked="checked"';?>>
                            <span class="custom-chekbox"></span>
                        </label>
                    </td>

                    <td class="text-center">
                        <div class="test-answer-img-change-wrap">
                            <a <?if($option['cover']) echo "style=\"background-image:url({$option['cover']})\"";?> class="btn iframe-btn test-answer-img-change<?if($option['cover']) echo ' with-cover'?>" href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=test_answer_img-<?=$option['option_id'];?>&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" type="button"></a>
                            <input id="test_answer_img-<?=$option['option_id'];?>" type="hidden" name="answers[answer_<?=$key+1;?>][cover]" value="<?=$option['cover'];?>">
                            <?if($option['cover']):?>
                                <a class="link_delete" href="javascript:void(0)" data-id="<?=$option['option_id'];?>" title="Удалить изображение">
                                    <span class="icon-remove"</span>
                                </a>
                            <?endif;?>
                        </div>
                    </td>

                    <td class="text-center">
                        <input style="width: 300px;" type="text" value="<?=$option['title'];?>" name="answers[answer_<?=$key+1;?>][title]">
                    </td>

                    <td class="text-center">
                        <div class="min-label-wrap">
                            <label class="mb-0">
                                <span class="min-label">б.</span>
                            </label>
                            <input style="width: 100px;" type="text" value="<?=$option['points'];?>" name="answers[answer_<?=$key+1;?>][points]">
                        </div>
                    </td>

                    <td class="td-last">
                        <a class="link_delete ajax" href="/admin/training/test/answer/del/<?="$training_id/$lesson_id/{$quest_id}";?>" data-id="<?=$option['option_id'];?>" data-replace_block="#answers_for_question" title="Удалить">
                            <span class="icon-remove"</span>
                        </a>
                    </td>
                </tr>
            <?endforeach;?>
        </table>
    <?endif;?>
</div>