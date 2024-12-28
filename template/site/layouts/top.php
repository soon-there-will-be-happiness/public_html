<?defined('BILLINGMASTER') or die;
$user_id = intval(User::isAuth());
if($user_id) $user = User::getUserById($user_id);
$menu_items = System::getMenuItems(1);
$current_url = htmlentities(substr($_SERVER['REQUEST_URI'], 1));
$en_trainings = System::CheckExtensension('training', 1);
$courses_enable = System::CheckExtensension('courses', 1);
?>

<nav class="topmenu">
    <div class="uk-offcanvas" id="offcanvas-1">
        <div class="uk-offcanvas-bar uk-offcanvas-bar-flip">
            <a onclick="UIkit.offcanvas.hide();" class="tm-offcanvas-close">
                <span class="icon-close"></span>
            </a>

            <ul class="main-menu">
                <?if($menu_items && $this->show_top_menu):
                    foreach($menu_items as $item):
                        if ($item['show_in_order_pages'] != 1 && $this->view['is_page'] == "order") {
                            continue;
                        }


                        if ($item['showByGroup'] != 0) {
                            $showGroups = json_decode($item['showGroups'], true);
                            $userAuth = User::isAuth();
                            if ($showGroups) {
                                    if (!$userAuth) {
                                        continue;
                                    }
                                    $userGroups = User::getGroupByUser($userAuth);

                                    $userHaveGroup = false;
                                    if ($userGroups) {
                                        foreach ($userGroups as $userGroup) {
                                            if (in_array($userGroup, $showGroups)) {
                                                $userHaveGroup = true;
                                                break;
                                            }
                                        }
                                    }

                                    if ($item['showByGroup'] == 1) {//Показывать только юзерам с группой
                                        if (!$userHaveGroup) {
                                            continue;
                                        }
                                    } else {//Показывать юзерам у которых нету групп
                                        if ($userHaveGroup) {
                                            continue;
                                        }
                                    }
                            }

                        }

                        $blank = $item['new_window'] == 1 ? ' target="_blank"' : '';
                        if ($item['visible'] == 0) {
                            continue;
                        };?>
                        <li>
                            <?$sub_items = System::getMenuItems(1, $item['item_id']);
                            if($item['type'] == 'main'):?>
                                <a href="/"<?=$blank;?> title="<?=$item['title'];?>"<?if($current_url == '') echo ' class="current"';?>>
                                    <?=$item['name'];?>
                                    <?if($sub_items):?>
                                        <span class="icon-arrow-down"></span>
                                    <?endif;?>
                                </a>
                            <?elseif($item['type'] == 'custom'):?>
                                <a href="<?=$item['link'];?>"<?=$blank;?> title="<?=$item['title'];?>"<?if(!empty($current_url) && strpos($item['link'], $current_url)) echo ' class="current"';?>>
                                    <?=$item['name'];?>
                                    <?if($sub_items):?>
                                        <span class="icon-arrow-down"></span>
                                    <?endif;?>
                                </a>
                            <?else:?>
                                <a href="/<?=$item['link'];?>"<?=$blank;?> title="<?=$item['title'];?>"<?if($item['link'] == $current_url) echo ' class=" current"';?>>
                                    <?=$item['name'];?>
                                    <?if($sub_items):?>
                                        <span class="icon-arrow-down"></span>
                                    <?endif;?>
                                </a>
                            <?endif;

                            $sub_blank = '';
                                if($sub_items):?>
                                    <ul class="submenu">
                                        <?foreach($sub_items as $sub):
                                            if ($sub['new_window'] == 1) {
                                                $sub_blank = ' target="_blank"';
                                            };
                                            if ($sub['visible'] == 0) {
                                                continue;
                                            };?>
                                        <li>
                                            <?if($sub['type'] == 'main'):?>
                                                <a href="/"<?=$sub_blank;?> title="<?=$sub['title'];?>">
                                                    <?=$sub['name'];?>
                                                </a>
                                            <?elseif($sub['type'] == 'custom'):?>
                                                <a href="<?=$sub['link'];?>"<?=$sub_blank;?> title="<?=$sub['title'];?>">
                                                    <?=$sub['name'];?>
                                                </a>
                                            <?else:?>
                                                <a href="/<?=$sub['link'];?>"<?=$sub_blank;?> title="<?=$sub['title'];?>">
                                                    <?=$sub['name'];?>
                                                </a>
                                            <?endif;?>
                                        </li>
                                    <?endforeach;?>
                                </ul>
                            <?endif;?>
                        </li>
                    <?endforeach;
                endif;?>
            </ul>
        </div>
    </div>

    <?if($this->settings['use_cart'] == 1):?>
        <div id="cartbox">
            <a href="<?=$this->settings['script_url']?>/cart">
                <i class="icon-cart"></i>
                <span id="cart-count"><?=Cart::countItems()['count']; ?></span>
            </a>
        </div>
    <?endif;?>

    <?if($this->settings['enable_cabinet'] == 1){
        if(!$is_auth):?>
        <ul class="logout-block">
            <li class="button-login">
                <a class="btn-blue-border-2" href="#modal-login" data-uk-modal="{center:true}"><?=System::Lang('LOGIN');?></a>
            </li>
        </ul>

    <?else:?>
        <div class="block-login">
            <div class="block-login__click">
                <img id="avatar-top" src="<?=User::getAvatarUrl($user, $this->settings);?>" class="<?=!empty($user['photo_url']) ? 'img-photo' : ''?>"/>
                <span class="icon-arrow-down"></span>
            </div>


            <div class="block-login__list">
                <?// TODO требуется переименовать названия меню
                $setting_main = System::getSettingMainpage();
                $user_menu = json_decode($setting_main['user_menu'], 1);
                $user_menu_links = [];
                if ($en_trainings && isset($user_menu['mytraining2']) && $user_menu['mytraining2'] == 1) {
                    $user_menu_links[] = ['href' => '/lk/mytrainings', 'title' => $user_menu['mytraining2_title']];
                }
                if ($courses_enable && isset($user_menu['mytraining']) && $user_menu['mytraining'] == 1) {
                    $user_menu_links[] = ['href' => '/lk/mycourses', 'title' => $user_menu['mytraining_title']];
                }
                if (isset($user_menu['myorders']) && $user_menu['myorders'] == 1) {
                    $user_menu_links[] = ['href' => '/lk/orders', 'title' => $user_menu['myorders_title']];
                }
                if (isset($user_menu['mysubs']) && $user_menu['mysubs'] == 1) {
                    $user_menu_links[] = ['href' => '/lk/membership', 'title' => $user_menu['mysubs_title']];
                }
                if (isset($user_menu['forum']) && $user_menu['forum'] == 1) {
                    $user_menu_links[] = ['href' => '/forum', 'title' => $user_menu['forum_title']];
                }
                if (isset($user_menu['myprofile']) && $user_menu['myprofile'] == 1) {
                    $user_menu_links[] = ['href' => '/lk', 'title' => $user_menu['myprofile_title']];
                }
                if (isset($user_menu['custom1']) && $user_menu['custom1'] == 1) {
                    $user_menu_links[] = ['href' => $user_menu['custom1_url'], 'title' => $user_menu['custom1_title']];
                }
               
                if($user_menu_links):?>
                    <ul>
                        <?foreach ($user_menu_links as $menu_link):?>
                            <li><a href="<?=$menu_link['href']?>"><?=$menu_link['title'];?></a></li>
                        <?endforeach;?>
                    </ul>
                <?endif;

                $user_menu_links = [];
                if ($user['is_author'] && isset($user_menu['authors']) && $user_menu['authors']) {
                    $user_menu_links[] = ['href' => '/lk/author', 'title' => $user_menu['authors_title']];
                }

                if ($user['is_curator']) {
                    if ($en_trainings && isset($user_menu['curators2']) && $user_menu['curators2']) {
                        $user_menu_links[] = ['href' => '/lk/curator', 'title' => $user_menu['curators2_title']];
                    }
                    if ($courses_enable && isset($user_menu['curators']) && $user_menu['curators']) {
                        $user_menu_links[] = ['href' => '/lk/answers', 'title' => $user_menu['curators_title']];
                    }
                }
                //$user_menu_links[] = ['href' => '/family', 'title' => "Семейные аккаунты"];
                if ($user['is_partner'] == 1 && isset($user_menu['partners']) && $user_menu['partners']) {
                    $user_menu_links[] = ['href' => '/lk/aff', 'title' => $user_menu['partners_title']];
                }

                if($user_menu_links):?>
                    <ul>
                        <?foreach ($user_menu_links as $menu_link):?>
                            <li><a href="<?=$menu_link['href']?>"><?=$menu_link['title'];?></a></li>
                        <?endforeach;?>
                    </ul>
                <?endif;?>

                <ul>
                    <li><a href="/lk/logout"><?=System::Lang('QUIT');?></a></li>
                </ul>
            </div>
        </div>
    <?endif;
    }?>

    <a data-uk-offcanvas href="#offcanvas-1" class="open-menu">
        <span></span><span></span><span></span>
    </a>
</nav>