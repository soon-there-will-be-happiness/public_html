<?defined('BILLINGMASTER') or die;?>

<div class="lessons_list sections_list">
    <?foreach ($section_list as $key => $section):
        $section_status = TrainingSection::getSectionStatus($training, $section, $user_id, $user_groups, $user_planes);
        if (($section_status == TrainingSection::STATUS_SECTION_NOT_YET && !$section['is_show_before_open']) || ($section_status == TrainingSection::STATUS_SECTION_NOT_ACCESS && !$section['is_show_not_access'])) {
            continue;
        }
        $section_status_data = TrainingSection::getSectionStatusData($section_status, $training, $section, $user_id, $user_groups, $user_planes);?>

        <div class="lesson_item">
            <?if(($section_status_data['link']) && ($section_status !== TrainingSection::STATUS_SECTION_NOT_ACCESS)):?><a class="lesson_item_link" href="<?=$section_status_data['link'];?>" data-section_id="<?=$section['section_id'];?>"></a><?endif;?>

            <?if($section['image_type'] == 1 || !$section['cover']):?>
                <span class="lesson_number"><?=($section['sort']);?></span>
            <?else:
                if($section_status_data['link']):?>
                    <a href="<?=$section_status_data['link'];?>" data-section_id="<?=$section['section_id'];?>">
                        <div class="lesson_cover">
                            <img src="/images/training/sections/<?=$section['cover'];?>" alt="<?=$section['img_alt'];?>"/>
                        </div>
                    </a>
                <?else:?>
                    <div class="lesson_cover">
                        <img src="/images/training/sections/<?=$section['cover'];?>" alt="<?=$section['img_alt'];?>"/>
                    </div>
                <?endif;
            endif;?>

            <div class="lesson_desc section_desc">
                <div class="<?=$section_status_data['class'];?>">
                    <div class="section_title">
                        <?if($section_status_data['link']):?>
                            <a href="<?=$section_status_data['link'];?>" data-section_id="<?=$section['section_id'];?>"><?=$section['name'];?></a>
                        <?else:?>
                            <span><?=$section['name'];?></span>
                        <?endif;?>
                    </div>

                    <div class="lesson-desc-text"><?=html_entity_decode($section['section_desc']);?></div>
                    <div class="lesson-title-status"><?=$section_status_data['text'];?></div>
                </div>
            </div>
        </div>
    <?endforeach;?>
</div>